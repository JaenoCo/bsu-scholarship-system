<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!$this->indexExists('users', 'users_role_name_index')) {
                    $table->index(['role', 'name']);
                }
                if (!$this->indexExists('users', 'users_role_sr_code_index')) {
                    $table->index(['role', 'sr_code']);
                }
                if (!$this->indexExists('users', 'users_role_campus_id_index')) {
                    $table->index(['role', 'campus_id']);
                }
            });
        }

        if (Schema::hasTable('scholarships')) {
            Schema::table('scholarships', function (Blueprint $table) {
                if (!$this->indexExists('scholarships', 'scholarships_scholarship_name_index')) {
                    $table->index('scholarship_name');
                }
                if (!$this->indexExists('scholarships', 'scholarships_scholarship_name_campus_id_index')) {
                    $table->index(['scholarship_name', 'campus_id']);
                }
            });
        }

        if (Schema::hasTable('programs')) {
            Schema::table('programs', function (Blueprint $table) {
                if (!$this->indexExists('programs', 'programs_name_index')) {
                    $table->index('name');
                }
            });
        }

        if (Schema::hasTable('colleges')) {
            Schema::table('colleges', function (Blueprint $table) {
                if (!$this->indexExists('colleges', 'colleges_name_index')) {
                    $table->index('name');
                }
            });
        }

        if (Schema::hasTable('campuses')) {
            Schema::table('campuses', function (Blueprint $table) {
                if (!$this->indexExists('campuses', 'campuses_name_index')) {
                    $table->index('name');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'users_role_name_index')) {
                $table->dropIndex('users_role_name_index');
            }
            if ($this->indexExists('users', 'users_role_sr_code_index')) {
                $table->dropIndex('users_role_sr_code_index');
            }
            if ($this->indexExists('users', 'users_role_campus_id_index')) {
                $table->dropIndex('users_role_campus_id_index');
            }
        });

        Schema::table('scholarships', function (Blueprint $table) {
            if ($this->indexExists('scholarships', 'scholarships_scholarship_name_index')) {
                $table->dropIndex('scholarships_scholarship_name_index');
            }
            if ($this->indexExists('scholarships', 'scholarships_scholarship_name_campus_id_index')) {
                $table->dropIndex('scholarships_scholarship_name_campus_id_index');
            }
        });

        Schema::table('programs', function (Blueprint $table) {
            if ($this->indexExists('programs', 'programs_name_index')) {
                $table->dropIndex('programs_name_index');
            }
        });

        Schema::table('colleges', function (Blueprint $table) {
            if ($this->indexExists('colleges', 'colleges_name_index')) {
                $table->dropIndex('colleges_name_index');
            }
        });

        Schema::table('campuses', function (Blueprint $table) {
            if ($this->indexExists('campuses', 'campuses_name_index')) {
                $table->dropIndex('campuses_name_index');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $indexes = $connection->getDoctrineSchemaManager()->listTableIndexes($table);
        return isset($indexes[strtolower($indexName)]);
    }
};
