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
        Schema::create('scholars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('scholarship_id')->constrained()->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['new', 'old'])->default('new');
            $table->integer('grant_count')->default(0);
            $table->decimal('total_grant_received', 10, 2)->default(0.00);
            $table->date('scholarship_start_date');
            $table->date('scholarship_end_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->json('grant_history')->nullable(); // Store grant history as JSON
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'scholarship_id']);
            $table->index(['type', 'status']);
            $table->index('scholarship_start_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scholars');
    }
};
