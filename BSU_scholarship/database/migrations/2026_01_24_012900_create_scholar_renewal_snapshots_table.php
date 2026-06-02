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
        Schema::create('scholar_renewal_snapshots', function (Blueprint $table) {
            // Primary Key
            $table->id()->comment('Primary key for each snapshot');

            // Foreign keys / context
            $table->unsignedBigInteger('scholar_id')->comment('FK to scholar/student');
            $table->unsignedBigInteger('scholarship_id')->comment('FK to scholarship program');
            $table->unsignedBigInteger('campus_id')->comment('FK to campus');

            // Grant / Renewal info
            $table->unsignedInteger('grant_no')->comment('Grant number for this scholar');
            $table->unsignedInteger('renewal_count')->comment('Number of renewals attempted');

            // Academic performance
            $table->decimal('current_gwa', 4, 2)->comment('Current GWA of the scholar');
            $table->decimal('previous_gwa', 4, 2)->nullable()->comment('Previous GWA, nullable if first grant');
            $table->decimal('gwa_change', 4, 2)->nullable()->comment('Difference between current and previous GWA');
            $table->decimal('lowest_subject_grade', 4, 2)->nullable()->comment('Lowest grade in any subject');
            $table->unsignedInteger('failed_subject_count')->nullable()->comment('Number of failed subjects');

            // Scholarship rule context
            $table->decimal('min_gwa_required', 4, 2)->comment('Minimum GWA required for scholarship eligibility');
            $table->decimal('gwa_margin', 4, 2)->nullable()->comment('Distance from minimum GWA');

            // Behavioral / process signals
            $table->unsignedInteger('days_late_submission')->nullable()->comment('Days late in submitting documents');
            $table->unsignedInteger('rejected_docs_count')->nullable()->comment('Number of documents rejected by SFAO');
            $table->unsignedInteger('endorsement_delay_days')->nullable()->comment('Delay in endorsement from campus');

            // Stability flags
            $table->boolean('has_previous_warning')->default(false)->comment('Whether scholar received a prior warning');
            $table->boolean('on_probation')->default(false)->comment('Whether scholar is on probation');

            // ML label
            $table->boolean('failed_next_renewal')->comment('Target label: 1 if scholar fails next renewal, 0 if successful');

            // Snapshot metadata
            $table->date('snapshot_date')->comment('Date when this snapshot was recorded');

            // Laravel timestamps
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['scholar_id', 'scholarship_id', 'campus_id'], 'srs_main_index');
            $table->index('snapshot_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholar_renewal_snapshots');
    }
};
