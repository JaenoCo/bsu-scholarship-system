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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sfao_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campus_id')->constrained('campuses')->onDelete('cascade');
            $table->string('original_campus_selection')->nullable(); // Store original campus selection
            $table->string('report_type'); // 'monthly', 'quarterly', 'annual', 'custom'
            
            // Filters
            $table->string('student_type')->nullable();
            $table->unsignedBigInteger('college_id')->nullable();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->unsignedBigInteger('track_id')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('report_period_start');
            $table->date('report_period_end');
            
            // Report data as JSON
            $table->json('report_data'); // Contains all the statistics and data
            
            // Status tracking
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'approved'])->default('draft');
            $table->text('notes')->nullable(); // SFAO notes
            $table->text('central_feedback')->nullable(); // Central admin feedback
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['sfao_user_id', 'status']);
            $table->index(['campus_id', 'report_type']);
            $table->index(['report_period_start', 'report_period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
