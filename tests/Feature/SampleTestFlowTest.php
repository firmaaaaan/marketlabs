<?php

namespace Tests\Feature;

use App\Models\SampleForm;
use App\Models\SampleTest;
use App\Models\SampleTestItem;
use App\Models\SampleType;
use App\Models\SampleUnit;
use App\Models\TestParameter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleTestFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function createUnit(string $name = 'Sampel', string $symbol = 'smpl'): SampleUnit
    {
        return SampleUnit::create(['name' => $name, 'symbol' => $symbol, 'is_active' => true]);
    }

    protected function createParameter(SampleUnit $unit, string $name = 'Uji pH', int $rate = 50000): TestParameter
    {
        return TestParameter::create(['name' => $name, 'unit_id' => $unit->id, 'rate' => $rate, 'is_active' => true]);
    }

    protected function createSampleTest(User $user, array $overrides = []): SampleTest
    {
        return SampleTest::create(array_merge([
            'user_id' => $user->id,
            'code' => 'UJI-TEST-'.strtoupper(substr(uniqid(), -5)),
            'status' => SampleTest::STATUS_PENDING,
            'total_cost' => 0,
            'payment_status' => SampleTest::PAYMENT_UNPAID,
        ], $overrides));
    }

    protected function attachItem(SampleTest $test, TestParameter $parameter, array $item = []): SampleTestItem
    {
        return SampleTestItem::create(array_merge([
            'sample_test_id' => $test->id,
            'parameter_id' => $parameter->id,
            'sample_name' => 'Air Sungai',
            'quantity' => 1,
            'rate' => $parameter->rate,
        ], $item));
    }

    public function test_guest_cannot_access_sample_test_pages(): void
    {
        $this->get(route('sample-tests.index'))->assertRedirect(route('login'));
        $this->get(route('sample-tests.checkout'))->assertRedirect(route('login'));
    }

    public function test_guest_can_view_public_test_catalog(): void
    {
        $unit = $this->createUnit();
        $this->createParameter($unit, 'Uji pH', 50000);

        $this->get(route('sample-tests.catalog'))
            ->assertOk()
            ->assertSee('Uji pH');
    }

    public function test_catalog_lists_services_and_can_filter_by_unit(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $unitA = $this->createUnit('Sampel');
        $unitB = $this->createUnit('Running', 'running');
        $this->createParameter($unitA, 'Uji Kadar Air', 10000);
        $this->createParameter($unitB, 'Pengujian PCR', 194000);

        $this->actingAs($user)
            ->get(route('sample-tests.catalog'))
            ->assertOk()
            ->assertSee('Uji Kadar Air')
            ->assertSee('Pengujian PCR');

        $this->actingAs($user)
            ->get(route('sample-tests.catalog', ['unit_id' => $unitB->id]))
            ->assertOk()
            ->assertSee('Pengujian PCR')
            ->assertDontSee('Uji Kadar Air');
    }

    public function test_user_can_add_to_cart_and_checkout(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $unitA = $this->createUnit('Sampel');
        $unitB = $this->createUnit('Running', 'running');
        $p1 = $this->createParameter($unitA, 'Uji Kadar Air', 10000);
        $p2 = $this->createParameter($unitB, 'Pengujian PCR', 194000);
        $form = SampleForm::create(['name' => 'Cair']);
        $type = SampleType::create(['name' => 'Air Sungai']);

        // Tambah dua layanan dari satuan berbeda (boleh campur).
        $this->actingAs($user)->post(route('test-cart.add', $p1), ['quantity' => 1])->assertRedirect();
        $this->actingAs($user)->post(route('test-cart.add', $p2), ['quantity' => 1])->assertRedirect();

        // Keranjang pengujian diarahkan ke halaman keranjang gabungan.
        $this->actingAs($user)
            ->get(route('test-cart.index'))
            ->assertRedirect(route('cart.index'));

        // Halaman keranjang gabungan menampilkan alat & layanan pengujian.
        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Uji Kadar Air')
            ->assertSee('Pengujian PCR')
            ->assertSee('Layanan Pengujian Sampel');

        // Checkout & submit: tiap layanan punya sampelnya sendiri (bentuk & jenis diisi).
        $this->actingAs($user)
            ->post(route('sample-tests.store'), [
                'notes' => 'Segera diproses.',
                'delivery_method' => SampleTest::DELIVERY_PACKAGED,
                'services' => [
                    $p1->id => [[
                        'sample_name' => 'Serum Darah',
                        'sample_description' => 'Diambil dari pasien A.',
                        'quantity' => 2,
                        'sample_form_id' => $form->id,
                        'sample_type_id' => $type->id,
                    ]],
                    $p2->id => [[
                        'sample_name' => 'DNA Isolat',
                        'quantity' => 1,
                    ]],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $test = SampleTest::where('user_id', $user->id)->first();
        $this->assertNotNull($test);
        $this->assertMatchesRegularExpression('/^UJI-\d{8}-[A-Z0-9]{5}$/', $test->code);
        $this->assertEquals(SampleTest::STATUS_PENDING, $test->status);
        $this->assertEquals(SampleTest::PAYMENT_UNPAID, $test->payment_status);
        $this->assertEquals(SampleTest::DELIVERY_PACKAGED, $test->delivery_method);
        $this->assertEquals(2, $test->items->count());
        $this->assertEquals(214000, $test->total_cost); // (10.000 × 2) + 194.000

        $item1 = $test->items->firstWhere('parameter_id', $p1->id);
        $this->assertNotNull($item1);
        $this->assertEquals('Serum Darah', $item1->sample_name);
        $this->assertEquals(2, $item1->quantity);
        $this->assertEquals($form->id, $item1->sample_form_id);
        $this->assertEquals($type->id, $item1->sample_type_id);
        $this->assertEquals(20000, $item1->subtotal);

        $item2 = $test->items->firstWhere('parameter_id', $p2->id);
        $this->assertNotNull($item2);
        $this->assertEquals(194000, $item2->subtotal);

        $this->assertDatabaseHas('sample_test_items', [
            'sample_test_id' => $test->id,
            'parameter_id' => $p1->id,
            'rate' => 10000,
            'quantity' => 2,
        ]);

        // Keranjang kosong setelah submit.
        $this->assertEmpty(session('test_cart', []));
    }

    public function test_one_parameter_can_have_multiple_samples(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $unit = $this->createUnit();
        $p1 = $this->createParameter($unit, 'Uji pH', 50000);

        $this->actingAs($user)->post(route('test-cart.add', $p1))->assertRedirect();

        // Satu layanan dipakai untuk 3 sampel.
        $this->actingAs($user)
            ->post(route('sample-tests.store'), [
                'delivery_method' => SampleTest::DELIVERY_DIRECT,
                'services' => [
                    $p1->id => [
                        ['sample_name' => 'Air Sungai', 'quantity' => 1],
                        ['sample_name' => 'Air Sumur', 'quantity' => 2],
                        ['sample_name' => 'Air PAM', 'quantity' => 1],
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $test = SampleTest::where('user_id', $user->id)->first();
        $this->assertNotNull($test);
        $this->assertCount(3, $test->items);
        $this->assertEquals(1, $test->services_count);
        $this->assertEquals(4, $test->total_samples);
        $this->assertEquals(200000, $test->total_cost); // 50k × (1 + 2 + 1)

        foreach ($test->items as $item) {
            $this->assertEquals($p1->id, $item->parameter_id);
        }
    }

    public function test_checkout_requires_cart_and_sample_name(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        // Tanpa isi keranjang → dialihkan ke katalog.
        $this->actingAs($user)
            ->get(route('sample-tests.checkout'))
            ->assertRedirect(route('sample-tests.catalog'));

        // Submit tanpa data → error validasi.
        $this->actingAs($user)
            ->post(route('sample-tests.store'), [])
            ->assertSessionHasErrors(['services', 'delivery_method']);
    }

    public function test_user_can_view_own_sample_test_history_and_detail(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $other = User::factory()->create(['role' => User::ROLE_USER]);
        $unit = $this->createUnit();
        $p1 = $this->createParameter($unit);
        $form = SampleForm::create(['name' => 'Cair']);
        $type = SampleType::create(['name' => 'Air Sumur']);

        $test = $this->createSampleTest($user, ['code' => 'UJI-TEST-001']);
        $this->attachItem($test, $p1, [
            'sample_name' => 'Air Sumur',
            'quantity' => 2,
            'sample_form_id' => $form->id,
            'sample_type_id' => $type->id,
        ]);

        $this->actingAs($user)
            ->get(route('sample-tests.index'))
            ->assertOk()
            ->assertSee('UJI-TEST-001')
            ->assertSee('Air Sumur');

        $this->actingAs($user)
            ->get(route('sample-tests.show', $test))
            ->assertOk()
            ->assertSee('UJI-TEST-001')
            ->assertSee('Air Sumur')
            ->assertSee('Uji pH')
            ->assertSee('Bentuk: Cair')
            ->assertSee('Jenis: Air Sumur')
            ->assertSee('Pengiriman Sampel')
            ->assertSee('Rp 100.000');

        $this->actingAs($other)
            ->get(route('sample-tests.show', $test))
            ->assertForbidden();
    }

    public function test_owner_can_cancel_pending_sample_test(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $test = $this->createSampleTest($user, ['code' => 'UJI-TEST-CANCEL']);

        $this->actingAs($user)
            ->delete(route('sample-tests.cancel', $test))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(SampleTest::STATUS_CANCELLED, $test->refresh()->status);
    }

    public function test_admin_can_update_status_and_result_and_payment(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $unit = $this->createUnit();
        $p1 = $this->createParameter($unit);

        $test = $this->createSampleTest($user, ['code' => 'UJI-TEST-ADMIN']);
        $this->attachItem($test, $p1, ['sample_name' => 'Logam Campuran']);

        // Setujui.
        $this->actingAs($admin)
            ->patch(route('admin.sample-tests.status', $test), ['status' => SampleTest::STATUS_APPROVED])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertEquals(SampleTest::STATUS_APPROVED, $test->refresh()->status);
        $this->assertNotNull($test->approved_at);

        // Tandai sedang diuji.
        $this->actingAs($admin)
            ->patch(route('admin.sample-tests.status', $test), ['status' => SampleTest::STATUS_TESTING])
            ->assertRedirect();
        $this->assertEquals(SampleTest::STATUS_TESTING, $test->refresh()->status);

        // Input hasil.
        $this->actingAs($admin)
            ->patch(route('admin.sample-tests.result', $test), [
                'result' => 'Kadar air 8,2%',
                'result_notes' => 'Sesuai standar SNI.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertEquals('Kadar air 8,2%', $test->refresh()->result);
        $this->assertEquals('Sesuai standar SNI.', $test->result_notes);

        // Selesai.
        $this->actingAs($admin)
            ->patch(route('admin.sample-tests.status', $test), ['status' => SampleTest::STATUS_DONE])
            ->assertRedirect();
        $this->assertEquals(SampleTest::STATUS_DONE, $test->refresh()->status);
        $this->assertNotNull($test->done_at);

        // Tandai lunas → invoice terbit.
        $this->actingAs($admin)
            ->patch(route('admin.sample-tests.payment', $test), ['payment_status' => SampleTest::PAYMENT_PAID])
            ->assertRedirect()
            ->assertSessionHas('success');

        $test->refresh();
        $this->assertTrue($test->is_paid);
        $this->assertNotNull($test->invoice_number);
        $this->assertStringStartsWith('INV-UJI-', $test->invoice_number);
    }

    public function test_owner_can_view_invoice_but_others_cannot(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $other = User::factory()->create(['role' => User::ROLE_USER]);
        $admin = User::factory()->create();
        $unit = $this->createUnit();
        $p1 = $this->createParameter($unit);

        $test = $this->createSampleTest($user, [
            'code' => 'UJI-TEST-INV',
            'status' => SampleTest::STATUS_DONE,
            'payment_status' => SampleTest::PAYMENT_PAID,
            'invoice_number' => 'INV-UJI-TEST-001',
        ]);
        $this->attachItem($test, $p1, ['sample_name' => 'Air Tanah']);

        $this->actingAs($user)
            ->get(route('sample-tests.invoice', $test))
            ->assertOk()
            ->assertSee('INV-UJI-TEST-001')
            ->assertSee('Invoice')
            ->assertSee('Air Tanah');

        $this->actingAs($other)
            ->get(route('sample-tests.invoice', $test))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.sample-tests.invoice', $test))
            ->assertOk()
            ->assertSee('INV-UJI-TEST-001');
    }

    public function test_admin_can_create_edit_and_delete_sample_test(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $unit = $this->createUnit();
        $p1 = $this->createParameter($unit, 'Uji pH', 50000);
        $p2 = $this->createParameter($unit, 'Uji Kadar Air', 75000);
        $form = SampleForm::create(['name' => 'Padat']);
        $type = SampleType::create(['name' => 'Kain']);

        // Halaman buat tersedia.
        $this->actingAs($admin)
            ->get(route('admin.sample-tests.create'))
            ->assertOk()
            ->assertSee('Buat Pengujian Sampel');

        // Buat manual: satu layanan dengan dua sampel.
        $this->actingAs($admin)
            ->post(route('admin.sample-tests.store'), [
                'user_id' => $user->id,
                'delivery_method' => SampleTest::DELIVERY_DIRECT,
                'services' => [
                    $p1->id => [
                        ['sample_name' => 'Kain Tenun', 'quantity' => 3, 'sample_form_id' => $form->id, 'sample_type_id' => $type->id],
                        ['sample_name' => 'Kain Songket', 'quantity' => 1],
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $test = SampleTest::where('user_id', $user->id)->first();
        $this->assertNotNull($test);
        $this->assertEquals($user->id, $test->user_id);
        $this->assertCount(2, $test->items);
        $this->assertEquals(200000, $test->total_cost); // 50k × (3 + 1)
        $this->assertEquals(4, $test->total_samples);
        $this->assertEquals($form->id, $test->items[0]->sample_form_id);

        // Edit.
        $this->actingAs($admin)
            ->get(route('admin.sample-tests.edit', $test))
            ->assertOk()
            ->assertSee('Edit Pengujian')
            ->assertSee('Kain Tenun');

        $this->actingAs($admin)
            ->patch(route('admin.sample-tests.update', $test), [
                'user_id' => $user->id,
                'delivery_method' => SampleTest::DELIVERY_PACKAGED,
                'services' => [
                    $p1->id => [[
                        'sample_name' => 'Kain Sutra',
                        'quantity' => 5,
                        'sample_form_id' => $form->id,
                        'sample_type_id' => $type->id,
                    ]],
                    $p2->id => [[
                        'sample_name' => 'Kain Katun',
                        'quantity' => 2,
                    ]],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $test->refresh();
        $this->assertCount(2, $test->items);
        $this->assertEquals(2, $test->services_count);
        $this->assertEquals('Kain Sutra', $test->items->firstWhere('parameter_id', $p1->id)->sample_name);
        $this->assertEquals('Kain Katun', $test->items->firstWhere('parameter_id', $p2->id)->sample_name);
        $this->assertEquals(SampleTest::DELIVERY_PACKAGED, $test->delivery_method);
        $this->assertEquals(400000, $test->total_cost); // (50k × 5) + (75k × 2)

        // Hapus.
        $this->actingAs($admin)
            ->delete(route('admin.sample-tests.destroy', $test))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('sample_tests', ['id' => $test->id]);
    }

    public function test_admin_can_manage_sample_units(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.sample-units.index'))
            ->assertOk()
            ->assertSee('Kelola Satuan Sampel');

        $this->actingAs($admin)
            ->post(route('admin.sample-units.store'), ['name' => 'Roll', 'symbol' => 'roll'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('sample_units', ['name' => 'Roll', 'symbol' => 'roll']);

        $unit = SampleUnit::where('name', 'Roll')->first();
        $this->actingAs($admin)
            ->put(route('admin.sample-units.update', $unit), ['name' => 'Roll Kain', 'symbol' => 'rk'])
            ->assertRedirect();

        $this->assertDatabaseHas('sample_units', ['name' => 'Roll Kain', 'symbol' => 'rk']);

        $this->actingAs($admin)
            ->delete(route('admin.sample-units.destroy', $unit->refresh()))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('sample_units', ['id' => $unit->id]);
    }

    public function test_unit_with_parameters_cannot_be_deleted(): void
    {
        $admin = User::factory()->create();
        $unit = $this->createUnit();
        $this->createParameter($unit);

        $this->actingAs($admin)
            ->delete(route('admin.sample-units.destroy', $unit))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('sample_units', ['id' => $unit->id]);
    }

    public function test_admin_can_manage_test_parameters(): void
    {
        $admin = User::factory()->create();
        $unit = $this->createUnit();

        $this->actingAs($admin)
            ->get(route('admin.test-parameters.index'))
            ->assertOk()
            ->assertSee('Kelola Parameter Pengujian');

        $this->actingAs($admin)
            ->post(route('admin.test-parameters.store'), [
                'name' => 'Uji Warna',
                'unit_id' => $unit->id,
                'rate' => 60000,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('test_parameters', ['name' => 'Uji Warna', 'rate' => 60000]);

        $parameter = TestParameter::where('name', 'Uji Warna')->first();
        $this->actingAs($admin)
            ->put(route('admin.test-parameters.update', $parameter), [
                'name' => 'Uji Warna Presisi',
                'unit_id' => $unit->id,
                'rate' => 65000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('test_parameters', ['name' => 'Uji Warna Presisi', 'rate' => 65000]);

        $this->actingAs($admin)
            ->delete(route('admin.test-parameters.destroy', $parameter->refresh()))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('test_parameters', ['id' => $parameter->id]);
    }

    public function test_admin_can_toggle_parameter_active_status(): void
    {
        $admin = User::factory()->create();
        $unit = $this->createUnit();
        $parameter = $this->createParameter($unit);

        $this->actingAs($admin)
            ->patch(route('admin.test-parameters.toggle', $parameter))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($parameter->refresh()->is_active);

        $this->actingAs($admin)
            ->patch(route('admin.test-parameters.toggle', $parameter))
            ->assertRedirect();

        $this->assertTrue($parameter->refresh()->is_active);
    }

    public function test_admin_can_manage_sample_forms_and_types(): void
    {
        $admin = User::factory()->create();

        // Halaman Bentuk & Jenis.
        $this->actingAs($admin)
            ->get(route('admin.sample-attributes.index'))
            ->assertOk()
            ->assertSee('Bentuk & Jenis Sampel');

        // Tambah bentuk & jenis.
        $this->actingAs($admin)
            ->post(route('admin.sample-attributes.forms.store'), ['name' => 'Pasta'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas('sample_forms', ['name' => 'Pasta']);

        $this->actingAs($admin)
            ->post(route('admin.sample-attributes.types.store'), ['name' => 'Kosmetik'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas('sample_types', ['name' => 'Kosmetik']);

        // Update.
        $form = SampleForm::where('name', 'Pasta')->first();
        $this->actingAs($admin)
            ->put(route('admin.sample-attributes.forms.update', $form), ['name' => 'Pasta Semi Kering', 'is_active' => '1'])
            ->assertRedirect();
        $this->assertDatabaseHas('sample_forms', ['name' => 'Pasta Semi Kering']);

        // Hapus.
        $this->actingAs($admin)
            ->delete(route('admin.sample-attributes.forms.destroy', $form->refresh()))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('sample_forms', ['id' => $form->id]);

        $type = SampleType::where('name', 'Kosmetik')->first();
        $this->actingAs($admin)
            ->delete(route('admin.sample-attributes.types.destroy', $type))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('sample_types', ['id' => $type->id]);
    }

    public function test_parameter_in_use_cannot_be_deleted(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $unit = $this->createUnit();
        $p1 = $this->createParameter($unit);

        $test = $this->createSampleTest($user, ['code' => 'UJI-TEST-DEL']);
        $this->attachItem($test, $p1);

        $this->actingAs($admin)
            ->delete(route('admin.test-parameters.destroy', $p1))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('test_parameters', ['id' => $p1->id]);
    }

    public function test_admin_can_filter_sample_tests_by_status(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->createSampleTest($user, ['code' => 'UJI-FILTER-1']);
        $this->createSampleTest($user, [
            'code' => 'UJI-FILTER-2',
            'status' => SampleTest::STATUS_DONE,
            'payment_status' => SampleTest::PAYMENT_PAID,
            'invoice_number' => 'INV-UJI-FILTER-2',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sample-tests.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('UJI-FILTER-1')
            ->assertDontSee('UJI-FILTER-2');
    }
}
