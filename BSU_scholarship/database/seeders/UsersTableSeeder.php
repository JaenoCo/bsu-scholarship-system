<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Create only student user accounts for campuses.
     * This seeder intentionally does not create applications, scholars, or related records.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $targetStudentCount = 10;

        // Keep the dataset focused on exactly 10 student users for this seeding run.
        User::where('role', 'student')->delete();

        // Use the first available campus for the generated users.
        $campus = \App\Models\Campus::query()->first();
        if (!$campus) {
            return;
        }

        for ($i = 0; $i < $targetStudentCount; $i++) {
            $studentId = $faker->unique()->numberBetween(100000, 999999);
            $studentEmail = sprintf("99-%06d@g.batstate-u.edu.ph", $studentId);

            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $middleName = $faker->lastName();

            $campusDepartments = $campus->departments;
            $randomDepartment = $campusDepartments->count() > 0
                ? $campusDepartments->random()->short_name
                : 'CICS';

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

        // Admin accounts are seeded separately by AdminSeeder to avoid duplicate role/email entries.
        // This seeder focuses only on creating student users linked to campuses.
    }
}
