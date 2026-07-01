<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            if (!Schema::hasColumn('scholarships', 'announcement_title')) {
                $table->string('announcement_title')->nullable()->after('background_image');
            }
            if (!Schema::hasColumn('scholarships', 'announcement_message')) {
                $table->text('announcement_message')->nullable()->after('announcement_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            if (Schema::hasColumn('scholarships', 'announcement_message')) {
                $table->dropColumn('announcement_message');
            }
            if (Schema::hasColumn('scholarships', 'announcement_title')) {
                $table->dropColumn('announcement_title');
            }
        });
    }
};
