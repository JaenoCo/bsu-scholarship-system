<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StudentGradesUploadTest extends TestCase
{
    public function test_student_can_submit_grade_records_with_a_single_supporting_document(): void
    {
        $user = User::create([
            'name' => 'Student User',
            'first_name' => 'Student',
            'last_name' => 'User',
            'email' => 'student.upload@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        session(['user_id' => $user->id, 'role' => 'student']);

        $file = UploadedFile::fake()->create('grades.pdf', 200, 'application/pdf');

        $response = $this->post(route('student.grades.upload.submit'), [
            'school_year' => '2025-2026',
            'semester' => '1st Semester',
            'grades' => [
                [
                    'subject_code' => 'CS101',
                    'subject_name' => 'Introduction to Computing',
                    'grade' => '95.00',
                ],
            ],
            'document' => $file,
        ]);

        $response->assertRedirect(route('student.grades.upload'));
        $this->assertDatabaseHas('student_grades', [
            'user_id' => $user->id,
            'school_year' => '2025-2026',
            'semester' => '1st Semester',
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computing',
            'grade' => 95.00,
        ]);
    }

    public function test_student_can_view_submitted_grades_and_reopen_the_form_for_editing(): void
    {
        $user = User::create([
            'name' => 'Student User',
            'first_name' => 'Student',
            'last_name' => 'User',
            'email' => 'student.view@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        session(['user_id' => $user->id, 'role' => 'student']);

        \App\Models\StudentGrade::create([
            'user_id' => $user->id,
            'school_year' => '2025-2026',
            'semester' => '1st Semester',
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computing',
            'grade' => 95.00,
            'document_path' => 'student_grades/' . $user->id . '/grades.pdf',
            'status' => 'pending',
        ]);

        $viewResponse = $this->get(route('student.grades.upload'));
        $viewResponse->assertOk();
        $viewResponse->assertViewHas('isReadOnly', true);
        $viewResponse->assertViewHas('isEditMode', false);

        $editResponse = $this->get(route('student.grades.edit'));
        $editResponse->assertOk();
        $editResponse->assertViewHas('isReadOnly', false);
        $editResponse->assertViewHas('isEditMode', true);
    }
}
