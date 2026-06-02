<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
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

            // 4. Students (Depends on Campuses/Programs)
            StudentSeeder::class,

            // 5. Applications (Depends on Students & Scholarships)
            ApplicationSeeder::class,

            // 6. Notifications (Depends on Users)
            NotificationSeeder::class,
        ]);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
