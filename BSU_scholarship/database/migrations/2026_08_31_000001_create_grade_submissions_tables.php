<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('school_year', 20);
            $table->string('semester', 50);
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('submission_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->string('subject_code', 50);
            $table->string('subject_name');
            $table->decimal('units', 5, 2)->default(0);
            $table->decimal('grade', 5, 2);
            $table->timestamps();
        });

        if (Schema::hasTable('student_grades')) {
            $legacyRows = DB::table('student_grades')->orderBy('id')->get();
            foreach ($legacyRows->groupBy(fn ($row) => implode('|', [
                $row->user_id,
                $row->school_year ?? $row->academic_year ?? '',
                $row->semester,
                $row->document_path ?? $row->proof_file ?? '',
                $row->status ?? 'pending',
            ])) as $rows) {
                $first = $rows->first();
                $status = $first->status ?? 'pending';
                $status = $status === 'verified' ? 'approved' : ($status === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'pending'));
                $submissionId = DB::table('submissions')->insertGetId([
                    'user_id' => $first->user_id,
                    'school_year' => $first->school_year ?? $first->academic_year,
                    'semester' => $first->semester,
                    'file_path' => $first->document_path ?? $first->proof_file,
                    'status' => $status,
                    'remarks' => $first->remarks ?? null,
                    'created_at' => $first->created_at,
                    'updated_at' => $first->updated_at,
                ]);

                foreach ($rows as $row) {
                    DB::table('submission_subjects')->insert([
                        'submission_id' => $submissionId,
                        'subject_code' => $row->subject_code ?? $row->course_code,
                        'subject_name' => $row->subject_name ?? $row->course_title,
                        'units' => $row->units ?? 0,
                        'grade' => $row->grade,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_subjects');
        Schema::dropIfExists('submissions');
    }
};
