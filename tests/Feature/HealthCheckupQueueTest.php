<?php

namespace Tests\Feature;

use App\Models\HealthCheckup;
use App\Models\HealthTestType;
use App\Models\User;
use App\Support\ServiceSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HealthCheckupQueueTest extends TestCase
{
    use RefreshDatabase;

    private function booking(User $user, HealthTestType $type, string $date, int $queue, string $status): HealthCheckup
    {
        return HealthCheckup::create([
            'user_id' => $user->id,
            'type_id' => $type->id,
            'code' => 'MCU-'.uniqid(),
            'booking_date' => $date,
            'queue_number' => $queue,
            'status' => $status,
        ]);
    }

    public function test_queue_info_menghitung_posisi_dari_booking_yang_masih_aktif(): void
    {
        $user = User::factory()->create();
        $type = HealthTestType::firstOrFail();

        $done = $this->booking($user, $type, '2026-08-20', 1, HealthCheckup::STATUS_DONE);
        $b = $this->booking($user, $type, '2026-08-20', 2, HealthCheckup::STATUS_PENDING);
        $c = $this->booking($user, $type, '2026-08-20', 3, HealthCheckup::STATUS_PENDING);

        $info = ServiceSchedule::queueInfo('2026-08-20');

        $this->assertSame(null, $info[$done->id]['position']);
        $this->assertSame(1, $info[$b->id]['position']);
        $this->assertSame(0, $info[$b->id]['people_ahead']);
        $this->assertSame(2, $info[$c->id]['position']);
        $this->assertSame(1, $info[$c->id]['people_ahead']);
        $this->assertSame(2, $info[$c->id]['waiting']);
    }

    public function test_antrian_berkurang_saat_pasien_selesai(): void
    {
        $user = User::factory()->create();
        $type = HealthTestType::firstOrFail();

        $a = $this->booking($user, $type, '2026-08-20', 1, HealthCheckup::STATUS_PENDING);
        $b = $this->booking($user, $type, '2026-08-20', 2, HealthCheckup::STATUS_PENDING);

        $a->update(['status' => HealthCheckup::STATUS_DONE]);

        $info = ServiceSchedule::queueInfo('2026-08-20');

        $this->assertSame(null, $info[$a->id]['position']);
        $this->assertSame(1, $info[$b->id]['position']);
        $this->assertSame(1, $info[$b->id]['waiting']);
    }

    public function test_booking_dibatalkan_ditolak_tidak_masuk_antrian(): void
    {
        $user = User::factory()->create();
        $type = HealthTestType::firstOrFail();

        $this->booking($user, $type, '2026-08-20', 1, HealthCheckup::STATUS_CANCELLED);
        $b = $this->booking($user, $type, '2026-08-20', 2, HealthCheckup::STATUS_PENDING);
        $this->booking($user, $type, '2026-08-20', 3, HealthCheckup::STATUS_REJECTED);
        $c = $this->booking($user, $type, '2026-08-20', 4, HealthCheckup::STATUS_APPROVED);

        $info = ServiceSchedule::queueInfo('2026-08-20');

        $this->assertArrayNotHasKey(1, $info);
        $this->assertSame(1, $info[$b->id]['position']);
        $this->assertSame(2, $info[$c->id]['position']);
        $this->assertSame(2, $info[$c->id]['waiting']);
    }

    public function test_next_queue_mengembalikan_posisi_booking_berikutnya(): void
    {
        $user = User::factory()->create();
        $type = HealthTestType::firstOrFail();

        $this->booking($user, $type, '2026-08-20', 1, HealthCheckup::STATUS_PENDING);
        $this->booking($user, $type, '2026-08-20', 2, HealthCheckup::STATUS_DONE);

        $next = ServiceSchedule::nextQueue('2026-08-20');

        $this->assertSame(3, $next['queue_number']);
        $this->assertSame('Q-003', $next['queue_label']);
        $this->assertSame(2, $next['position']);
        $this->assertSame(1, $next['people_ahead']);
        $this->assertSame(2, $next['waiting']);
    }

    public function test_endpoint_estimate_mengembalikan_info_antrian(): void
    {
        $user = User::factory()->create();
        $type = HealthTestType::firstOrFail();

        $this->booking($user, $type, '2026-08-20', 1, HealthCheckup::STATUS_PENDING);

        $this->actingAs($user)
            ->getJson(route('health-checkups.estimate', ['date' => '2026-08-20']))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('queue.position', 2)
            ->assertJsonPath('queue.people_ahead', 1)
            ->assertJsonPath('queue.waiting', 2);
    }

    public function test_admin_dapat_mengupload_file_hasil_pemeriksaan(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();
        $type = HealthTestType::firstOrFail();
        $checkup = $this->booking($user, $type, '2026-08-20', 1, HealthCheckup::STATUS_PENDING);

        $file = UploadedFile::fake()->create('hasil.pdf', 100, 'application/pdf');

        $this->actingAs($admin)
            ->patch(route('admin.health-checkups.result', $checkup), [
                'result' => 'Negatif',
                'result_notes' => 'Semua normal.',
                'result_file' => $file,
            ])
            ->assertRedirect();

        $checkup->refresh();
        $this->assertEquals('Negatif', $checkup->result);
        $this->assertEquals('Semua normal.', $checkup->result_notes);
        $this->assertNotNull($checkup->result_file);
        \Storage::disk('local')->assertExists($checkup->result_file);
    }

    public function test_laboran_dapat_mengupload_file_hasil_pemeriksaan(): void
    {
        Storage::fake('local');
        $laboran = User::factory()->create(['role' => User::ROLE_LABORAN]);
        $user = User::factory()->create();
        $type = HealthTestType::firstOrFail();

        $checkup = $this->booking($user, $type, '2026-08-20', 1, HealthCheckup::STATUS_PENDING);
        $checkup->update(['examiner_id' => $laboran->id]);

        $file = UploadedFile::fake()->create('hasil.jpg', 100, 'image/jpeg');

        $this->actingAs($laboran)
            ->patch(route('laboran.health-checkups.result', $checkup), [
                'result' => 'Positif',
                'result_file' => $file,
            ])
            ->assertRedirect();

        $checkup->refresh();
        $this->assertEquals('Positif', $checkup->result);
        $this->assertNotNull($checkup->result_file);
        \Storage::disk('local')->assertExists($checkup->result_file);
    }

    public function test_laboran_dapat_melihat_antrian_pemeriksaan_di_dashboard(): void
    {
        $laboran = User::factory()->create(['role' => User::ROLE_LABORAN]);
        $user = User::factory()->create();
        $type = HealthTestType::firstOrFail();

        $checkup = $this->booking($user, $type, '2026-08-20', 1, HealthCheckup::STATUS_PENDING);
        $checkup->update(['examiner_id' => $laboran->id]);

        $this->actingAs($laboran)
            ->get(route('laboran.index'))
            ->assertOk()
            ->assertSee($checkup->code)
            ->assertSee('Pemeriksaan Kesehatan');
    }

    public function test_user_biasa_tidak_bisa_memproses_pemeriksaan(): void
    {
        $user = User::factory()->create();
        $laboran = User::factory()->create(['role' => User::ROLE_LABORAN]);
        $type = HealthTestType::firstOrFail();

        $checkup = $this->booking($user, $type, '2026-08-20', 1, HealthCheckup::STATUS_PENDING);
        $checkup->update(['examiner_id' => $laboran->id]);

        $this->actingAs($user)
            ->patch(route('laboran.health-checkups.status', $checkup), ['status' => HealthCheckup::STATUS_DONE])
            ->assertRedirect(route('home'));
    }
}
