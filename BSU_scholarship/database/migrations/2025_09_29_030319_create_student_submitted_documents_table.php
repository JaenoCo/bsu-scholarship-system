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
        Schema::create('student_submitted_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('scholarship_id');
            $table->enum('document_category', ['sfao_required', 'scholarship_required']);
            $table->string('document_name'); // e.g. 'form_137', 'grades', 'Barangay Clearance', 'Birth Certificate'
            $table->string('file_path'); // Path to the uploaded file
            $table->string('original_filename'); // Original filename when uploaded
            $table->string('file_type'); // pdf, jpg, png, etc.
            $table->bigInteger('file_size'); // File size in bytes
            $table->boolean('is_mandatory')->default(true); // Whether this document was mandatory
            $table->text('description')->nullable(); // Optional description
            
            // Evaluation fields
            $table->enum('evaluation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('evaluation_notes')->nullable(); // SFAO evaluation notes
            $table->unsignedBigInteger('evaluated_by')->nullable(); // SFAO admin who evaluated
            $table->timestamp('evaluated_at')->nullable(); // When evaluation was completed
            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('scholarship_id')->references('id')->on('scholarships')->onDelete('cascade');
            $table->foreign('evaluated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for better performance
            $table->index(['user_id', 'scholarship_id', 'document_category'], 'ssd_user_scholarship_category_idx');
            $table->index(['document_category', 'is_mandatory'], 'ssd_category_mandatory_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_submitted_documents');
    }
};