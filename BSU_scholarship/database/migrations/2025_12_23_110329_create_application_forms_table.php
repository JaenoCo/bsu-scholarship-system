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
        Schema::create('application_forms', function (Blueprint $table) {
            $table->id();
            $table->string('form_name');
            $table->string('form_type')->nullable(); // e.g., 'SFAO Application Form', 'TDP Application Form', etc.
            $table->text('description')->nullable();
            $table->string('file_path'); // Storage path to the file
            $table->string('file_type'); // docx, pdf, etc.
            $table->unsignedBigInteger('campus_id'); // Which campus this form belongs to
            $table->unsignedBigInteger('uploaded_by'); // SFAO user who uploaded
            $table->integer('download_count')->default(0);
            $table->timestamps();

            $table->foreign('campus_id')->references('id')->on('campuses')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_forms');
    }
};
