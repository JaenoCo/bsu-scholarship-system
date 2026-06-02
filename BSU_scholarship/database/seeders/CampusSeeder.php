<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data - handle foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('campuses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Insert constituent campuses first (use exact names as requested)
        $pabloBorbon = DB::table('campuses')->insertGetId([
            'name' => 'Pablo Borbon',
            'type' => 'constituent',
            'parent_campus_id' => null,
            'has_sfao_admin' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $alangilan = DB::table('campuses')->insertGetId([
            'name' => 'Alangilan',
            'type' => 'constituent',
            'parent_campus_id' => null,
            'has_sfao_admin' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $lipa = DB::table('campuses')->insertGetId([
            'name' => 'Lipa',
            'type' => 'constituent',
            'parent_campus_id' => null,
            'has_sfao_admin' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $nasugbu = DB::table('campuses')->insertGetId([
            'name' => 'ARASOF',
            'type' => 'constituent',
            'parent_campus_id' => null,
            'has_sfao_admin' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $malvar = DB::table('campuses')->insertGetId([
            'name' => 'JPLPC',
            'type' => 'constituent',
            'parent_campus_id' => null,
            'has_sfao_admin' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert extension campuses (use exact names as requested)
        DB::table('campuses')->insert([
            // Pablo Borbon extensions
            ['name' => 'Lemery', 'type' => 'extension', 'parent_campus_id' => $pabloBorbon, 'has_sfao_admin' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rosario', 'type' => 'extension', 'parent_campus_id' => $pabloBorbon, 'has_sfao_admin' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'San Juan', 'type' => 'extension', 'parent_campus_id' => $pabloBorbon, 'has_sfao_admin' => false, 'created_at' => now(), 'updated_at' => now()],

            // Alangilan extensions
            ['name' => 'Lobo', 'type' => 'extension', 'parent_campus_id' => $alangilan, 'has_sfao_admin' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mabini', 'type' => 'extension', 'parent_campus_id' => $alangilan, 'has_sfao_admin' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Balayan', 'type' => 'extension', 'parent_campus_id' => $alangilan, 'has_sfao_admin' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
