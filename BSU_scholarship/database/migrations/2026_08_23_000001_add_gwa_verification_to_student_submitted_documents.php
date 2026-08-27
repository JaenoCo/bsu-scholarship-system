<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_submitted_documents', function (Blueprint $table) {
            $table->decimal('declared_gwa', 4, 2)->nullable()->after('description');
            $table->decimal('extracted_gwa', 4, 2)->nullable()->after('declared_gwa');
            $table->decimal('verified_gwa', 4, 2)->nullable()->after('extracted_gwa');
            $table->string('gwa_source')->nullable()->after('verified_gwa');
            $table->unsignedBigInteger('gwa_verified_by')->nullable()->after('gwa_source');
            $table->timestamp('gwa_verified_at')->nullable()->after('gwa_verified_by');

            $table->foreign('gwa_verified_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'document_name', 'verified_gwa'], 'ssd_user_doc_verified_gwa_idx');
        });
    }

    public function down(): void
    {
        Schema::table('student_submitted_documents', function (Blueprint $table) {
            $table->dropForeign(['gwa_verified_by']);
            $table->dropIndex('ssd_user_doc_verified_gwa_idx');
            $table->dropColumn([
                'declared_gwa',
                'extracted_gwa',
                'verified_gwa',
                'gwa_source',
                'gwa_verified_by',
                'gwa_verified_at',
            ]);
        });
    }
};
