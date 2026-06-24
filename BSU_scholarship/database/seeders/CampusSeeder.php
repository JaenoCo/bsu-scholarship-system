<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        // Constituent campuses
        DB::table('campuses')->updateOrInsert(
            ['name' => 'Pablo Borbon'],
            [
                'type' => 'constituent',
                'parent_campus_id' => null,
                'has_sfao_admin' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('campuses')->updateOrInsert(
            ['name' => 'Alangilan'],
            [
                'type' => 'constituent',
                'parent_campus_id' => null,
                'has_sfao_admin' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('campuses')->updateOrInsert(
            ['name' => 'Lipa'],
            [
                'type' => 'constituent',
                'parent_campus_id' => null,
                'has_sfao_admin' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('campuses')->updateOrInsert(
            ['name' => 'ARASOF'],
            [
                'type' => 'constituent',
                'parent_campus_id' => null,
                'has_sfao_admin' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('campuses')->updateOrInsert(
            ['name' => 'JPLPC'],
            [
                'type' => 'constituent',
                'parent_campus_id' => null,
                'has_sfao_admin' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        // Get IDs after insert/update
        $pabloBorbon = DB::table('campuses')->where('name', 'Pablo Borbon')->value('id');
        $alangilan = DB::table('campuses')->where('name', 'Alangilan')->value('id');

        // Extension campuses
        $extensions = [
            ['name' => 'Lemery', 'parent_campus_id' => $pabloBorbon],
            ['name' => 'Rosario', 'parent_campus_id' => $pabloBorbon],
            ['name' => 'San Juan', 'parent_campus_id' => $pabloBorbon],
            ['name' => 'Lobo', 'parent_campus_id' => $alangilan],
            ['name' => 'Mabini', 'parent_campus_id' => $alangilan],
            ['name' => 'Balayan', 'parent_campus_id' => $alangilan],
        ];

        foreach ($extensions as $campus) {
            DB::table('campuses')->updateOrInsert(
                ['name' => $campus['name']],
                [
                    'type' => 'extension',
                    'parent_campus_id' => $campus['parent_campus_id'],
                    'has_sfao_admin' => false,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}