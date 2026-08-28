<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_list(): void
    {
        $admin = User::factory()->create();
        User::factory()->create(['role' => User::ROLE_LABORAN, 'name' => 'Budi Laboran']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Budi Laboran', false)
            ->assertSee('Kelola User', false);
    }

    public function test_admin_can_create_user_with_laboran_role(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Sari Laboran',
                'email' => 'sari@example.com',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'role' => User::ROLE_LABORAN,
                'nim_nip' => '1987654321',
                'institution' => 'Laboratorium Kimia',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'sari@example.com',
            'role' => User::ROLE_LABORAN,
            'nim_nip' => '1987654321',
            'institution' => 'Laboratorium Kimia',
        ]);
    }

    public function test_admin_can_update_user_role(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => User::ROLE_LABORAN,
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => User::ROLE_LABORAN,
        ]);
    }

    public function test_role_petugas_tidak_lagi_tersedia(): void
    {
        $admin = User::factory()->create();

        $this->assertArrayNotHasKey('petugas', User::roles());

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Siapa Petugas',
                'email' => 'petugas@example.com',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'role' => 'petugas',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'petugas@example.com']);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_last_admin_cannot_lose_admin_role(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => User::ROLE_USER,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_admin_can_download_user_template(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.users.template'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_export_users(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Test']);
        User::factory()->create(['name' => 'Laboran Test', 'role' => User::ROLE_LABORAN]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.export'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_import_users_from_excel(): void
    {
        $admin = User::factory()->create();

        // Buat file Excel template secara manual.
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Nama', 'Email', 'NIM/NIK/NIP', 'Password', 'Role'],
            ['Import Satu', 'import1@example.com', 'NIM-001', 'password123', 'User'],
            ['Import Dua', 'import2@example.com', 'NIM-002', 'password456', 'Laboran'],
        ], null, 'A1');

        $tmpFile = tempnam(sys_get_temp_dir(), 'user_import_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);
        $spreadsheet->disconnectWorksheets();

        $file = new UploadedFile($tmpFile, 'user-import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.import'), ['file' => $file]);

        $response->assertRedirect()->assertSessionHas('success', 'Import selesai: 2 user berhasil ditambahkan.');

        $this->assertDatabaseHas('users', ['email' => 'import1@example.com', 'nim_nip' => 'NIM-001']);
        $this->assertDatabaseHas('users', ['email' => 'import2@example.com', 'nim_nip' => 'NIM-002', 'role' => User::ROLE_LABORAN]);

        @unlink($tmpFile);
    }

    public function test_import_skips_duplicate_email(): void
    {
        $admin = User::factory()->create();
        User::factory()->create(['email' => 'existing@example.com']);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Nama', 'Email', 'NIM/NIK/NIP', 'Password', 'Role'],
            ['Duplikat', 'existing@example.com', 'NIM-DUP', 'password123', 'User'],
        ], null, 'A1');

        $tmpFile = tempnam(sys_get_temp_dir(), 'user_import_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);
        $spreadsheet->disconnectWorksheets();

        $file = new UploadedFile($tmpFile, 'user-import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($admin)
            ->post(route('admin.users.import'), ['file' => $file]);

        $response->assertRedirect()->assertSessionHas('success');

        // Hanya ada 2 user (admin + existing), bukan 3.
        $this->assertEquals(2, User::count());

        @unlink($tmpFile);
    }

    public function test_non_admin_cannot_access_import(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get(route('admin.users.template'))
            ->assertRedirect(route('home'));

        $this->actingAs($user)
            ->get(route('admin.users.export'))
            ->assertRedirect(route('home'));
    }
}
