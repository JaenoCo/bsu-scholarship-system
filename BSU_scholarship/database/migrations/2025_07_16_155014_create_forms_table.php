<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();

            // Link to users table
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // ------------------- PERSONAL DATA -------------------
            // Removed: last_name, first_name, middle_name (in users)
            
            $table->integer('age')->nullable();
            // Removed: sex (in users)
            $table->string('civil_status')->nullable();
            // Removed: birthdate (in users)
            $table->string('birthplace')->nullable();
            // Removed: email, contact_number (in users)

            // Address broken down
            $table->string('street_barangay')->nullable();
            $table->string('town_city')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code')->nullable();

            $table->string('citizenship')->nullable();
            $table->string('disability')->nullable(); // Type of disability
            $table->string('tribe')->nullable(); // Tribal membership

            // ------------------- ACADEMIC DATA -------------------
            // Removed: sr_code, education_level, program, college_department, year_level, campus (in users)
            $table->decimal('previous_gwa', 4, 2)->nullable();
            $table->string('honors_received')->nullable();
            $table->integer('units_enrolled')->nullable();
            $table->string('scholarship_applied')->nullable();
            $table->string('semester')->nullable();
            $table->string('academic_year')->nullable();
            $table->boolean('has_existing_scholarship')->default(false);
            $table->text('existing_scholarship_details')->nullable();

            // ------------------- FAMILY DATA -------------------
            $table->enum('father_status', ['living', 'deceased'])->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_address')->nullable();
            $table->string('father_contact')->nullable();
            $table->string('father_occupation')->nullable();

            $table->enum('mother_status', ['living', 'deceased'])->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_address')->nullable();
            $table->string('mother_contact')->nullable();
            $table->string('mother_occupation')->nullable();

            $table->string('estimated_gross_annual_income')->nullable();
            $table->integer('siblings_count')->nullable();

            // ------------------- ESSAY / QUESTION -------------------
            $table->text('reason_for_applying')->nullable();

            // ------------------- CERTIFICATION -------------------
            $table->string('student_signature')->nullable();
            $table->date('date_signed')->nullable();

            // ------------------- STATUS / META -------------------
            $table->enum('form_status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('draft');
            $table->text('reviewer_remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forms');
    }
};
