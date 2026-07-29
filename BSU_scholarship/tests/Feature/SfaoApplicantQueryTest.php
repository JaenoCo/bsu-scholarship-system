<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Campus;
use App\Models\Scholarship;
use App\Models\StudentSubmittedDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SfaoApplicantQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sfao_applicants_list_returns_students_and_status_counts(): void
    {
        $constituentCampus = Campus::create([
            'name' => 'Campus A',
            'type' => 'constituent',
            'has_sfao_admin' => true,
        ]);

        $extensionCampus = Campus::create([
            'name' => 'Campus A Extension',
            'type' => 'extension',
            'parent_campus_id' => $constituentCampus->id,
            'has_sfao_admin' => false,
        ]);

        $sfaoUser = User::factory()->create([
            'name' => 'SFAO Admin',
            'email' => 'sfao@example.com',
            'role' => 'sfao',
            'campus_id' => $constituentCampus->id,
        ]);

        $studentA = User::factory()->create([
            'name' => 'Student A',
            'email' => 'student.a@example.com',
            'role' => 'student',
            'campus_id' => $constituentCampus->id,
        ]);

        $studentB = User::factory()->create([
            'name' => 'Student B',
            'email' => 'student.b@example.com',
            'role' => 'student',
            'campus_id' => $extensionCampus->id,
        ]);

        $outsideCampus = Campus::create([
            'name' => 'Campus B',
            'type' => 'constituent',
            'has_sfao_admin' => false,
        ]);

        $outsideStudent = User::factory()->create([
            'name' => 'Outside Student',
            'email' => 'outside@example.com',
            'role' => 'student',
            'campus_id' => $outsideCampus->id,
        ]);

        $scholarship = Scholarship::create([
            'scholarship_name' => 'Test Scholarship',
            'description' => 'Test scholarship',
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'created_by' => $sfaoUser->id,
        ]);

        Application::create([
            'user_id' => $studentA->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'in_progress',
        ]);

        Application::create([
            'user_id' => $studentB->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'pending',
        ]);

        StudentSubmittedDocument::create([
            'user_id' => $studentA->id,
            'scholarship_id' => $scholarship->id,
            'document_category' => 'sfao_required',
            'document_name' => 'ID',
            'file_path' => 'documents/id.pdf',
            'original_filename' => 'id.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 12345,
            'is_mandatory' => true,
            'description' => 'Test document',
            'evaluation_status' => 'pending',
        ]);

        session(['user_id' => $sfaoUser->id, 'role' => 'sfao']);

        $response = $this->getJson(route('sfao.applicants.list', ['tab' => 'applicants']));

        $response->assertOk();
        $response->assertJsonPath('counts.total', 2);
        $response->assertJsonPath('counts.in_progress', 1);
        $response->assertJsonPath('counts.pending', 1);
        $response->assertJsonPath('counts.approved', 0);
        $response->assertJsonPath('counts.rejected', 0);
        $this->assertStringContainsString('Student A', $response->json('html'));
        $this->assertStringContainsString('Student B', $response->json('html'));
        $this->assertStringNotContainsString('Outside Student', $response->json('html'));
    }
}
