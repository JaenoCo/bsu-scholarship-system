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
            CollegeSeeder::class,
            CampusCollegeSeeder::class,
            ProgramSeeder::class,
            ProgramTrackSeeder::class,

            // 2. Admins
            AdminSeeder::class,

            // 3. Scholarships (Depends on Admin)
            ScholarshipsTableSeeder::class,

            // 4. Students (Depends on structure data)
            StudentSeeder::class,

            // 5. Applications
            ApplicationSeeder::class,

            // 6. Notifications
            NotificationSeeder::class,
        ]);
    }
}