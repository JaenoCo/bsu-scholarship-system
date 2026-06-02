<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_grades', function (Blueprint $table) {
            // Primary Key
            $table->id()->comment('Primary key for each grade entry');

            // Foreign keys / context
            $table->unsignedBigInteger('student_id')->comment('FK to student');
            $table->unsignedBigInteger('scholarship_id')->comment('FK to scholarship program');
            $table->unsignedInteger('units_enrolled')->nullable()->comment('Total units student enrolled in this semester, from SFAO Application Form');

            // Course details
            $table->string('course_code')->comment('Course code');
            $table->string('course_title')->comment('Course title');
            $table->decimal('grade', 4, 2)->comment('Grade in the course');
            $table->string('professor_name')->nullable()->comment('Name of professor');

            // Proof / verification
            $table->string('proof_file')->nullable()->comment('Path to uploaded COR or report card');
            $table->boolean('verified_by_sfao')->default(false)->comment('Whether SFAO verified this grade');
            $table->dateTime('verification_date')->nullable()->comment('Date when SFAO verified this grade');

            // Semester / academic period
            $table->string('academic_year')->comment('Academic year (e.g., 2024–2025)');
            $table->string('semester')->comment('Semester for the grade (e.g., 2nd Sem)');

            // Multi-stage workflow tracking
            $table->boolean('stage_courses_added')->default(false)->comment('Stage 1: courses added');
            $table->boolean('stage_proof_uploaded')->default(false)->comment('Stage 2: proof uploaded');
            $table->boolean('stage_confirmed')->default(false)->comment('Stage 3: student confirmed submission');

            // Laravel timestamps
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['student_id', 'scholarship_id', 'academic_year', 'semester'], 'sg_main_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
