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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('birthdate')->nullable();
            $table->enum('sex', ['Male', 'Female'])->nullable();
            
            $table->string('email')->unique();
            $table->string('contact_number')->nullable();
            $table->timestamp('email_verified_at')->nullable(); // ✅ for email verification
            $table->string('password');
            
            $table->string('role')->default('student'); // student, admin, etc.
            $table->string('profile_picture')->nullable(); // optional profile picture

            // Academic Info
            $table->string('sr_code')->unique()->nullable();
            $table->string('education_level')->nullable();
            $table->string('college')->nullable();
            $table->string('program')->nullable();
            $table->string('track')->nullable();
            $table->string('year_level')->nullable();

            // 🔗 Each user belongs to one campus (nullable)
            $table->foreignId('campus_id')
                ->nullable()
                ->constrained('campuses')
                ->onDelete('set null');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
