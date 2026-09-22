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

    public function test_student_grade_submission_rejects_values_outside_the_official_bsu_grade_scale(): void
    {
        $user = User::create([
            'name' => 'Student User',
            'first_name' => 'Student',
            'last_name' => 'User',
            'email' => 'student.invalidgrade@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        session(['user_id' => $user->id, 'role' => 'student']);

        $response = $this->post(route('student.grades.upload.submit'), [
            'school_year' => '2025-2026',
            'semester' => '1st Semester',
            'grades' => [
                [
                    'subject_code' => 'CS101',
                    'subject_name' => 'Introduction to Computing',
                    'grade' => '1.30',
                ],
            ],
            'document' => UploadedFile::fake()->create('grades.pdf', 200, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('grades.0.grade');
        $this->assertDatabaseMissing('student_grades', [
            'user_id' => $user->id,
            'subject_code' => 'CS101',
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

    public function test_student_can_update_only_the_selected_semester_submission(): void
    {
        $user = User::create([
            'name' => 'Student User',
            'first_name' => 'Student',
            'last_name' => 'User',
            'email' => 'student.semester.edit@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        session(['user_id' => $user->id, 'role' => 'student']);

        $firstSubmission = \App\Models\GradeSubmission::create([
            'user_id' => $user->id,
            'school_year' => '2025-2026',
            'semester' => '1st Semester',
            'file_path' => 'student_grades/' . $user->id . '/first.pdf',
            'status' => 'pending',
            'verified_gwa' => 1.50,
        ]);

        $firstSubmission->subjects()->create([
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computing',
            'units' => 3,
            'grade' => 1.50,
        ]);

        \App\Models\StudentGrade::create([
            'user_id' => $user->id,
            'school_year' => '2025-2026',
            'semester' => '1st Semester',
            'subject_code' => 'CS101',
            'subject_name' => 'Introduction to Computing',
            'grade' => 1.50,
            'document_path' => 'student_grades/' . $user->id . '/first.pdf',
            'status' => 'pending',
        ]);

        $secondSubmission = \App\Models\GradeSubmission::create([
            'user_id' => $user->id,
            'school_year' => '2025-2026',
            'semester' => '2nd Semester',
            'file_path' => 'student_grades/' . $user->id . '/second.pdf',
            'status' => 'pending',
            'verified_gwa' => 2.00,
        ]);

        $secondSubmission->subjects()->create([
            'subject_code' => 'CS102',
            'subject_name' => 'Data Structures',
            'units' => 3,
            'grade' => 2.00,
        ]);

        \App\Models\StudentGrade::create([
            'user_id' => $user->id,
            'school_year' => '2025-2026',
            'semester' => '2nd Semester',
            'subject_code' => 'CS102',
            'subject_name' => 'Data Structures',
            'grade' => 2.00,
            'document_path' => 'student_grades/' . $user->id . '/second.pdf',
            'status' => 'pending',
        ]);

        $response = $this->put(route('student.grades.upload.update'), [
            'submission_id' => $secondSubmission->id,
            'school_year' => '2025-2026',
            'semester' => '2nd Semester',
            'grades' => [[
                'subject_code' => 'CS102',
                'subject_name' => 'Data Structures',
                'units' => '3',
                'grade' => '1.75',
            ]],
        ]);

        $response->assertRedirect(route('student.grades.submissions.edit', $secondSubmission));
        $this->assertDatabaseHas('submissions', [
            'id' => $secondSubmission->id,
            'verified_gwa' => 1.75,
        ]);
        $this->assertDatabaseHas('student_grades', [
            'user_id' => $user->id,
            'school_year' => '2025-2026',
            'semester' => '2nd Semester',
            'subject_code' => 'CS102',
            'grade' => 1.75,
        ]);
        $this->assertDatabaseHas('student_grades', [
            'user_id' => $user->id,
            'school_year' => '2025-2026',
            'semester' => '1st Semester',
            'subject_code' => 'CS101',
            'grade' => 1.50,
        ]);
    }

    public function test_student_can_update_a_pending_submission_from_the_grade_history_modal(): void
    {
        $user = User::create([
            'name' => 'Modal Student',
            'first_name' => 'Modal',
            'last_name' => 'Student',
            'email' => 'student.modal.edit@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);
        session(['user_id' => $user->id, 'role' => 'student']);

        $submission = \App\Models\GradeSubmission::create([
            'user_id' => $user->id,
            'school_year' => '2025-2026',
            'semester' => '1st Semester',
            'status' => 'pending',
        ]);
        $submission->subjects()->create([
            'subject_code' => 'CS101',
            'subject_name' => 'Old Subject',
            'units' => 3,
            'grade' => 2.00,
        ]);

        $response = $this->post(route('student.grades.update.json.post', $submission), [
            '_method' => 'PUT',
            'school_year' => '2026-2027',
            'semester' => '2nd Semester',
            'subjects' => [[
                'subject_code' => 'CS201',
                'subject_name' => 'Updated Subject',
                'units' => 3,
                'grade' => '1.75',
            ]],
        ], ['Accept' => 'application/json']);

        $response->assertOk()
            ->assertJsonPath('submission.school_year', '2026-2027')
            ->assertJsonPath('submission.semester', '2nd Semester')
            ->assertJsonPath('submission.subjects.0.subject_code', 'CS201');
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'school_year' => '2026-2027',
            'semester' => '2nd Semester',
        ]);
        $this->assertDatabaseHas('submission_subjects', [
            'submission_id' => $submission->id,
            'subject_code' => 'CS201',
            'grade' => 1.75,
        ]);
    }
}
