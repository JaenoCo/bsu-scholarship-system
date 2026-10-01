<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('claimed_at')->nullable()->after('grant_count');
            $table->string('claim_term', 16)->nullable()->index()->after('claimed_at');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['claim_term']);
            $table->dropColumn(['claimed_at', 'claim_term']);
        });
    }
};
