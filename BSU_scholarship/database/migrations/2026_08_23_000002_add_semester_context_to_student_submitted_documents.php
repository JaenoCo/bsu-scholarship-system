<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_submitted_documents', function (Blueprint $table) {
            $table->string('academic_year')->nullable()->after('document_name');
            $table->string('semester')->nullable()->after('academic_year');
            $table->index(['user_id', 'scholarship_id', 'academic_year', 'semester'], 'ssd_grades_period_idx');
        });
    }

    public function down(): void
    {
        Schema::table('student_submitted_documents', function (Blueprint $table) {
            $table->dropIndex('ssd_grades_period_idx');
            $table->dropColumn(['academic_year', 'semester']);
        });
    }
};