<?php

namespace Tests\Feature;

use App\Models\Laboratorium;
use App\Models\ResearchLogbook;
use App\Models\ResearchProposal;
use App\Models\Tool;
use App\Models\ToolCategory;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ResearchProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_research_excel_with_filters(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER, 'nim_nip' => '2019123456', 'institution' => 'Universitas Contoh']);

        $proposalSatu = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-XLSX-001',
            'title' => 'Riset Export Satu',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_APPROVED,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
            'bench_fee' => 100000,
            'laboran_fee' => 50000,
            'penalty' => 25000,
            'payment_status' => ResearchProposal::PAYMENT_PAID,
            'invoice_number' => 'INV-RST-XLSX-001',
        ]);
        $proposalSatu->forceFill(['created_at' => now()->subDays(2)])->save();

        $proposalDua = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-XLSX-002',
            'title' => 'Riset Export Dua',
            'field' => 'Biologi',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
        ]);
        $proposalDua->forceFill(['created_at' => now()->subDays(10)])->save();

        // Tanpa filter → semua data.
        $response = $this->actingAs($admin)->get(route('admin.research.export'));
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $values = $this->xlsxValues($response->streamedContent());
        $all = implode('|', $values);
        $this->assertStringContainsString('RST-XLSX-001', $all);
        $this->assertStringContainsString('RST-XLSX-002', $all);
        $this->assertStringContainsString('Disetujui', $all);
        $this->assertStringContainsString('INV-RST-XLSX-001', $all);

        // Filter status → hanya yang sesuai.
        $response = $this->actingAs($admin)->get(route('admin.research.export', ['status' => 'approved']));
        $values = $this->xlsxValues($response->streamedContent());
        $all = implode('|', $values);
        $this->assertStringContainsString('RST-XLSX-001', $all);
        $this->assertStringNotContainsString('RST-XLSX-002', $all);

        // Filter tanggal → hanya dalam rentang.
        $response = $this->actingAs($admin)->get(route('admin.research.export', [
            'date_from' => now()->subDays(5)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]));
        $values = $this->xlsxValues($response->streamedContent());
        $all = implode('|', $values);
        $this->assertStringContainsString('RST-XLSX-001', $all);
        $this->assertStringNotContainsString('RST-XLSX-002', $all);
    }

    public function test_user_can_view_research_proposal_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('research.create'))
            ->assertOk()
            ->assertSee('Ajukan Permohonan Riset &amp; Penelitian', false)
            ->assertSee('Judul Riset', false)
            ->assertSee('Bidang Penelitian', false)
            ->assertSee('Estimasi Biaya', false)
            ->assertSee('Total Estimasi', false);
    }

    public function test_guest_cannot_access_research_pages(): void
    {
        $this->get(route('research.index'))->assertRedirect(route('login'));
        $this->get(route('research.create'))->assertRedirect(route('login'));
    }

    public function test_user_can_submit_research_proposal(): void
    {
        $user = User::factory()->create();
        $tool = Tool::create([
            'code' => 'AL-RST-01',
            'name' => 'Spektrometer Riset',
            'category_id' => ToolCategory::create(['name' => 'Uji'])->id,
            'total_stock' => 5,
            'available_stock' => 5,
            'price_per_day' => 100000,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('research.store'), [
                'title' => 'Analisis Logam Berat pada Air Sungai',
                'field' => 'Kimia Analitik',
                'description' => 'Penelitian tentang kandungan logam berat pada air sungai.',
                'objectives' => 'Mengukur kadar Pb dan Hg.',
                'nim_nip' => '2019123456',
                'institution' => 'Universitas Contoh',
                'customer_type' => 'mahasiswa',
                'start_date' => now()->addDay()->format('Y-m-d'),
                'end_date' => now()->addDays(30)->format('Y-m-d'),
                'letter' => UploadedFile::fake()->create('surat-permohonan.pdf', 20),
                'replacement_letter' => UploadedFile::fake()->create('surat-penggantian.pdf', 20),
                'tools' => [$tool->id],
                'quantities' => [$tool->id => 2],
                'days' => [$tool->id => 10],
                'members' => [
                    ['name' => 'Dr. Peneliti Utama', 'role' => 'Ketua'],
                    ['name' => 'Andi', 'role' => 'Anggota'],
                ],
                'bench_fee_level' => 'S2/S3',
                'bench_fee_type' => 'luar',
                'bench_fee_category' => 'biomedis',
                'needs_laboran' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('research_proposals', [
            'user_id' => $user->id,
            'title' => 'Analisis Logam Berat pada Air Sungai',
            'status' => ResearchProposal::STATUS_PENDING,
        ]);

        $proposal = ResearchProposal::first();
        $this->assertMatchesRegularExpression('/^RST-\d{8}-[A-Z0-9]{5}$/', $proposal->code);
        $this->assertNotNull($proposal->letter_path);

        // Anggota tersimpan.
        $this->assertDatabaseHas('research_proposal_members', [
            'research_proposal_id' => $proposal->id,
            'name' => 'Dr. Peneliti Utama',
            'role' => 'Ketua',
        ]);
        $this->assertDatabaseCount('research_proposal_members', 2);

        // Alat yang dibutuhkan tersimpan dengan jumlah.
        $this->assertDatabaseHas('research_proposal_tools', [
            'research_proposal_id' => $proposal->id,
            'tool_id' => $tool->id,
            'quantity' => 2,
            'days' => 10,
        ]);

        // Bench fee: S2/S3 luar biomedis (200.000) × 30 hari ≈ 2 bulan → 1 periode.
        $proposal->refresh();
        $this->assertEquals(200000, $proposal->bench_fee);
        $this->assertEquals('S2/S3', $proposal->bench_fee_level);
        $this->assertEquals('luar', $proposal->bench_fee_type);
        $this->assertEquals('biomedis', $proposal->bench_fee_category);
        $this->assertTrue($proposal->needs_laboran);
        $this->assertEquals('2019123456', $proposal->nim_nip);
        $this->assertEquals('Universitas Contoh', $proposal->institution);
        $this->assertNotNull($proposal->replacement_letter_path);
    }

    public function test_bench_fee_calculation_scales_with_duration(): void
    {
        // S1 dalam biomedis = 75.000 per 3 bulan.
        // 7 bulan (mis. 1 Jan - 31 Jul) → ceil(7/3) = 3 periode → 225.000.
        $this->assertEquals(225000, ResearchProposal::calculateBenchFee('S1', 'dalam', 'biomedis', '2026-01-01', '2026-07-31'));

        // S2/S3 luar biomedis = 200.000 per 3 bulan.
        // 3 bulan → 1 periode → 200.000.
        $this->assertEquals(200000, ResearchProposal::calculateBenchFee('S2/S3', 'luar', 'biomedis', '2026-03-01', '2026-05-31'));

        // S1 luar non-biomedis = 100.000 per 3 bulan. 1 bulan → 1 periode → 100.000.
        $this->assertEquals(100000, ResearchProposal::calculateBenchFee('S1', 'luar', 'non-biomedis', '2026-06-01', '2026-06-30'));
    }

    public function test_research_proposal_requires_title_field_letter_and_tools(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('research.store'), [
                'title' => '',
                'field' => '',
                'description' => 'Deskripsi.',
                'start_date' => now()->addDay()->format('Y-m-d'),
                'end_date' => now()->addDays(10)->format('Y-m-d'),
                // tanpa letter, tanpa tools, tanpa bench fee
            ])
            ->assertSessionHasErrors(['title', 'field', 'letter', 'bench_fee_level', 'bench_fee_type', 'bench_fee_category', 'institution', 'replacement_letter', 'nim_nip', 'customer_type']);

        $this->assertDatabaseCount('research_proposals', 0);
    }

    public function test_informasi_pemohon_fields_always_required(): void
    {
        $tool = Tool::create([
            'code' => 'AL-RST-03',
            'name' => 'Sentrifuge Riset',
            'category_id' => ToolCategory::create(['name' => 'Uji'])->id,
            'total_stock' => 4,
            'available_stock' => 4,
            'price_per_day' => 100000,
            'is_active' => true,
        ]);

        // Field informasi pemohon wajib diisi, meskipun profil akun sudah lengkap.
        $withProfile = User::factory()->create([
            'nim_nip' => '1987654321',
            'institution' => 'Institut Contoh',
        ]);

        $this->actingAs($withProfile)
            ->post(route('research.store'), $this->validProposalPayload($tool, [
                'nim_nip' => '',
                'institution' => '',
                'customer_type' => '',
            ]))
            ->assertSessionHasErrors(['nim_nip', 'institution', 'customer_type']);

        // Lengkapi semua field → permohonan tersimpan.
        $this->actingAs($withProfile)
            ->post(route('research.store'), $this->validProposalPayload($tool, [
                'nim_nip' => '1987654321',
                'institution' => 'Institut Contoh',
                'customer_type' => 'dosentendik',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $proposal = ResearchProposal::where('user_id', $withProfile->id)->first();
        $this->assertNotNull($proposal);
        $this->assertEquals('1987654321', $proposal->nim_nip);
        $this->assertEquals('Institut Contoh', $proposal->institution);
        $this->assertEquals('dosentendik', $proposal->customer_type);
    }

    public function test_research_proposal_detail_shows_team_tools_and_letters(): void
    {
        $user = User::factory()->create();
        $tool = Tool::create([
            'code' => 'AL-RST-02',
            'name' => 'Mikroskop Riset',
            'category_id' => ToolCategory::create(['name' => 'Optik'])->id,
            'total_stock' => 3,
            'available_stock' => 3,
            'price_per_day' => 50000,
            'is_active' => true,
        ]);

        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-00006',
            'title' => 'Riset Lengkap',
            'field' => 'Biologi',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
            'letter_path' => 'research-letters/surat.pdf',
        ]);

        $proposal->members()->create(['name' => 'Anggota Satu', 'role' => 'Peneliti']);
        $proposal->tools()->attach($tool->id, ['quantity' => 3, 'days' => 7]);

        $this->actingAs($user)
            ->get(route('research.show', $proposal))
            ->assertOk()
            ->assertSee('Anggota Satu', false)
            ->assertSee('Mikroskop Riset', false)
            ->assertSee('Surat Permohonan', false)
            ->assertSee('surat.pdf', false);
    }

    public function test_user_sees_own_research_history(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-00001',
            'title' => 'Riset Milik Saya',
            'field' => 'Biologi',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
        ]);

        ResearchProposal::create([
            'user_id' => $other->id,
            'code' => 'RST-TEST-00002',
            'title' => 'Riset Orang Lain',
            'field' => 'Fisika',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
        ]);

        $this->actingAs($user)
            ->get(route('research.index'))
            ->assertOk()
            ->assertSee('Riset Milik Saya', false)
            ->assertDontSee('Riset Orang Lain', false);
    }

    public function test_user_cannot_view_others_proposal(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $proposal = ResearchProposal::create([
            'user_id' => $other->id,
            'code' => 'RST-TEST-00003',
            'title' => 'Riset Rahasia',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
        ]);

        $this->actingAs($user)
            ->get(route('research.show', $proposal))
            ->assertForbidden();
    }

    public function test_admin_can_update_status_and_notify_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create();

        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-00004',
            'title' => 'Riset Ditolak',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.research.status', $proposal), [
                'status' => ResearchProposal::STATUS_REJECTED,
                'admin_notes' => 'Dokumen belum lengkap.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $proposal->refresh();
        $this->assertEquals(ResearchProposal::STATUS_REJECTED, $proposal->status);
        $this->assertEquals('Dokumen belum lengkap.', $proposal->admin_notes);
        $this->assertNotNull($proposal->rejected_at);
        $this->assertNull($proposal->approved_at);

        Notification::assertSentTo($user, BorrowingNotification::class);
    }

    public function test_admin_can_mark_proposal_as_ongoing(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $admin = User::factory()->create();

        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-ONGOING',
            'title' => 'Riset Berlangsung',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_APPROVED,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.research.status', $proposal), [
                'status' => ResearchProposal::STATUS_ONGOING,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $proposal->refresh();
        $this->assertEquals(ResearchProposal::STATUS_ONGOING, $proposal->status);
        $this->assertNotNull($proposal->ongoing_at);
        $this->assertEquals('Sedang Berlangsung', ResearchProposal::statusLabel($proposal->status));

        Notification::assertSentTo($user, BorrowingNotification::class);
    }

    public function test_client_research_history_shows_status_tracker_with_wib_time(): void
    {
        $user = User::factory()->create();

        ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-TRACKER',
            'title' => 'Riset Tracker',
            'field' => 'Biologi',
            'status' => ResearchProposal::STATUS_ONGOING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
            'created_at' => now()->subDays(3),
            'ongoing_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('research.index'))
            ->assertOk()
            ->assertSee('Riset Tracker', false)
            ->assertSee('Diajukan', false)
            ->assertSee('Disetujui', false)
            ->assertSee('Berlangsung', false)
            ->assertSee('Selesai', false)
            ->assertSee('WIB', false);
    }

    public function test_admin_can_manage_research_proposals(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();

        ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-00005',
            'title' => 'Riset Pengujian',
            'field' => 'Biologi Molekuler',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(14),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.research.index'))
            ->assertOk()
            ->assertSee('Riset Pengujian', false)
            ->assertSee('Permohonan Riset &amp; Penelitian', false);

        $this->actingAs($admin)
            ->get(route('admin.research.show', ResearchProposal::first()))
            ->assertOk()
            ->assertSee('Ubah Status', false)
            ->assertSee('Catatan Admin', false);
    }

    public function test_admin_can_assign_laboran_and_laboratorium_with_fee(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $laboran = User::factory()->create(['role' => User::ROLE_LABORAN, 'name' => 'Sari Laboran']);
        $lab = Laboratorium::create(['name' => 'Laboratorium Kimia']);

        $tool = Tool::create([
            'code' => 'AL-RST-04',
            'name' => 'Spektrometer Penugasan',
            'category_id' => ToolCategory::create(['name' => 'Uji'])->id,
            'total_stock' => 5,
            'available_stock' => 5,
            'price_per_day' => 50000,
            'is_active' => true,
        ]);

        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-ASSIGN',
            'title' => 'Riset Penugasan',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
            'bench_fee' => 100000,
        ]);
        $proposal->tools()->attach($tool->id, ['quantity' => 3, 'days' => 7]);

        $this->actingAs($admin)
            ->patch(route('admin.research.assignment', $proposal), [
                'laboran_id' => $laboran->id,
                'laboran_fee' => 150000,
                'laboratorium_id' => $lab->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $proposal->refresh();
        $this->assertEquals($laboran->id, $proposal->laboran_id);
        $this->assertEquals(150000, $proposal->laboran_fee);
        $this->assertEquals($lab->id, $proposal->laboratorium_id);

        // Total sewa alat: 50.000 × 3 × 7 hari = 1.050.000 + bench fee (100.000) + biaya laboran (150.000) = 1.300.000.
        $proposal->load('tools');
        $this->assertEquals(1050000, $proposal->tools_subtotal);
        $this->assertEquals(1300000, $proposal->grand_total);
        $this->assertEquals('Rp 1.300.000', $proposal->formatted_grand_total);
    }

    public function test_client_detail_shows_assigned_laboran_and_laboratorium(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $laboran = User::factory()->create(['role' => User::ROLE_LABORAN, 'name' => 'Dewi Laboran']);
        $lab = Laboratorium::create(['name' => 'Laboratorium Biologi Molekuler']);

        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-CLIENT',
            'title' => 'Riset Klien',
            'field' => 'Biologi',
            'status' => ResearchProposal::STATUS_APPROVED,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
            'needs_laboran' => true,
            'laboran_id' => $laboran->id,
            'laboratorium_id' => $lab->id,
        ]);

        $this->actingAs($user)
            ->get(route('research.show', $proposal))
            ->assertOk()
            ->assertSee('Dewi Laboran', false)
            ->assertSee('Laboratorium Biologi Molekuler', false)
            ->assertSee('Laboran:', false)
            ->assertSee('Laboratorium:', false);
    }

    public function test_laboran_assignment_requires_laboran_role(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-ASSIGN2',
            'title' => 'Riset Validasi Laboran',
            'field' => 'Biologi',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.research.assignment', $proposal), [
                'laboran_id' => $user->id,
            ])
            ->assertSessionHasErrors('laboran_id');
    }

    /**
     * Baca semua nilai sel dari konten biner .xlsx menjadi array datar.
     *
     * @return array<int, string>
     */
    private function xlsxValues(string $binary): array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($tmp, $binary);

        try {
            $reader = IOFactory::createReaderForFile($tmp);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($tmp);
            $values = [];
            foreach ($spreadsheet->getActiveSheet()->toArray(null, true, true, true) as $row) {
                foreach (array_values($row) as $cell) {
                    $values[] = (string) $cell;
                }
            }
            $spreadsheet->disconnectWorksheets();

            return $values;
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * Payload valid untuk pengajuan riset.
     */
    protected function validProposalPayload(Tool $tool, array $overrides = []): array
    {
        return array_merge([
            'title' => 'Riset Validasi Form',
            'field' => 'Biologi',
            'description' => 'Deskripsi penelitian.',
            'objectives' => 'Tujuan penelitian.',
            'nim_nip' => '2019123456',
            'institution' => 'Universitas Contoh',
            'customer_type' => 'mahasiswa',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
            'letter' => UploadedFile::fake()->create('surat-permohonan.pdf', 20),
            'replacement_letter' => UploadedFile::fake()->create('surat-penggantian.pdf', 20),
            'tools' => [$tool->id],
            'quantities' => [$tool->id => 1],
            'days' => [$tool->id => 30],
            'bench_fee_level' => 'S1',
            'bench_fee_type' => 'dalam',
            'bench_fee_category' => 'biomedis',
        ], $overrides);
    }

    protected function createResearchTool(): Tool
    {
        return Tool::create([
            'code' => 'AL-RST-'.rand(1000, 9999),
            'name' => 'Spektrometer Riset',
            'category_id' => ToolCategory::create(['name' => 'Uji'])->id,
            'total_stock' => 5,
            'available_stock' => 5,
            'price_per_day' => 100000,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_and_update_bench_fee_rates(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.bench-fee.index'))
            ->assertOk()
            ->assertSee('Tarif Bench Fee', false)
            ->assertSee('Non-Biomedis', false);

        $this->actingAs($admin)
            ->put(route('admin.bench-fee.update'), [
                'rates' => [
                    ['level' => 'S1', 'type' => 'dalam', 'category' => 'biomedis', 'rate' => 80000],
                    ['level' => 'S1', 'type' => 'dalam', 'category' => 'non-biomedis', 'rate' => 50000],
                    ['level' => 'S1', 'type' => 'luar', 'category' => 'biomedis', 'rate' => 110000],
                    ['level' => 'S1', 'type' => 'luar', 'category' => 'non-biomedis', 'rate' => 90000],
                    ['level' => 'S2/S3', 'type' => 'dalam', 'category' => 'biomedis', 'rate' => 160000],
                    ['level' => 'S2/S3', 'type' => 'dalam', 'category' => 'non-biomedis', 'rate' => 130000],
                    ['level' => 'S2/S3', 'type' => 'luar', 'category' => 'biomedis', 'rate' => 210000],
                    ['level' => 'S2/S3', 'type' => 'luar', 'category' => 'non-biomedis', 'rate' => 180000],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bench_fee_rates', [
            'level' => 'S1',
            'type' => 'dalam',
            'category' => 'biomedis',
            'rate' => 80000,
        ]);

        $rates = ResearchProposal::benchFeeRates();
        $this->assertEquals(80000, $rates['S1']['dalam']['biomedis']);
        $this->assertEquals(50000, $rates['S1']['dalam']['non-biomedis']);
        $this->assertEquals(210000, $rates['S2/S3']['luar']['biomedis']);

        // Kalkulasi ikut memakai tarif terbaru sesuai kategori.
        $this->assertEquals(240000, ResearchProposal::calculateBenchFee('S1', 'dalam', 'biomedis', '2026-01-01', '2026-07-31'));
        $this->assertEquals(150000, ResearchProposal::calculateBenchFee('S1', 'dalam', 'non-biomedis', '2026-01-01', '2026-07-31'));
    }

    public function test_bench_fee_rates_reject_invalid_values(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.bench-fee.update'), [
                'rates' => [
                    ['level' => 'S1', 'type' => 'dalam', 'category' => 'biomedis', 'rate' => -5],
                ],
            ])
            ->assertSessionHasErrors(['rates.0.rate']);

        $this->actingAs($admin)
            ->put(route('admin.bench-fee.update'), [
                'rates' => [
                    ['level' => 'S1', 'type' => 'dalam', 'category' => 'teknologi', 'rate' => 100000],
                ],
            ])
            ->assertSessionHasErrors(['rates.0.category']);
    }

    public function test_admin_can_mark_research_payment_as_paid_and_generate_invoice(): void
    {
        $user = User::factory()->create(['nim_nip' => '2019123456', 'institution' => 'Universitas Contoh']);
        $admin = User::factory()->create();
        $tool = $this->createResearchTool();

        $this->actingAs($user)->post(route('research.store'), $this->validProposalPayload($tool));

        $proposal = ResearchProposal::where('user_id', $user->id)->first();
        $this->assertNotNull($proposal);
        $this->assertNull($proposal->invoice_number);
        $this->assertEquals(ResearchProposal::PAYMENT_UNPAID, $proposal->payment_status);

        $this->actingAs($admin)
            ->patch(route('admin.research.payment', $proposal), ['payment_status' => ResearchProposal::PAYMENT_PAID])
            ->assertRedirect()
            ->assertSessionHas('success');

        $proposal->refresh();
        $this->assertEquals(ResearchProposal::PAYMENT_PAID, $proposal->payment_status);
        $this->assertNotNull($proposal->invoice_number);
        $this->assertStringStartsWith('INV-RST-', $proposal->invoice_number);
    }

    public function test_owner_can_view_research_invoice_but_others_cannot(): void
    {
        $user = User::factory()->create(['nim_nip' => '2019123456', 'institution' => 'Universitas Contoh']);
        $other = User::factory()->create();
        $admin = User::factory()->create();
        $tool = $this->createResearchTool();

        $this->actingAs($user)->post(route('research.store'), $this->validProposalPayload($tool));
        $proposal = ResearchProposal::where('user_id', $user->id)->first();

        $this->actingAs($admin)
            ->patch(route('admin.research.payment', $proposal), ['payment_status' => ResearchProposal::PAYMENT_PAID]);
        $proposal->refresh();

        $this->actingAs($user)
            ->get(route('research.invoice', $proposal))
            ->assertOk()
            ->assertSee($proposal->invoice_number)
            ->assertSee('Invoice');

        $this->actingAs($other)
            ->get(route('research.invoice', $proposal))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.research.invoice', $proposal))
            ->assertOk()
            ->assertSee($proposal->invoice_number);
    }

    public function test_admin_can_mark_research_payment_back_to_unpaid(): void
    {
        $user = User::factory()->create(['nim_nip' => '2019123456', 'institution' => 'Universitas Contoh']);
        $admin = User::factory()->create();
        $tool = $this->createResearchTool();

        $this->actingAs($user)->post(route('research.store'), $this->validProposalPayload($tool));
        $proposal = ResearchProposal::where('user_id', $user->id)->first();

        $this->actingAs($admin)
            ->patch(route('admin.research.payment', $proposal), ['payment_status' => ResearchProposal::PAYMENT_PAID]);
        $proposal->refresh();
        $this->assertNotNull($proposal->invoice_number);

        $this->actingAs($admin)
            ->patch(route('admin.research.payment', $proposal), ['payment_status' => ResearchProposal::PAYMENT_UNPAID])
            ->assertRedirect()
            ->assertSessionHas('success');

        $proposal->refresh();
        $this->assertEquals(ResearchProposal::PAYMENT_UNPAID, $proposal->payment_status);
    }

    public function test_admin_can_set_penalty_and_grand_total_includes_it(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER, 'nim_nip' => '2019123456', 'institution' => 'Universitas Contoh']);
        $tool = Tool::create([
            'code' => 'AL-RST-PLT',
            'name' => 'Mikroskop Riset',
            'category_id' => ToolCategory::create(['name' => 'Uji'])->id,
            'total_stock' => 2,
            'available_stock' => 2,
            'price_per_day' => 50000,
            'is_active' => true,
        ]);

        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-PENALTY',
            'title' => 'Riset Denda',
            'field' => 'Biologi',
            'status' => ResearchProposal::STATUS_ONGOING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
            'bench_fee' => 100000,
        ]);
        $proposal->tools()->attach($tool->id, ['quantity' => 1, 'days' => 7]);

        $this->assertEquals(0, $proposal->penalty);
        $before = $proposal->grand_total;

        $this->actingAs($admin)
            ->patch(route('admin.research.penalty', $proposal), [
                'penalty' => 250000,
                'penalty_note' => '1 unit mikroskop rusak saat penelitian',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $proposal->refresh();
        $this->assertEquals(250000, $proposal->penalty);
        $this->assertEquals('1 unit mikroskop rusak saat penelitian', $proposal->penalty_note);
        $this->assertEquals($before + 250000, $proposal->grand_total);

        // Detail client menampilkan denda.
        $this->actingAs($user)
            ->get(route('research.show', $proposal))
            ->assertOk()
            ->assertSee('Denda / Biaya Tambahan')
            ->assertSee('Rp 250.000')
            ->assertSee('1 unit mikroskop rusak saat penelitian');
    }

    public function test_penalty_rejects_negative_or_oversized_values(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-PLT-INV',
            'title' => 'Riset Denda Invalid',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.research.penalty', $proposal), ['penalty' => -5])
            ->assertSessionHasErrors('penalty');

        $this->actingAs($admin)
            ->patch(route('admin.research.penalty', $proposal), ['penalty' => 999999999999])
            ->assertSessionHasErrors('penalty');
    }

    public function test_owner_can_add_logbook_only_when_ongoing(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER, 'nim_nip' => '2019123456', 'institution' => 'Universitas Contoh']);
        $tool = $this->createResearchTool();

        $this->actingAs($user)->post(route('research.store'), $this->validProposalPayload($tool));
        $proposal = ResearchProposal::where('user_id', $user->id)->first();

        // Belum ongoing → dilarang menambah logbook.
        $this->actingAs($user)
            ->post(route('research.logbook.store', $proposal), [
                'log_date' => now()->format('Y-m-d'),
                'note' => 'Persiapan sampel.',
            ])
            ->assertForbidden();

        // Admin tandai sedang berlangsung.
        $admin = User::factory()->create();
        $this->actingAs($admin)
            ->patch(route('admin.research.status', $proposal), ['status' => ResearchProposal::STATUS_ONGOING]);
        $proposal->refresh();

        // Sekarang boleh menambah.
        $this->actingAs($user)
            ->post(route('research.logbook.store', $proposal), [
                'log_date' => now()->format('Y-m-d'),
                'note' => 'Pengukuran sampel A selesai.',
                'obstacle' => 'Alat kalibrasi kurang presisi.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('research_logbooks', [
            'research_proposal_id' => $proposal->id,
            'note' => 'Pengukuran sampel A selesai.',
            'obstacle' => 'Alat kalibrasi kurang presisi.',
        ]);
    }

    public function test_logbook_requires_date_and_note_and_rejects_future_date(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-LOG-INV',
            'title' => 'Riset Logbook Invalid',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_ONGOING,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
        ]);

        $this->actingAs($user)
            ->post(route('research.logbook.store', $proposal), [
                'log_date' => now()->addDay()->format('Y-m-d'),
                'note' => '',
            ])
            ->assertSessionHasErrors(['log_date', 'note']);
    }

    public function test_owner_can_delete_logbook_but_others_cannot(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $other = User::factory()->create(['role' => User::ROLE_USER]);
        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-LOG-DEL',
            'title' => 'Riset Logbook Hapus',
            'field' => 'Biologi',
            'status' => ResearchProposal::STATUS_ONGOING,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
        ]);
        $logbook = ResearchLogbook::create([
            'research_proposal_id' => $proposal->id,
            'log_date' => now()->format('Y-m-d'),
            'note' => 'Entri uji coba.',
        ]);

        $this->actingAs($other)
            ->delete(route('research.logbook.destroy', [$proposal, $logbook]))
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('research.logbook.destroy', [$proposal, $logbook]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('research_logbooks', ['id' => $logbook->id]);
    }

    public function test_owner_can_view_logbook_page_but_others_cannot(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $other = User::factory()->create(['role' => User::ROLE_USER]);
        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-LOG-PAGE',
            'title' => 'Riset Logbook Page',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_ONGOING,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
        ]);
        ResearchLogbook::create([
            'research_proposal_id' => $proposal->id,
            'log_date' => now()->format('Y-m-d'),
            'note' => 'Analisis data minggu pertama.',
            'obstacle' => 'Sampel terkontaminasi.',
        ]);

        $this->actingAs($user)
            ->get(route('research.logbook', $proposal))
            ->assertOk()
            ->assertSee('Logbook Penelitian')
            ->assertSee('Analisis data minggu pertama.')
            ->assertSee('Sampel terkontaminasi.');

        $this->actingAs($other)
            ->get(route('research.logbook', $proposal))
            ->assertForbidden();
    }

    public function test_logbook_print_page_accessible_for_owner_and_admin(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER, 'name' => 'Budi Pemohon']);
        $other = User::factory()->create(['role' => User::ROLE_USER]);
        $admin = User::factory()->create();
        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-LOG-PRINT',
            'title' => 'Riset Logbook Print',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_ONGOING,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
        ]);
        ResearchLogbook::create([
            'research_proposal_id' => $proposal->id,
            'log_date' => now()->format('Y-m-d'),
            'note' => 'Analisis sampel selesai.',
            'obstacle' => null,
        ]);

        // Owner dapat mencetak.
        $this->actingAs($user)
            ->get(route('research.logbook.print', $proposal))
            ->assertOk()
            ->assertSee('Logbook Penelitian')
            ->assertSee('Cetak / Simpan PDF')
            ->assertSee('Analisis sampel selesai.');

        // User lain tidak bisa mencetak punya orang lain.
        $this->actingAs($other)
            ->get(route('research.logbook.print', $proposal))
            ->assertForbidden();

        // Admin dapat mencetak.
        $this->actingAs($admin)
            ->get(route('admin.research.logbook.print', $proposal))
            ->assertOk()
            ->assertSee('Logbook Penelitian')
            ->assertSee('Cetak / Simpan PDF');
    }

    public function test_admin_can_view_logbook_page(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $proposal = ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-LOG-ADM',
            'title' => 'Riset Logbook Admin',
            'field' => 'Kimia',
            'status' => ResearchProposal::STATUS_ONGOING,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
        ]);
        ResearchLogbook::create([
            'research_proposal_id' => $proposal->id,
            'log_date' => now()->format('Y-m-d'),
            'note' => 'Analisis data minggu pertama.',
            'obstacle' => 'Sampel terkontaminasi.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.research.logbook', $proposal))
            ->assertOk()
            ->assertSee('Logbook Penelitian')
            ->assertSee('Analisis data minggu pertama.')
            ->assertSee('Sampel terkontaminasi.');

        // Detail admin tetap menampilkan link ke halaman logbook.
        $this->actingAs($admin)
            ->get(route('admin.research.show', $proposal))
            ->assertOk()
            ->assertSee('Logbook Penelitian');
    }
}
