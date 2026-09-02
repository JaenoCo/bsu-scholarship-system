<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->decimal('verified_gwa', 4, 2)->nullable()->after('status');
            $table->foreignId('gwa_verified_by')->nullable()->after('verified_gwa')->constrained('users')->nullOnDelete();
            $table->timestamp('gwa_verified_at')->nullable()->after('gwa_verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['gwa_verified_by']);
            $table->dropColumn(['verified_gwa', 'gwa_verified_by', 'gwa_verified_at']);
        });
    }
};
