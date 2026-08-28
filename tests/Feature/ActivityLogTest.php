<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_actions_are_logged(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin);

        $tool = Tool::create([
            'code' => 'T-001',
            'name' => 'Mikroskop Binokuler',
            'total_stock' => 2,
            'available_stock' => 2,
            'price_per_day' => 50000,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'role' => User::ROLE_ADMIN,
            'user_name' => $admin->name,
            'action' => 'create',
            'subject_type' => Tool::class,
            'subject_id' => $tool->id,
        ]);

        $tool->update(['name' => 'Mikroskop Binokuler X']);

        $updateLog = ActivityLog::where('action', 'update')
            ->where('subject_id', $tool->id)
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertSame('Mikroskop Binokuler', $updateLog->properties['old']['name']);
        $this->assertSame('Mikroskop Binokuler X', $updateLog->properties['new']['name']);
        $this->assertStringContainsString('Mikroskop Binokuler X', $updateLog->description);

        $toolId = $tool->id;
        $tool->delete();

        $this->assertDatabaseHas('activity_logs', [
            'role' => User::ROLE_ADMIN,
            'action' => 'delete',
            'subject_type' => Tool::class,
            'subject_id' => $toolId,
        ]);
    }

    public function test_laboran_actions_are_logged(): void
    {
        $laboran = User::factory()->create(['role' => User::ROLE_LABORAN]);

        $this->actingAs($laboran);

        Tool::create([
            'code' => 'T-002',
            'name' => 'Centrifuge',
            'total_stock' => 1,
            'available_stock' => 1,
            'price_per_day' => 75000,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'role' => User::ROLE_LABORAN,
            'action' => 'create',
            'subject_type' => Tool::class,
        ]);
    }

    public function test_regular_user_actions_are_not_logged(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user);

        Tool::create([
            'code' => 'T-003',
            'name' => 'pH Meter',
            'total_stock' => 3,
            'available_stock' => 3,
            'price_per_day' => 25000,
        ]);

        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_admin_can_view_activity_logs_page(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertSee('Log Aktivitas');
    }

    public function test_non_admin_cannot_view_activity_logs_page(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $laboran = User::factory()->create(['role' => User::ROLE_LABORAN]);

        $this->actingAs($user)
            ->get(route('admin.activity-logs.index'))
            ->assertRedirect(route('home'));

        $this->actingAs($laboran)
            ->get(route('admin.activity-logs.index'))
            ->assertRedirect(route('home'));
    }

    public function test_action_filter_filters_logs(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        Tool::create([
            'code' => 'T-004',
            'name' => 'Neraca Analitik',
            'total_stock' => 1,
            'available_stock' => 1,
            'price_per_day' => 40000,
        ]);

        $this->get(route('admin.activity-logs.index', ['action' => 'create']))
            ->assertOk()
            ->assertSee('menambahkan alat', false);

        $this->get(route('admin.activity-logs.index', ['action' => 'delete']))
            ->assertOk()
            ->assertDontSee('menambahkan alat', false);
    }

    public function test_admin_login_and_logout_are_logged(): void
    {
        $admin = User::factory()->create([
            'nim_nip' => '7777-8888-99',
            'password' => bcrypt('rahasia123'),
        ]);

        $this->post(route('login'), [
            'nim_nip' => '7777-8888-99',
            'password' => 'rahasia123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'action' => 'login',
            'description' => 'masuk ke sistem',
        ]);

        $this->post(route('logout'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'action' => 'logout',
            'description' => 'keluar dari sistem',
        ]);
    }
}
