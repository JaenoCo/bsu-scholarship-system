<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        
        // Get all constituent campuses and their extensions
        $constituentCampuses = \App\Models\Campus::constituent()->with(['extensionCampuses'])->get();
        
        // Create 20 student users for each constituent campus and its extensions
        foreach ($constituentCampuses as $constituent) {
            // Get all campuses under this constituent (constituent + extensions)
            $allCampuses = $constituent->getAllCampusesUnder();
            
            // Calculate how many students per campus to get 20 total
            $totalCampuses = $allCampuses->count();
            $studentsPerCampus = $totalCampuses > 0 ? intval(20 / $totalCampuses) : 0;
            $remainingStudents = 20 % $totalCampuses;
            
            $studentCount = 0;
            foreach ($allCampuses as $index => $campus) {
                // Calculate students for this campus
                $studentsForThisCampus = $studentsPerCampus;
                if ($index < $remainingStudents) {
                    $studentsForThisCampus += 1; // Distribute remaining students
                }
                
                // Create students for this campus
                for ($i = 0; $i < $studentsForThisCampus; $i++) {
                    $studentCount++;
                    // Use 99-xxxxxx format to avoid conflicts with actual G Suite accounts
                    $studentId = $faker->unique()->numberBetween(100000, 999999);
                    $studentEmail = sprintf("99-%06d@g.batstate-u.edu.ph", $studentId);
                    
                    $firstName = $faker->firstName();
                    $lastName = $faker->lastName();
                    $middleName = $faker->lastName();

                    // Get valid departments for this campus
                    $campusDepartments = $campus->departments;
                    $randomDepartment = $campusDepartments->count() > 0 
                        ? $campusDepartments->random()->short_name 
                        : 'CICS'; // Fallback
                    
                    User::create([
                        'name' => "$firstName $middleName $lastName",
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'sex' => $faker->randomElement(['Male', 'Female']),
                        'birthdate' => $faker->date(),
                        'contact_number' => $faker->phoneNumber(),
                        'sr_code' => 'SR-' . $studentId,
                        'education_level' => 'Undergraduate',
                        'program' => 'BS Information Technology',
                        'college' => $randomDepartment,
                        'year_level' => '3rd Year',
                        'email' => $studentEmail,
                        'email_verified_at' => now(),
                        'password' => Hash::make('password123'),
                        'role' => 'student',
                        'campus_id' => $campus->id,
                    ]);
                }
            }
        }

        // Create 5 SFAO Admins (one for each constituent campus)
        $sfaoCampuses = ['Pablo Borbon', 'Alangilan', 'Lipa', 'ARASOF', 'JPLPC'];
        foreach ($sfaoCampuses as $index => $campusName) {
            $campus = \App\Models\Campus::where('name', $campusName)->first();
            if ($campus) {
                User::updateOrCreate(
                    ['email' => "sfao-" . ($index + 1) . "@g.batstate-u.edu.ph"],
                    [
                        'name' => "SFAO Admin - {$campusName}",
                        'first_name' => 'SFAO',
                        'last_name' => 'Admin',
                        'sex' => 'Male',
                        'contact_number' => '09123456789',
                        'sr_code' => 'SFAO-' . ($index + 1),
                        'password' => Hash::make('password123'),
                        'role' => 'sfao',
                        'campus_id' => $campus->id,
                        'email_verified_at' => now(),
                    ]
                );
            }
        }

        // Create 1 Central Admin
        User::updateOrCreate(
            ['email' => 'central-admin@g.batstate-u.edu.ph'],
            [
                'name' => 'Central Admin',
                'first_name' => 'Central',
                'last_name' => 'Admin',
                'sex' => 'Male',
                'contact_number' => '09123456789',
                'sr_code' => 'CENTRAL-ADMIN',
                'password' => Hash::make('password123'),
                'role' => 'central',
                'campus_id' => 1, // Pablo Borbon campus
                'email_verified_at' => now(),
            ]
        );
    }
}
