<?php

namespace Tests\Feature;

use App\Models\FooterLogo;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_footer_address_and_contact(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.footer.index'))
            ->assertOk()
            ->assertSee('Pengaturan Footer', false);

        $this->actingAs($admin)
            ->put(route('admin.footer.settings-update'), [
                'footer_address' => "Jl. Merdeka No. 10\nYogyakarta",
                'footer_phone' => '+62 812-1111-2222',
                'footer_email' => 'hello@marketlabs.id',
            ])
            ->assertRedirect(route('admin.footer.index'))
            ->assertSessionHas('success');

        $this->assertSame("Jl. Merdeka No. 10\nYogyakarta", Setting::get('footer_address'));
        $this->assertSame('+62 812-1111-2222', Setting::get('footer_phone'));
        $this->assertSame('hello@marketlabs.id', Setting::get('footer_email'));

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('Jl. Merdeka No. 10', false)
            ->assertSee('Yogyakarta', false)
            ->assertSee('+62 812-1111-2222', false)
            ->assertSee('hello@marketlabs.id', false)
            ->assertSee('mailto:hello@marketlabs.id', false);
    }

    public function test_admin_can_add_footer_logo_and_it_appears_in_footer(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.footer.logo-store'), [
                'name' => 'UPT Laboratorium Terpadu',
                'image' => UploadedFile::fake()->image('logo.png', 200, 100),
                'url' => 'https://upt.unisa.ac.id',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.footer.index'))
            ->assertSessionHas('success');

        $logo = FooterLogo::first();

        $this->assertNotNull($logo);
        $this->assertSame('UPT Laboratorium Terpadu', $logo->name);
        $this->assertSame('https://upt.unisa.ac.id', $logo->url);
        $this->assertTrue($logo->is_active);
        Storage::disk('public')->assertExists($logo->image);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee('UPT Laboratorium Terpadu', false)
            ->assertSee('storage/' . $logo->image, false);
    }

    public function test_admin_can_update_footer_logo_and_old_image_is_deleted(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.footer.logo-store'), [
                'name' => 'Logo Lama',
                'image' => UploadedFile::fake()->image('old.png'),
                'is_active' => '1',
            ]);

        $logo = FooterLogo::first();
        $oldPath = $logo->image;

        $this->actingAs($admin)
            ->put(route('admin.footer.logo-update', $logo), [
                'name' => 'Logo Baru',
                'image' => UploadedFile::fake()->image('new.png'),
                'url' => 'https://example.com',
                'is_active' => '0',
            ])
            ->assertRedirect(route('admin.footer.index'))
            ->assertSessionHas('success');

        $logo->refresh();

        $this->assertSame('Logo Baru', $logo->name);
        $this->assertFalse($logo->is_active);
        Storage::disk('public')->assertExists($logo->image);
        Storage::disk('public')->assertMissing($oldPath);

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertDontSee('storage/' . $logo->image, false);
    }

    public function test_admin_can_delete_footer_logo_with_its_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.footer.logo-store'), [
                'name' => 'Logo A',
                'image' => UploadedFile::fake()->image('logo-a.png'),
                'is_active' => '1',
            ]);

        $logo = FooterLogo::first();
        $path = $logo->image;

        $this->actingAs($admin)
            ->delete(route('admin.footer.logo-destroy', $logo))
            ->assertRedirect(route('admin.footer.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('footer_logos', ['id' => $logo->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_reorder_footer_logos(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();

        foreach (['Logo A', 'Logo B'] as $i => $name) {
            $this->actingAs($admin)
                ->post(route('admin.footer.logo-store'), [
                    'name' => $name,
                    'image' => UploadedFile::fake()->image("logo-{$i}.png"),
                    'is_active' => '1',
                ]);
        }

        $a = FooterLogo::where('name', 'Logo A')->first();
        $b = FooterLogo::where('name', 'Logo B')->first();

        $this->actingAs($admin)
            ->post(route('admin.footer.logo-move', ['logo' => $a, 'direction' => 'down']))
            ->assertRedirect(route('admin.footer.index'))
            ->assertSessionHas('success');

        $this->assertGreaterThan($b->fresh()->sort_order, $a->fresh()->sort_order);

        $this->actingAs($admin)
            ->post(route('admin.footer.logo-move', ['logo' => $a, 'direction' => 'up']))
            ->assertRedirect(route('admin.footer.index'));

        $this->assertLessThan($b->fresh()->sort_order, $a->fresh()->sort_order);
    }

    public function test_inactive_footer_logos_are_hidden_from_footer(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.footer.logo-store'), [
                'name' => 'Logo Nonaktif',
                'image' => UploadedFile::fake()->image('hidden.png'),
                'is_active' => '0',
            ]);

        $logo = FooterLogo::first();

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertDontSee('storage/' . $logo->image, false);
    }
}