<?php
// database/migrations/xxxx_xx_xx_create_scholarships_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('scholarships')) {
            Schema::create('scholarships', function (Blueprint $table) {
                $table->id();
                $table->string('scholarship_name');
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->onDelete('set null');
                $table->enum('scholarship_type', ['private', 'government'])->default('private');
                $table->text('description');
                $table->date('submission_deadline'); // Changed from deadline
                $table->date('application_start_date')->nullable(); // When applications can start
                $table->integer('slots_available')->nullable();
                $table->decimal('grant_amount', 10, 2)->nullable(); // ₱99999999.99 max
                $table->boolean('renewal_allowed')->default(false); // whether renewal is allowed
                $table->enum('grant_type', ['one_time', 'recurring', 'discontinued'])->default('recurring'); // Grant distribution type
                $table->boolean('is_active')->default(true);
                $table->boolean('allow_existing_scholarship')->default(false); // Whether this scholarship can be applied for if student has another scholarship
                $table->text('eligibility_notes')->nullable(); // Additional eligibility information
                $table->string('background_image')->nullable(); // Background image for scholarship display
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        } else {
            // Table already exists, update existing records and enum
            // Update existing records from 'public' to 'private'
            DB::table('scholarships')
                ->where('scholarship_type', 'public')
                ->update(['scholarship_type' => 'private']);

            // Update existing records from 'internal' and 'external' to 'private'
            DB::table('scholarships')
                ->whereIn('scholarship_type', ['internal', 'external'])
                ->update(['scholarship_type' => 'private']);

            // Modify the enum column to remove 'public', 'internal', and 'external'
            DB::statement("ALTER TABLE scholarships MODIFY COLUMN scholarship_type ENUM('private', 'government') DEFAULT 'private'");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
