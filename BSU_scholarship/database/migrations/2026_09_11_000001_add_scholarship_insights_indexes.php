<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'applications_status_created_at_index');
            $table->index(['scholarship_id', 'status', 'created_at'], 'applications_insights_program_status_date_index');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->index(['campus_id', 'college', 'program'], 'users_insights_scope_index');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('applications_status_created_at_index');
            $table->dropIndex('applications_insights_program_status_date_index');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_insights_scope_index');
        });
    }
};
