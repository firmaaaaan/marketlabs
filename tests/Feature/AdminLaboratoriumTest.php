<?php

namespace Tests\Feature;

use App\Models\Laboratorium;
use App\Models\ResearchProposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLaboratoriumTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_laboratoriums(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.laboratoriums.index'))
            ->assertOk()
            ->assertSee('Kelola Laboratorium', false);

        $this->actingAs($admin)
            ->post(route('admin.laboratoriums.store'), [
                'name' => 'Laboratorium Kimia Analitik',
                'code' => 'LAB-KIM-01',
                'description' => 'Lab untuk pengujian kimia.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.laboratoriums.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('laboratoriums', [
            'name' => 'Laboratorium Kimia Analitik',
            'code' => 'LAB-KIM-01',
            'is_active' => true,
        ]);

        $lab = Laboratorium::first();

        $this->actingAs($admin)
            ->put(route('admin.laboratoriums.update', $lab), [
                'name' => 'Lab Kimia',
                'code' => 'LAB-KIM-01',
                'is_active' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('laboratoriums', ['name' => 'Lab Kimia']);
    }

    public function test_laboratorium_in_use_cannot_be_deleted(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $lab = Laboratorium::create(['name' => 'Lab Biologi']);

        ResearchProposal::create([
            'user_id' => $user->id,
            'code' => 'RST-TEST-LAB-01',
            'title' => 'Riset Lab',
            'field' => 'Biologi',
            'status' => ResearchProposal::STATUS_PENDING,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(7),
            'laboratorium_id' => $lab->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.laboratoriums.destroy', $lab))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('laboratoriums', ['id' => $lab->id]);
    }
}
