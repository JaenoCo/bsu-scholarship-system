<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScholarEditFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_central_admin_can_open_and_update_a_scholar_record(): void
    {
        $campus = Campus::create(['name' => 'Main Campus', 'type' => 'constituent']);
        $centralUser = User::create([
            'name' => 'Central Admin',
            'first_name' => 'Central',
            'last_name' => 'Admin',
            'email' => 'central@example.com',
            'password' => bcrypt('password123'),
            'role' => 'central',
            'campus_id' => $campus->id,
        ]);

        $student = User::create([
            'name' => 'Juan Dela Cruz',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'student@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'campus_id' => $campus->id,
        ]);

        $scholarship = Scholarship::create([
            'scholarship_name' => 'Academic Excellence',
            'scholarship_type' => 'private',
            'grant_type' => 'recurring',
            'is_active' => true,
            'description' => 'Test scholarship',
            'submission_deadline' => now()->addMonth()->toDateString(),
            'created_by' => $centralUser->id,
        ]);

        $scholar = Scholar::create([
            'user_id' => $student->id,
            'scholarship_id' => $scholarship->id,
            'type' => 'new',
            'status' => 'active',
            'scholarship_start_date' => '2025-01-01',
            'scholarship_end_date' => '2025-12-31',
            'notes' => 'Original note',
        ]);

        $this->withSession(['user_id' => $centralUser->id, 'role' => 'central']);

        $response = $this->get(route('central.scholars.edit', $scholar));
        $response->assertOk();
        $response->assertSee('Edit Scholar');

        $updateResponse = $this->put(route('central.scholars.update', $scholar), [
            'scholarship_id' => $scholarship->id,
            'type' => 'old',
            'status' => 'completed',
            'scholarship_start_date' => '2025-02-01',
            'scholarship_end_date' => '2026-01-31',
            'notes' => 'Updated note',
        ]);

        $updateResponse->assertRedirect(route('central.scholars.show', $scholar));

        $scholar->refresh();
        $this->assertSame('old', $scholar->type);
        $this->assertSame('completed', $scholar->status);
        $this->assertSame('Updated note', $scholar->notes);
    }
}
