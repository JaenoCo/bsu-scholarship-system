<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Create only student user accounts for campuses.
     * This seeder intentionally does not create applications, scholars, or related records.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $targetStudentCount = 550;

        $campus = \App\Models\Campus::query()->first();
        if (!$campus) {
            return;
        }

        $existingStudentEmails = User::where('role', 'student')->pluck('email')->all();

        for ($i = 0; $i < $targetStudentCount; $i++) {
            $studentId = $faker->unique()->numberBetween(100000, 999999);
            $studentEmail = sprintf("99-%06d@g.batstate-u.edu.ph", $studentId);

            while (in_array($studentEmail, $existingStudentEmails, true)) {
                $studentId = $faker->unique()->numberBetween(100000, 999999);
                $studentEmail = sprintf("99-%06d@g.batstate-u.edu.ph", $studentId);
            }

            $existingStudentEmails[] = $studentEmail;

            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $middleName = $faker->lastName();

            $campusDepartments = $campus->departments;
            $randomDepartment = ($campusDepartments && $campusDepartments->count() > 0)
                ? $campusDepartments->random()->short_name
                : 'CICS';

            User::firstOrCreate(
                ['email' => $studentEmail],
                [
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
                ]
            );
        }

        // Admin accounts are seeded separately by AdminSeeder to avoid duplicate role/email entries.
        // This seeder focuses only on creating student users linked to campuses.
    }
}
