<?php

namespace Tests\Feature;

use App\Jobs\GenerateCertificatesBatchJob;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Support\FormFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'code' => 'EVT-2026-001',
            'slug' => 'event-uji',
            'title' => 'Workshop Uji',
            'description' => 'Deskripsi workshop.',
            'location' => 'Lab Utama',
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(7)->addHours(6),
            'quota' => 10,
            'registration_deadline' => now()->addDays(5),
            'status' => Event::STATUS_ACTIVE,
            'form_fields' => [
                ['key' => 'nim', 'label' => 'NIM', 'type' => 'text', 'required' => true],
                ['key' => 'tshirt', 'label' => 'Ukuran Kaos', 'type' => 'select', 'options' => ['S', 'M', 'L'], 'required' => false],
            ],
            'attendance_fields' => [
                ['key' => 'feedback', 'label' => 'Kesan', 'type' => 'textarea', 'required' => false],
            ],
            'created_by' => User::factory()->create()->id,
        ], $overrides));
    }

    public function test_guest_cannot_register_event(): void
    {
        $event = $this->makeEvent();

        $this->post(route('events.store', $event), ['nim' => '123'])
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_create_event_with_forms(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();

        $formJson = json_encode([
            ['key' => 'nim', 'label' => 'NIM', 'type' => 'text', 'required' => true],
            ['key' => 'tshirt', 'label' => 'Ukuran Kaos', 'type' => 'select', 'options' => ['S', 'M', 'L'], 'required' => false],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Seminar Lab 2026',
                'description' => 'Seminar tahunan.',
                'location' => 'Aula',
                'status' => Event::STATUS_ACTIVE,
                'mode' => Event::MODE_HYBRID,
                'image' => UploadedFile::fake()->image('banner.png', 800, 450),
                'poster' => UploadedFile::fake()->image('poster.png', 800, 1131),
                'quota' => 20,
                'fee' => 150000,
                'discount' => 50000,
                'form_fields' => $formJson,
                'attendance_fields' => json_encode([
                    ['key' => 'signed', 'label' => 'Tanda Tangan', 'type' => 'checkbox', 'required' => true],
                ]),
            ])
            ->assertRedirect(route('admin.events.index'));

        $event = Event::where('title', 'Seminar Lab 2026')->firstOrFail();

        $this->assertMatchesRegularExpression('/^EVT-\d{4}-\d{3,}$/', $event->code);
        $this->assertEquals('seminar-lab-2026', $event->slug);
        $this->assertEquals(Event::MODE_HYBRID, $event->mode);
        $this->assertNotNull($event->image);
        Storage::disk('public')->assertExists($event->image);
        $this->assertNotNull($event->poster);
        Storage::disk('public')->assertExists($event->poster);
        $this->assertEquals(150000, (float) $event->fee);
        $this->assertEquals(50000, (float) $event->discount);
        $this->assertEquals(100000, $event->effective_fee);
        $this->assertCount(2, $event->form_fields);
        $this->assertEquals('tshirt', $event->form_fields[1]['key']);
        $this->assertEquals(['S', 'M', 'L'], $event->form_fields[1]['options']);
        $this->assertCount(1, $event->attendance_fields);

        $this->assertDatabaseHas('activity_logs', [
            'role' => User::ROLE_ADMIN,
            'action' => 'create',
            'subject_type' => Event::class,
            'subject_id' => $event->id,
        ]);
    }

    public function test_admin_can_update_event_mode_image_and_poster(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = $this->makeEvent(['mode' => Event::MODE_OFFLINE]);

        $this->actingAs($admin)
            ->put(route('admin.events.update', $event), [
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'starts_at' => $event->starts_at?->format('Y-m-d\TH:i'),
                'ends_at' => $event->ends_at?->format('Y-m-d\TH:i'),
                'quota' => $event->quota,
                'registration_deadline' => $event->registration_deadline?->format('Y-m-d\TH:i'),
                'status' => $event->status,
                'mode' => Event::MODE_ONLINE,
                'image' => UploadedFile::fake()->image('banner2.png', 800, 450),
                'poster' => UploadedFile::fake()->image('poster2.png', 800, 1131),
                'form_fields' => json_encode($event->form_fields),
                'attendance_fields' => json_encode($event->attendance_fields),
            ])
            ->assertRedirect(route('admin.events.show', $event));

        $event->refresh();

        $this->assertEquals(Event::MODE_ONLINE, $event->mode);
        $this->assertNotNull($event->image);
        Storage::disk('public')->assertExists($event->image);
        $this->assertNotNull($event->poster);
        Storage::disk('public')->assertExists($event->poster);
    }

    public function test_public_catalog_shows_mode_and_image(): void
    {
        $event = $this->makeEvent([
            'mode' => Event::MODE_ONLINE,
            'image' => 'events/online.png',
        ]);
        Storage::disk('public')->put($event->image, 'png');

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('Online')
            ->assertSee('events/online.png');
    }

    public function test_public_catalog_lists_active_events(): void
    {
        $this->makeEvent();

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('Workshop Uji');
    }

    public function test_public_catalog_shows_fee_and_discount(): void
    {
        $this->makeEvent(['fee' => 150000, 'discount' => 50000]);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSee('Rp 150.000')
            ->assertSee('Rp 100.000');
    }

    public function test_discount_cannot_exceed_fee(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Event Uji Diskon',
                'status' => Event::STATUS_ACTIVE,
                'fee' => 100000,
                'discount' => 150000,
                'form_fields' => '[]',
                'attendance_fields' => '[]',
            ])
            ->assertSessionHasErrors('discount');

        $this->assertDatabaseCount('events', 0);
    }

    public function test_field_keys_are_auto_generated_from_labels(): void
    {
        $fields = FormFields::normalize([
            ['key' => '', 'label' => 'NIM / NIP', 'type' => 'text', 'required' => true],
            ['key' => 'No. HP', 'label' => 'Nomor HP', 'type' => 'text', 'required' => false],
            ['label' => 'NIM / NIP', 'type' => 'textarea', 'required' => false],
        ]);

        $this->assertEquals(['nim_nip', 'no_hp', 'nim_nip_2'], array_column($fields, 'key'));
        $this->assertEquals('NIM / NIP', $fields[0]['label']);
        $this->assertTrue($fields[0]['required']);
        $this->assertFalse($fields[1]['required']);
    }

    public function test_admin_create_accepts_fields_without_key(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Event Tanpa Key',
                'status' => Event::STATUS_ACTIVE,
                'form_fields' => json_encode([
                    ['key' => '', 'label' => 'NIM / NIP', 'type' => 'text', 'required' => true],
                    ['label' => 'Ukuran Kaos', 'type' => 'select', 'options' => ['S', 'M'], 'required' => false],
                ]),
                'attendance_fields' => '[]',
            ])
            ->assertRedirect(route('admin.events.index'));

        $event = Event::where('title', 'Event Tanpa Key')->firstOrFail();
        $this->assertEquals('nim_nip', $event->form_fields[0]['key']);
        $this->assertEquals('ukuran_kaos', $event->form_fields[1]['key']);
    }

    public function test_user_can_register_with_dynamic_answers(): void
    {
        $event = $this->makeEvent();
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->post(route('events.store', $event), [
                'nim' => '2101234567',
                'tshirt' => 'M',
            ])
            ->assertRedirect(route('events.my'));

        $registration = EventRegistration::where('user_id', $user->id)->firstOrFail();
        $this->assertEquals(EventRegistration::STATUS_REGISTERED, $registration->status);
        $this->assertEquals('2101234567', $registration->answers['nim']);
        $this->assertEquals('M', $registration->answers['tshirt']);
        $this->assertNotNull($registration->attendance_token);
    }

    public function test_required_answer_must_be_filled(): void
    {
        $event = $this->makeEvent();
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->post(route('events.store', $event), ['tshirt' => 'M'])
            ->assertSessionHasErrors('nim');

        $this->assertDatabaseCount('event_registrations', 0);
    }

    public function test_duplicate_registration_is_rejected(): void
    {
        $event = $this->makeEvent();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        $this->actingAs($user)
            ->post(route('events.store', $event), ['nim' => '2101234567'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('event_registrations', 1);
    }

    public function test_registration_closed_when_event_not_active(): void
    {
        $event = $this->makeEvent(['status' => Event::STATUS_CLOSED]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->post(route('events.store', $event), ['nim' => '2101234567'])
            ->assertStatus(403);

        $this->assertDatabaseCount('event_registrations', 0);
    }

    public function test_user_can_fill_attendance_via_token_link(): void
    {
        $event = $this->makeEvent();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventRegistration::STATUS_REGISTERED,
            'attendance_token' => 'token-presensi-abc',
        ]);

        $this->actingAs($user)
            ->get(route('events.attendance', $registration->attendance_token))
            ->assertOk()
            ->assertSee('Form Presensi');

        $this->actingAs($user)
            ->post(route('events.attendance.store', $registration->attendance_token), [
                'feedback' => 'Seru sekali!',
            ])
            ->assertRedirect(route('events.my'));

        $registration->refresh();
        $this->assertNotNull($registration->attended_at);
        $this->assertEquals('Seru sekali!', $registration->attendance_answers['feedback']);
    }

    public function test_other_user_cannot_fill_attendance(): void
    {
        $event = $this->makeEvent();
        $owner = User::factory()->create(['role' => User::ROLE_USER]);
        $other = User::factory()->create(['role' => User::ROLE_USER]);
        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $owner->id,
            'status' => EventRegistration::STATUS_REGISTERED,
            'attendance_token' => 'token-presensi-xyz',
        ]);

        $this->actingAs($other)
            ->post(route('events.attendance.store', $registration->attendance_token), ['feedback' => 'ok'])
            ->assertStatus(403);

        $this->assertNull($registration->refresh()->attended_at);
    }

    public function test_admin_can_generate_certificates_for_attended_only(): void
    {
        Storage::fake('public');
        Bus::fake();
        $admin = User::factory()->create();

        $event = $this->makeEvent();
        $user = User::factory()->create(['role' => User::ROLE_USER, 'name' => 'Peserta Hadir']);

        $attended = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventRegistration::STATUS_REGISTERED,
            'attended_at' => now(),
        ]);

        $absentUser = User::factory()->create(['role' => User::ROLE_USER]);
        $absent = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $absentUser->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        // Atur template + tata letak sertifikat (depan & belakang).
        $this->actingAs($admin)
            ->post(route('admin.events.certificate.save', $event), [
                'certificate_template' => UploadedFile::fake()->image('master.png', 1240, 876),
                'certificate_layout' => json_encode([
                    ['type' => 'name', 'x' => 50, 'y' => 60, 'size' => 44, 'color' => '#1e293b', 'align' => 'center', 'font' => 'lato', 'weight' => 'bold', 'enabled' => true],
                    ['type' => 'event_title', 'x' => 50, 'y' => 72, 'size' => 22, 'font' => 'regular', 'enabled' => true],
                ]),
                'certificate_template_back' => UploadedFile::fake()->image('master-back.png', 1240, 876),
                'certificate_layout_back' => json_encode([
                    ['type' => 'name', 'x' => 50, 'y' => 40, 'size' => 32, 'color' => '#334155', 'align' => 'center', 'font' => 'great_vibes', 'weight' => 'regular', 'enabled' => true],
                ]),
            ])
            ->assertRedirect();

        $event->refresh();
        $this->assertNotNull($event->certificate_template);
        $this->assertNotNull($event->certificate_template_back);
        // Layout hanya menyimpan baris nama; baris selain 'name' dibuang.
        $this->assertCount(1, $event->certificate_layout);
        $this->assertEquals('name', $event->certificate_layout[0]['type']);
        $this->assertEquals('lato', $event->certificate_layout[0]['font']);
        $this->assertEquals('great_vibes', $event->certificate_layout_back[0]['font']);

        // Generate — dispatch batch job ke queue.
        $this->actingAs($admin)
            ->post(route('admin.events.certificate.generate', $event))
            ->assertRedirect()
            ->assertSessionHas('success');

        Bus::assertDispatched(GenerateCertificatesBatchJob::class, function ($job) use ($event) {
            return $job->event->id === $event->id;
        });

        // jalankan batch job secara sync untuk verifikasi
        $batchJob = new GenerateCertificatesBatchJob($event);
        $batchJob->handle();

        $attended->refresh();
        $absent->refresh();

        $this->assertEquals('pending', $attended->certificate_status);
        $this->assertNull($absent->certificate_number);
    }

    public function test_admin_can_delete_certificate_back_side(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = $this->makeEvent([
            'certificate_template' => 'events/templates/front.png',
            'certificate_layout' => [['type' => 'name', 'x' => 50, 'y' => 60, 'size' => 44, 'color' => '#1e293b', 'align' => 'center', 'font' => 'lato', 'weight' => 'bold', 'enabled' => true]],
            'certificate_template_back' => 'events/templates/back.png',
            'certificate_layout_back' => [['type' => 'name', 'x' => 50, 'y' => 40, 'size' => 32, 'color' => '#334155', 'align' => 'center', 'font' => 'great_vibes', 'weight' => 'regular', 'enabled' => true]],
        ]);
        Storage::disk('public')->put('events/templates/front.png', 'png');
        Storage::disk('public')->put('events/templates/back.png', 'png');

        $this->actingAs($admin)
            ->delete(route('admin.events.certificate.back-delete', $event))
            ->assertRedirect();

        $event->refresh();
        $this->assertNull($event->certificate_template_back);
        $this->assertNull($event->certificate_layout_back);
        $this->assertNotNull($event->certificate_template);
        Storage::disk('public')->assertMissing('events/templates/back.png');
    }

    public function test_save_layout_accepts_legacy_format_without_weight_key(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = $this->makeEvent();

        // Format lama (cache browser) tidak menyertakan key 'weight'/'font' family.
        $this->actingAs($admin)
            ->post(route('admin.events.certificate.save', $event), [
                'certificate_template' => UploadedFile::fake()->image('master.png', 1240, 876),
                'certificate_layout' => json_encode([
                    ['type' => 'name', 'x' => 50, 'y' => 60, 'size' => 44, 'color' => '#000000', 'align' => 'center', 'font' => 'bold', 'enabled' => true],
                ]),
                'certificate_layout_back' => json_encode([]),
            ])
            ->assertRedirect();

        $event->refresh();
        $this->assertCount(1, $event->certificate_layout);
        $this->assertEquals('name', $event->certificate_layout[0]['type']);
        $this->assertEquals('lato', $event->certificate_layout[0]['font']);
        $this->assertEquals('bold', $event->certificate_layout[0]['weight']);
        $this->assertEquals('#000000', $event->certificate_layout[0]['color']);
    }

    public function test_certificate_preview_renders_before_generate(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = $this->makeEvent([
            'certificate_template' => 'events/templates/front.png',
            'certificate_layout' => [['type' => 'name', 'x' => 50, 'y' => 60, 'size' => 44, 'color' => '#1e293b', 'align' => 'center', 'font' => 'lato', 'weight' => 'bold', 'enabled' => true]],
            'certificate_template_back' => 'events/templates/back.png',
            'certificate_layout_back' => [['type' => 'name', 'x' => 50, 'y' => 40, 'size' => 32, 'color' => '#334155', 'align' => 'center', 'font' => 'great_vibes', 'weight' => 'regular', 'enabled' => true]],
        ]);
        Storage::disk('public')->put('events/templates/front.png', (string) UploadedFile::fake()->image('front.png', 1240, 876)->getContent());
        Storage::disk('public')->put('events/templates/back.png', (string) UploadedFile::fake()->image('back.png', 1240, 876)->getContent());

        $this->actingAs($admin)
            ->get(route('admin.events.certificate', $event))
            ->assertOk()
            ->assertSee('Pratinjau Render Asli');

        $this->assertNotNull($event->certificate_template_back);
        Storage::disk('public')->assertExists('events/'.$event->id.'/preview-front.png');
        Storage::disk('public')->assertExists('events/'.$event->id.'/preview-back.png');
    }

    public function test_user_event_history_page_renders_with_account_menu(): void
    {
        $event = $this->makeEvent();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventRegistration::STATUS_REGISTERED,
            'attendance_token' => 'token-riwayat-1',
        ]);

        $this->actingAs($user)
            ->get(route('events.my'))
            ->assertOk()
            ->assertSee('Riwayat Event')
            ->assertSee('Peminjaman Alat')
            ->assertSee('Workshop Uji')
            ->assertSee('Isi Presensi');
    }

    public function test_certificate_editor_does_not_500_on_problematic_template(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = $this->makeEvent([
            'certificate_template' => 'events/templates/broken.png',
            'certificate_layout' => [['type' => 'name', 'x' => 50, 'y' => 60, 'size' => 44, 'color' => '#1e293b', 'align' => 'center', 'font' => 'lato', 'weight' => 'bold', 'enabled' => true]],
        ]);
        // File bukan gambar valid (imagecreatefromstring akan mengembalikan false / warning libpng).
        Storage::disk('public')->put('events/templates/broken.png', 'not-a-real-png');

        $this->actingAs($admin)
            ->get(route('admin.events.certificate', $event))
            ->assertOk()
            ->assertSee('Pratinjau Render Asli');
    }

    public function test_certificate_editor_page_renders_and_does_not_nest_delete_form(): void
    {
        $admin = User::factory()->create();
        $event = $this->makeEvent();

        $this->actingAs($admin)
            ->get(route('admin.events.certificate', $event))
            ->assertOk()
            ->assertSee('Tata Letak Sertifikat')
            ->assertSee('certificate-layout')
            ->assertSee('Sisi Belakang');

        // Form hapus belakang harus berada DI LUAR form simpan (tanpa form bersarang).
        $html = $this->actingAs($admin)
            ->get(route('admin.events.certificate', $event))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('back-delete-wrap', $html);
        $this->assertSame(
            substr_count($html, '<form'),
            substr_count($html, '</form>'),
            'Jumlah tag form tidak seimbang (ada form bersarang).'
        );

        // Route hapus belakang hanya menerima DELETE — tidak boleh 405.
        $this->actingAs($admin)
            ->delete(route('admin.events.certificate.back-delete', $event))
            ->assertRedirect();
    }

    public function test_certificate_visible_to_owner_and_admin(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create();
        $event = $this->makeEvent();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $other = User::factory()->create(['role' => User::ROLE_USER]);

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventRegistration::STATUS_REGISTERED,
            'attended_at' => now(),
            'certificate_number' => 'CERT-2026-0001',
            'certificate_path' => 'events/'.$event->id.'/certificate-'.$event->id.'.png',
            'certificate_generated_at' => now(),
        ]);

        Storage::disk('public')->put($registration->certificate_path, 'png');

        $this->actingAs($user)
            ->get(route('events.certificate', $registration))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('events.certificate', $registration))
            ->assertOk();

        $this->actingAs($other)
            ->get(route('events.certificate', $registration))
            ->assertStatus(403);
    }

    public function test_admin_can_export_participants(): void
    {
        $admin = User::factory()->create();
        $event = $this->makeEvent();
        $user = User::factory()->create(['name' => 'Peserta Export']);

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventRegistration::STATUS_REGISTERED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.events.export', $event));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
