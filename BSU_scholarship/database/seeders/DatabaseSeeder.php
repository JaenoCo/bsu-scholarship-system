<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Structure / Static Data
            CampusSeeder::class,
            DepartmentSeeder::class,
            CollegeSeeder::class,
            CampusDepartmentSeeder::class,
            CampusCollegeSeeder::class,
            ProgramSeeder::class,
            ProgramTrackSeeder::class,

            // 2. Seed core records without applications or scholars
            AdminSeeder::class,
            UsersTableSeeder::class,
            ScholarshipsTableSeeder::class,
        ]);
    }
}