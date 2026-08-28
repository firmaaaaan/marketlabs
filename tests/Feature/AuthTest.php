<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_is_accessible(): void
    {
        $this->get(route('register'))->assertStatus(200);
    }

    public function test_user_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'nim_nip' => '1234-5678-90',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'nim_nip' => '1234-5678-90',
            'role' => User::ROLE_USER,
        ]);
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))->assertStatus(200);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'nim_nip' => '1111-2222-33',
            'password' => bcrypt('rahasia123'),
        ]);

        $response = $this->post(route('login'), [
            'nim_nip' => '1111-2222-33',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_credentials(): void
    {
        User::factory()->create([
            'nim_nip' => '1111-2222-33',
            'password' => bcrypt('rahasia123'),
        ]);

        $response = $this->post(route('login'), [
            'nim_nip' => '1111-2222-33',
            'password' => 'salah123',
        ]);

        $response->assertSessionHasErrors('nim_nip');
        $this->assertGuest();
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee($user->name);
    }

    public function test_non_admin_user_cannot_access_admin_pages(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('home'));

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('home'));
    }

    public function test_laboran_user_cannot_access_admin_pages(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_LABORAN]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('home'));
    }

    public function test_non_admin_user_login_redirects_to_home(): void
    {
        User::factory()->create([
            'nim_nip' => '4444-5555-66',
            'password' => bcrypt('rahasia123'),
            'role' => User::ROLE_LABORAN,
        ]);

        $this->post(route('login'), [
            'nim_nip' => '4444-5555-66',
            'password' => 'rahasia123',
        ])->assertRedirect(route('home'));
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_profile_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertStatus(200)
            ->assertSee('Informasi Akun')
            ->assertSee('Ubah Kata Sandi');
    }

    public function test_profile_requires_authentication(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@example.com',
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => 'baru@example.com',
                'nim_nip' => '2101234567',
                'institution' => 'Universitas Contoh',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
            'nim_nip' => '2101234567',
            'institution' => 'Universitas Contoh',
        ]);
    }

    public function test_profile_requires_nim_and_institution(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => 'baru@example.com',
            ])
            ->assertSessionHasErrors(['nim_nip', 'institution']);
    }

    public function test_user_cannot_use_email_owned_by_others(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['email' => 'diambil@example.com']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => 'diambil@example.com',
                'nim_nip' => '2101234567',
                'institution' => 'Universitas Contoh',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        $this->actingAs($user)
            ->patch(route('profile.password'), [
                'current_password' => 'rahasia123',
                'password' => 'rahasiaBaru456',
                'password_confirmation' => 'rahasiaBaru456',
            ])
            ->assertRedirect();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('rahasiaBaru456', $user->fresh()->password));

        // Bisa login dengan kata sandi baru.
        $this->post(route('logout'));

        $this->post(route('login'), [
            'nim_nip' => $user->nim_nip,
            'password' => 'rahasiaBaru456',
        ])->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        $this->actingAs($user)
            ->patch(route('profile.password'), [
                'current_password' => 'salah123',
                'password' => 'rahasiaBaru456',
                'password_confirmation' => 'rahasiaBaru456',
            ])
            ->assertSessionHasErrors('current_password');
    }
}
