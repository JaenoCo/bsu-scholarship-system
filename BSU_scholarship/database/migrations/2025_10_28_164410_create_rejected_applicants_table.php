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
        Schema::create('rejected_applicants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Student who was rejected
            $table->unsignedBigInteger('scholarship_id'); // Scholarship they were rejected from
            $table->unsignedBigInteger('application_id'); // Original application ID
            $table->enum('rejected_by', ['sfao', 'central']); // Who rejected the application
            $table->unsignedBigInteger('rejected_by_user_id'); // ID of the user who rejected (SFAO or Central admin)
            $table->text('rejection_reason')->nullable(); // Reason for rejection
            $table->text('remarks')->nullable(); // Additional remarks
            $table->json('rejection_data')->nullable(); // Additional data about the rejection
            $table->timestamp('rejected_at'); // When the rejection occurred
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('scholarship_id')->references('id')->on('scholarships')->onDelete('cascade');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
            $table->foreign('rejected_by_user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['user_id', 'scholarship_id']);
            $table->index(['rejected_by', 'rejected_at']);
            $table->index('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rejected_applicants');
    }
};
