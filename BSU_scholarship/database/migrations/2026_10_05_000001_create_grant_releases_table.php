<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grant_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scholar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('released_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('grant_number');
            $table->decimal('amount', 10, 2);
            $table->string('tracking_number')->nullable()->unique();
            $table->text('qr_code')->nullable();
            $table->string('status')->default('released');
            $table->timestamp('released_at');
            $table->timestamp('email_sent_at')->nullable();
            $table->text('email_error')->nullable();
            $table->foreignId('notification_id')->nullable()->unique()->constrained('notifications')->nullOnDelete();
            $table->timestamps();

            $table->unique(['application_id', 'grant_number']);
            $table->index(['scholarship_id', 'released_at']);
            $table->index(['user_id', 'released_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grant_releases');
    }
};
