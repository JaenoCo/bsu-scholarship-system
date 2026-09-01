<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $private = Scholarship::where('scholarship_type', 'private')->first();
        $government = Scholarship::where('scholarship_type', 'government')->first();

        if (!$private || !$government) {
            $this->command->warn('A private and government scholarship are required.');
            return;
        }

        $students = User::where('role', 'student')->where('sr_code', 'like', 'SR-%')->orderBy('id')->get();
        foreach ($students->values() as $index => $student) {
            $createdAt = $student->created_at ?: now();
            Application::where('user_id', $student->id)->delete();
            $this->clearSeededRecords($student);

            $applications = [Application::create([
                'user_id' => $student->id,
                'scholarship_id' => $private->id,
                'status' => 'pending',
                'grant_count' => 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])];

            if ($index % 3 === 0) {
                $applications[] = Application::create([
                    'user_id' => $student->id,
                    'scholarship_id' => $government->id,
                    'status' => 'pending',
                    'grant_count' => 0,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $this->seedGrades($student, $createdAt, $private->id);
            foreach ($applications as $application) {
                $this->seedDocuments($student, $application->scholarship_id, $createdAt);
                $this->seedStatusLog($student, $application, $createdAt);
            }
        }
    }

    private function clearSeededRecords(User $student): void
    {
        if (Schema::hasTable('student_submitted_documents')) {
            DB::table('student_submitted_documents')->where('user_id', $student->id)->delete();
        }
        if (Schema::hasTable('student_grades')) {
            $column = Schema::hasColumn('student_grades', 'user_id') ? 'user_id' : 'student_id';
            DB::table('student_grades')->where($column, $student->id)->delete();
        }
        if (Schema::hasTable('submissions')) {
            DB::table('submissions')->where('user_id', $student->id)->delete();
        }
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')->where('user_id', $student->id)->where('type', 'application_status')->delete();
        }
    }

    private function seedGrades(User $student, $createdAt, int $scholarshipId): void
    {
        $subjects = [
            ['code' => 'GENED101', 'name' => 'Purposive Communication', 'grade' => 1.50],
            ['code' => 'GENED102', 'name' => 'Readings in Philippine History', 'grade' => 1.75],
            ['code' => 'MATH101', 'name' => 'Mathematics in the Modern World', 'grade' => 2.00],
            ['code' => 'PROG101', 'name' => 'Introduction to Computing', 'grade' => 1.75],
        ];

        if (Schema::hasTable('student_grades')) {
            $columns = Schema::getColumnListing('student_grades');
            foreach ($subjects as $subject) {
                $row = [
                    'user_id' => $student->id, 'student_id' => $student->id, 'scholarship_id' => $scholarshipId,
                    'school_year' => '2025-2026', 'academic_year' => '2025-2026', 'semester' => '1st Sem',
                    'subject_code' => $subject['code'], 'course_code' => $subject['code'],
                    'subject_name' => $subject['name'], 'course_title' => $subject['name'], 'grade' => $subject['grade'],
                    'document_path' => 'seeded/grades/' . $student->sr_code . '.pdf',
                    'proof_file' => 'seeded/grades/' . $student->sr_code . '.pdf', 'status' => 'pending',
                    'verified_by_sfao' => false, 'units' => 3, 'units_enrolled' => 21,
                    'created_at' => $createdAt, 'updated_at' => $createdAt,
                ];
                DB::table('student_grades')->insert(array_intersect_key($row, array_flip($columns)));
            }
        }

        if (Schema::hasTable('submissions') && Schema::hasTable('submission_subjects')) {
            $submissionId = DB::table('submissions')->insertGetId([
                'user_id' => $student->id, 'school_year' => '2025-2026', 'semester' => '1st Sem',
                'file_path' => 'seeded/grades/' . $student->sr_code . '.pdf', 'status' => 'pending',
                'created_at' => $createdAt, 'updated_at' => $createdAt,
            ]);
            foreach ($subjects as $subject) {
                DB::table('submission_subjects')->insert([
                    'submission_id' => $submissionId, 'subject_code' => $subject['code'],
                    'subject_name' => $subject['name'], 'units' => 3, 'grade' => $subject['grade'],
                    'created_at' => $createdAt, 'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function seedDocuments(User $student, int $scholarshipId, $createdAt): void
    {
        if (!Schema::hasTable('student_submitted_documents')) {
            return;
        }

        $columns = Schema::getColumnListing('student_submitted_documents');
        foreach ([
            ['name' => 'Certificate of Indigency', 'category' => 'scholarship_required', 'file' => 'certificate_of_indigency.pdf'],
            ['name' => 'Transcript of Records', 'category' => 'sfao_required', 'file' => 'transcript_of_records.pdf'],
            ['name' => 'Valid Student ID', 'category' => 'sfao_required', 'file' => 'student_id.jpg'],
        ] as $document) {
            $row = [
                'user_id' => $student->id, 'scholarship_id' => $scholarshipId,
                'document_category' => $document['category'], 'document_name' => $document['name'],
                'academic_year' => '2025-2026', 'semester' => '1st Sem',
                'file_path' => 'seeded/documents/' . $student->sr_code . '/' . $document['file'],
                'original_filename' => $document['file'], 'file_type' => pathinfo($document['file'], PATHINFO_EXTENSION),
                'file_size' => 204800, 'is_mandatory' => true, 'evaluation_status' => 'pending',
                'declared_gwa' => 1.75, 'extracted_gwa' => 1.75,
                'created_at' => $createdAt, 'updated_at' => $createdAt,
            ];
            DB::table('student_submitted_documents')->insert(array_intersect_key($row, array_flip($columns)));
        }
    }

    private function seedStatusLog(User $student, Application $application, $createdAt): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        DB::table('notifications')->insert([
            'user_id' => $student->id, 'type' => 'application_status', 'title' => 'Application submitted',
            'message' => 'Your scholarship application is pending evaluation.',
            'data' => json_encode(['application_id' => $application->id, 'status' => 'pending']),
            'is_read' => false, 'created_at' => $createdAt, 'updated_at' => $createdAt,
        ]);
    }
}
