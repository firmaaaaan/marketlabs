<?php

namespace Tests\Feature;

use App\Models\Borrowing;
use App\Models\Tool;
use App\Models\ToolCategory;
use App\Models\User;
use App\Notifications\BorrowingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class BorrowingFlowTest extends TestCase
{
    use RefreshDatabase;

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
     * Buat file .xlsx sungguhan dari array baris.
     *
     * @param  array<int, array<int, mixed>>  $rows
     */
    private function makeXlsx(array $rows): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'import');
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($rows);
        (new Xlsx($spreadsheet))->save($tmp);
        $spreadsheet->disconnectWorksheets();

        return $tmp;
    }

    protected Tool $tool;

    protected ToolCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = ToolCategory::create(['name' => 'Optik']);

        $this->tool = Tool::create([
            'code' => 'AL-999',
            'name' => 'Mikroskop Uji',
            'category_id' => $this->category->id,
            'brand' => 'Olympus',
            'series' => 'CX23',
            'description' => 'Alat uji untuk test.',
            'total_stock' => 5,
            'available_stock' => 5,
            'price_per_day' => 75000,
            'is_active' => true,
        ]);
    }

    public function test_catalog_page_shows_tools(): void
    {
        $response = $this->get(route('tools.index'));

        $response->assertStatus(200)
            ->assertSee($this->tool->name)
            ->assertSee('5 tersedia')
            ->assertSee('Rp 75.000');
    }

    public function test_catalog_search_filters_tools(): void
    {
        $steril = ToolCategory::create(['name' => 'Sterilisasi']);

        Tool::create([
            'code' => 'AL-998',
            'name' => 'Autoclave Uji',
            'category_id' => $steril->id,
            'brand' => 'Hirayama',
            'description' => 'Alat uji autoclave.',
            'total_stock' => 2,
            'available_stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->get(route('tools.index', ['search' => 'autoclave']));

        $response->assertStatus(200)
            ->assertSee('Autoclave Uji')
            ->assertDontSee('Mikroskop Uji');
    }

    public function test_tool_detail_page_is_accessible(): void
    {
        $this->get(route('tools.show', $this->tool))
            ->assertStatus(200)
            ->assertSee($this->tool->name)
            ->assertSee('Tambah ke Keranjang');
    }

    public function test_add_to_cart_and_view_cart(): void
    {
        $response = $this->post(route('cart.add', $this->tool), ['quantity' => 2]);

        $response->assertRedirect();

        $this->get(route('cart.index'))
            ->assertStatus(200)
            ->assertSee($this->tool->name);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $response = $this->post(route('cart.add', $this->tool), ['quantity' => 99]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_guest_checkout_requires_login(): void
    {
        $this->post(route('cart.add', $this->tool), ['quantity' => 1]);

        $this->get(route('borrowings.create'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_complete_borrowing_checkout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('cart.add', $this->tool), ['quantity' => 2]);

        $response = $this->post(route('borrowings.store'), [
            'borrower_type' => 'internal',
            'nim_nip' => '2101234567',
            'institution' => 'Universitas Contoh',
            'purpose' => 'Praktikum mikroskopi.',
            'borrow_date' => now()->addDay()->format('Y-m-d'),
            'return_date' => now()->addDays(3)->format('Y-m-d'),
            'notes' => 'Untuk praktikum.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('borrowings', [
            'user_id' => $user->id,
            'status' => Borrowing::STATUS_PENDING,
            'borrower_type' => 'internal',
            'payment_status' => Borrowing::PAYMENT_UNPAID,
            'nim_nip' => '2101234567',
            'institution' => 'Universitas Contoh',
            'purpose' => 'Praktikum mikroskopi.',
        ]);

        $borrowing = Borrowing::first();
        $this->assertNotNull($borrowing);
        $this->assertCount(1, $borrowing->items);
        $this->assertEquals(2, $borrowing->items->first()->quantity);
        $this->assertEquals(75000, $borrowing->items->first()->price_per_day);
        $this->assertNotNull($borrowing->invoice_number);
        $this->assertStringStartsWith('INV-', $borrowing->invoice_number);
        $this->assertNull(session('cart'));
    }

    public function test_borrowing_requires_borrower_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('cart.add', $this->tool), ['quantity' => 1]);

        $this->post(route('borrowings.store'), [
            'borrow_date' => now()->addDay()->format('Y-m-d'),
            'return_date' => now()->addDays(2)->format('Y-m-d'),
        ])->assertSessionHasErrors('borrower_type');
    }

    public function test_borrowing_can_upload_supporting_document(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('cart.add', $this->tool), ['quantity' => 1]);

        $file = UploadedFile::fake()->create('permohonan.pdf', 100, 'application/pdf');

        $this->post(route('borrowings.store'), [
            'borrower_type' => 'eksternal',
            'nim_nip' => '198501012010011001',
            'institution' => 'PT Riset Nusantara',
            'purpose' => 'Uji sampel penelitian.',
            'borrow_date' => now()->addDay()->format('Y-m-d'),
            'return_date' => now()->addDays(2)->format('Y-m-d'),
            'document' => $file,
        ])->assertRedirect();

        $borrowing = Borrowing::first();
        $this->assertNotNull($borrowing);
        $this->assertEquals('eksternal', $borrowing->borrower_type);
        $this->assertNotNull($borrowing->document_path);
        $this->assertStringEndsWith('.pdf', $borrowing->document_path);
    }

    public function test_borrowing_requires_purpose(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('cart.add', $this->tool), ['quantity' => 1]);

        $this->post(route('borrowings.store'), [
            'borrower_type' => 'internal',
            'nim_nip' => '2101234567',
            'institution' => 'Universitas Contoh',
            'borrow_date' => now()->addDay()->format('Y-m-d'),
            'return_date' => now()->addDays(2)->format('Y-m-d'),
        ])->assertSessionHasErrors('purpose');
    }

    public function test_borrowing_requires_personal_info(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('cart.add', $this->tool), ['quantity' => 1]);

        $this->post(route('borrowings.store'), [
            'borrower_type' => 'internal',
            'borrow_date' => now()->addDay()->format('Y-m-d'),
            'return_date' => now()->addDays(2)->format('Y-m-d'),
        ])->assertSessionHasErrors(['nim_nip', 'institution']);
    }

    public function test_borrowing_total_cost_is_calculated_by_days(): void
    {
        $user = User::factory()->create();
        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-COST-01',
            'status' => Borrowing::STATUS_PENDING,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(4), // 3 hari
        ]);

        $borrowing->items()->create([
            'tool_id' => $this->tool->id,
            'quantity' => 2,
            'price_per_day' => 75000,
        ]);

        // 75000 × 2 unit × 3 hari = 450.000
        $this->assertEquals(3, $borrowing->duration_days);
        $this->assertEquals(450000, $borrowing->total_cost);
    }

    public function test_user_borrowing_history_is_visible(): void
    {
        $user = User::factory()->create();
        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-00001',
            'status' => Borrowing::STATUS_PENDING,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(3),
        ]);

        $this->actingAs($user)
            ->get(route('borrowings.index'))
            ->assertStatus(200)
            ->assertSee('PNJ-TEST-00001');
    }

    public function test_user_cannot_view_others_borrowing(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $owner->id,
            'code' => 'PNJ-TEST-00002',
            'status' => Borrowing::STATUS_PENDING,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(3),
        ]);

        $this->actingAs($other)
            ->get(route('borrowings.show', $borrowing))
            ->assertStatus(403);
    }

    public function test_admin_can_approve_borrowing_and_reduce_stock(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-00003',
            'status' => Borrowing::STATUS_PENDING,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(3),
        ]);

        $borrowing->items()->create([
            'tool_id' => $this->tool->id,
            'quantity' => 2,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.borrowings.status', $borrowing), ['status' => 'approved'])
            ->assertRedirect();

        $this->assertDatabaseHas('borrowings', ['id' => $borrowing->id, 'status' => 'approved']);
        $this->assertDatabaseHas('tools', ['id' => $this->tool->id, 'available_stock' => 3]);
    }

    public function test_admin_can_mark_returned_and_restore_stock(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-00004',
            'status' => Borrowing::STATUS_BORROWED,
            'borrow_date' => now()->subDay(),
            'return_date' => now()->addDays(2),
        ]);

        $borrowing->items()->create([
            'tool_id' => $this->tool->id,
            'quantity' => 2,
        ]);

        $this->tool->decrement('available_stock', 2);

        $this->actingAs($admin)
            ->patch(route('admin.borrowings.status', $borrowing), ['status' => 'returned'])
            ->assertRedirect();

        $this->assertDatabaseHas('borrowings', ['id' => $borrowing->id, 'status' => 'returned']);
        $this->assertDatabaseHas('tools', ['id' => $this->tool->id, 'available_stock' => 5]);
    }

    public function test_admin_can_update_billing_discount_penalty_and_pickup_notes(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-BILL-01',
            'status' => Borrowing::STATUS_APPROVED,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(4), // 3 hari
        ]);

        $borrowing->items()->create([
            'tool_id' => $this->tool->id,
            'quantity' => 2,
            'price_per_day' => 75000,
        ]);

        // Biaya dasar: 75000 × 2 × 3 = 450.000
        $this->assertEquals(450000, $borrowing->base_cost);

        $this->actingAs($admin)
            ->patch(route('admin.borrowings.billing', $borrowing), [
                'discount' => 10,
                'penalty' => 50000,
                'pickup_notes' => 'Ambil di Lab Utama lantai 2, bawa KTP.',
            ])
            ->assertRedirect();

        $borrowing->refresh();

        $this->assertEquals(10, $borrowing->discount);
        $this->assertEquals(50000, $borrowing->penalty);
        $this->assertEquals('Ambil di Lab Utama lantai 2, bawa KTP.', $borrowing->pickup_notes);

        // Diskon 10% dari 450.000 = 45.000 → total = 450.000 − 45.000 + 50.000 = 455.000
        $this->assertEquals(45000, $borrowing->discount_amount);
        $this->assertEquals(455000, $borrowing->total_cost);
    }

    public function test_admin_topbar_shows_notification_bell(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $admin->notify(new BorrowingNotification(
            'Peminjaman Baru',
            'Peminjaman PNJ-TEST-NOTIF-01 diajukan dan menunggu persetujuan.',
            route('admin.borrowings.index', ['status' => 'pending']),
        ));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Peminjaman Baru')
            ->assertSee('PNJ-TEST-NOTIF-01')
            ->assertSee('1 belum dibaca');

        // Setelah ditandai dibaca, badge & chip tidak muncul lagi.
        $admin->unreadNotifications->markAsRead();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertDontSee('1 belum dibaca')
            ->assertSee('Tidak ada notifikasi');
    }

    public function test_admin_notifications_page_lists_all_notifications(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $admin->notify(new BorrowingNotification(
            'Peminjaman Baru',
            'Peminjaman PNJ-TEST-NOTIF-PG-01 diajukan dan menunggu persetujuan.',
            route('admin.borrowings.index', ['status' => 'pending']),
        ));

        $this->actingAs($admin)
            ->get(route('admin.notifications.all'))
            ->assertStatus(200)
            ->assertSee('Peminjaman Baru')
            ->assertSee('PNJ-TEST-NOTIF-PG-01')
            ->assertSee('Tandai Semua Dibaca');
    }

    public function test_admin_notifications_endpoint_returns_all_notifications(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $admin->notify(new BorrowingNotification(
            'Peminjaman Baru',
            'Peminjaman PNJ-TEST-JSON-01 diajukan dan menunggu persetujuan.',
            route('admin.borrowings.index', ['status' => 'pending']),
        ));

        $this->actingAs($admin)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('items.0.title', 'Peminjaman Baru')
            ->assertJsonPath('items.0.message', 'Peminjaman PNJ-TEST-JSON-01 diajukan dan menunggu persetujuan.');
    }

    public function test_admin_action_creates_client_notification(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-NOTIF-CL-01',
            'status' => Borrowing::STATUS_PENDING,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(3),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.borrowings.status', $borrowing), ['status' => 'approved'])
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $user->id]);

        $notification = $user->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertEquals('Peminjaman Disetujui', $notification->data['title']);
        $this->assertStringContainsString('PNJ-TEST-NOTIF-CL-01', $notification->data['message']);

        // Menandai pembayaran lunas juga membuat notifikasi.
        $borrowing->refresh()->update(['status' => Borrowing::STATUS_RETURNED]);

        $this->actingAs($admin)
            ->patch(route('admin.borrowings.payment', $borrowing), ['payment_status' => 'paid'])
            ->assertRedirect();

        $titles = $borrowing->user->notifications()->get()->map(fn ($n) => $n->data['title'] ?? '');
        $this->assertContains('Pembayaran Lunas', $titles);
    }

    public function test_client_notification_feed_and_mark_read(): void
    {
        $user = User::factory()->create();

        $user->notify(new BorrowingNotification(
            'Peminjaman Disetujui',
            'Peminjaman Anda disetujui.',
            route('borrowings.index'),
        ));

        $this->actingAs($user)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('items.0.title', 'Peminjaman Disetujui')
            ->assertJsonPath('items.0.is_read', false);

        $this->actingAs($user)
            ->postJson(route('notifications.read'))
            ->assertOk();

        $this->actingAs($user)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonPath('unread_count', 0)
            ->assertJsonPath('items.0.is_read', true);
    }

    public function test_client_can_view_all_notifications_page(): void
    {
        $user = User::factory()->create();

        $user->notify(new BorrowingNotification(
            'Peminjaman Disetujui',
            'Peminjaman Anda disetujui.',
            route('borrowings.index'),
        ));

        $this->actingAs($user)
            ->get(route('notifications.all'))
            ->assertStatus(200)
            ->assertSee('Peminjaman Disetujui')
            ->assertSee('Belum dibaca')
            ->assertSee('Tandai Semua Dibaca');
    }

    public function test_client_navbar_shows_notification_bell_for_logged_in_user(): void
    {
        $user = User::factory()->create();

        // Tamu tidak melihat lonceng notifikasi.
        $this->get(route('home'))
            ->assertStatus(200)
            ->assertDontSee('client-notif-btn');

        // User yang login melihat lonceng notifikasi.
        $this->actingAs($user)
            ->get(route('home'))
            ->assertStatus(200)
            ->assertSee('client-notif-btn');
    }

    public function test_admin_can_mark_borrowing_as_paid(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-PAY-01',
            'invoice_number' => 'INV-TEST-00001',
            'status' => Borrowing::STATUS_RETURNED,
            'payment_status' => Borrowing::PAYMENT_UNPAID,
            'borrow_date' => now()->subDays(5),
            'return_date' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.borrowings.payment', $borrowing), ['payment_status' => 'paid'])
            ->assertRedirect();

        $this->assertDatabaseHas('borrowings', [
            'id' => $borrowing->id,
            'payment_status' => 'paid',
        ]);
    }

    public function test_owner_can_view_invoice_but_others_cannot(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $owner->id,
            'code' => 'PNJ-TEST-INV-01',
            'invoice_number' => 'INV-TEST-00002',
            'status' => Borrowing::STATUS_APPROVED,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(4), // 3 hari
        ]);

        $borrowing->items()->create([
            'tool_id' => $this->tool->id,
            'quantity' => 1,
            'price_per_day' => 75000,
        ]);

        $this->actingAs($owner)
            ->get(route('borrowings.invoice', $borrowing))
            ->assertStatus(200)
            ->assertSee('INV-TEST-00002')
            ->assertSee('Rp 225.000');

        $this->actingAs($other)
            ->get(route('borrowings.invoice', $borrowing))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->get(route('admin.borrowings.invoice', $borrowing))
            ->assertStatus(200)
            ->assertSee('INV-TEST-00002');
    }

    public function test_admin_can_export_borrowings_excel(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-XLSX-01',
            'invoice_number' => 'INV-TEST-XLSX-01',
            'status' => Borrowing::STATUS_APPROVED,
            'borrower_type' => Borrowing::TYPE_INTERNAL,
            'nim_nip' => '2101234567',
            'institution' => 'Universitas Contoh',
            'purpose' => 'Praktikum.',
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(3),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.borrowings.export-excel'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $values = $this->xlsxValues($response->streamedContent());
        $this->assertStringContainsString('PNJ-TEST-XLSX-01', implode('|', $values));
        $this->assertStringContainsString('Kode', implode('|', $values));
        $this->assertStringContainsString('Disetujui', implode('|', $values));
    }

    public function test_user_can_export_own_borrowings_excel(): void
    {
        $user = User::factory()->create();

        Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-XLSX-02',
            'status' => Borrowing::STATUS_PENDING,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(3),
        ]);

        $response = $this->actingAs($user)->get(route('borrowings.export'));

        $response->assertOk();
        $values = $this->xlsxValues($response->streamedContent());
        $this->assertStringContainsString('PNJ-TEST-XLSX-02', implode('|', $values));
    }

    public function test_admin_can_reject_pending_borrowing(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-00005',
            'status' => Borrowing::STATUS_PENDING,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(3),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.borrowings.status', $borrowing), ['status' => 'rejected'])
            ->assertRedirect();

        $this->assertDatabaseHas('borrowings', ['id' => $borrowing->id, 'status' => 'rejected']);
    }

    public function test_user_cancel_approved_borrowing_restores_stock(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-CANCEL-01',
            'status' => Borrowing::STATUS_APPROVED,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(3),
        ]);

        $borrowing->items()->create([
            'tool_id' => $this->tool->id,
            'quantity' => 2,
        ]);

        // Stok sudah berkurang saat disetujui: 5 - 2 = 3.
        $this->tool->decrement('available_stock', 2);

        $this->actingAs($user)
            ->delete(route('borrowings.cancel', $borrowing))
            ->assertRedirect();

        $this->assertDatabaseHas('borrowings', ['id' => $borrowing->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('tools', ['id' => $this->tool->id, 'available_stock' => 5]);
    }

    public function test_user_cancel_pending_borrowing_does_not_change_stock(): void
    {
        $user = User::factory()->create();

        $borrowing = Borrowing::create([
            'user_id' => $user->id,
            'code' => 'PNJ-TEST-CANCEL-02',
            'status' => Borrowing::STATUS_PENDING,
            'borrow_date' => now()->addDay(),
            'return_date' => now()->addDays(3),
        ]);

        $borrowing->items()->create([
            'tool_id' => $this->tool->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->delete(route('borrowings.cancel', $borrowing))
            ->assertRedirect();

        $this->assertDatabaseHas('borrowings', ['id' => $borrowing->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('tools', ['id' => $this->tool->id, 'available_stock' => 5]);
    }

    public function test_admin_can_create_and_update_tool(): void
    {
        $admin = User::factory()->create();
        $analitik = ToolCategory::create(['name' => 'Analitik']);

        $this->actingAs($admin)
            ->post(route('admin.tools.store'), [
                'name' => 'Spektrometer Baru',
                'category_id' => $analitik->id,
                'brand' => 'Shimadzu',
                'series' => 'UV-1900',
                'description' => 'Alat baru.',
                'total_stock' => 3,
                'price_per_day' => 90000,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.tools.index'));

        $this->assertDatabaseHas('tools', [
            'available_stock' => 3,
            'price_per_day' => 90000,
            'brand' => 'Shimadzu',
        ]);

        // Kode alat dibuat otomatis dengan format AL-XXX (minimal 3 digit).
        $created = Tool::where('brand', 'Shimadzu')->first();
        $this->assertMatchesRegularExpression('/^AL-\d{3,}$/', $created->code);
    }

    public function test_admin_can_export_tools_excel(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.tools.export'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $values = $this->xlsxValues($response->streamedContent());
        $this->assertStringContainsString('Mikroskop Uji', implode('|', $values));
        $this->assertStringContainsString('Optik', implode('|', $values));
    }

    public function test_admin_can_download_tool_import_template(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.tools.template'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $values = $this->xlsxValues($response->streamedContent());
        $this->assertStringContainsString('Nama', implode('|', $values));
        $this->assertStringContainsString('Contoh Alat 1', implode('|', $values));
        $this->assertStringContainsString('Kategori', implode('|', $values));
    }

    public function test_admin_can_import_tools_from_excel(): void
    {
        $admin = User::factory()->create();
        $category = ToolCategory::create(['name' => 'Pengukuran']);

        // Alat yang sudah ada untuk diuji pembaruan via kode.
        $existing = Tool::create([
            'code' => 'AL-777',
            'name' => 'Alat Lama',
            'category_id' => $category->id,
            'total_stock' => 2,
            'available_stock' => 2,
            'price_per_day' => 10000,
            'is_active' => true,
        ]);

        $xlsxPath = $this->makeXlsx([
            ['Kode', 'Nama', 'Kategori', 'Merk', 'Seri', 'Deskripsi', 'Total Stok', 'Harga Sewa/Hari', 'Status Aktif'],
            ['', 'Spektrometer Import', 'Pengukuran', 'Shimadzu', 'UV-1900', 'Alat dari import', 3, 90000, 'Aktif'],
            ['AL-777', 'Alat Lama Diperbarui', 'Pengukuran', '', '', '', 5, 12000, 'Aktif'],
        ]);

        $file = new UploadedFile($xlsxPath, 'alat.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($admin)
            ->post(route('admin.tools.import'), ['file' => $file])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tools', ['name' => 'Spektrometer Import', 'price_per_day' => 90000]);

        // Alat dengan kode AL-777 diperbarui, bukan dibuat baru.
        // Total alat: 1 (setup) + 1 (AL-777) + 1 (import baru) = 3.
        $existing->refresh();
        $this->assertEquals('Alat Lama Diperbarui', $existing->name);
        $this->assertEquals(5, $existing->total_stock);
        $this->assertEquals(12000, $existing->price_per_day);
        $this->assertDatabaseCount('tools', 3);

        @unlink($xlsxPath);
    }

    public function test_tools_page_shows_import_card(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.tools.index'))
            ->assertOk()
            ->assertSee('Import Excel', false)
            ->assertSee('importCard', false)
            ->assertSee('Unggah File Excel', false)
            ->assertSee(route('admin.tools.template'), false);
    }

    public function test_admin_can_manage_categories(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Kategori Baru'])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('tool_categories', ['name' => 'Kategori Baru']);
    }

    public function test_tool_form_requires_category(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.tools.store'), [
                'name' => 'Alat Tanpa Kategori',
                'total_stock' => 1,
                'price_per_day' => 10000,
            ])
            ->assertSessionHasErrors(['category_id']);
    }

    public function test_tool_code_is_sequential_and_auto_generated(): void
    {
        $admin = User::factory()->create();
        $analitik = ToolCategory::create(['name' => 'Pengukuran']);

        $this->actingAs($admin)->post(route('admin.tools.store'), [
            'name' => 'Alat Auto 1',
            'category_id' => $analitik->id,
            'total_stock' => 1,
            'price_per_day' => 10000,
        ]);

        $this->actingAs($admin)->post(route('admin.tools.store'), [
            'name' => 'Alat Auto 2',
            'category_id' => $analitik->id,
            'total_stock' => 1,
            'price_per_day' => 10000,
        ]);

        $codes = Tool::whereIn('name', ['Alat Auto 1', 'Alat Auto 2'])->orderBy('id')->pluck('code');

        $this->assertCount(2, $codes);
        $this->assertNotEquals($codes[0], $codes[1]);
        $this->assertMatchesRegularExpression('/^AL-\d{3,}$/', $codes[0]);
        $this->assertMatchesRegularExpression('/^AL-\d{3,}$/', $codes[1]);
    }
}
