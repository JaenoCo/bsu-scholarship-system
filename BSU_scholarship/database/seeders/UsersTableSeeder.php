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
     * Run the database seeds.
     */
    public function run(): void
    {
        $campuses = Campus::query()->orderBy('name')->get();

        if ($campuses->isEmpty()) {
            $this->command->warn('No campuses found. Skipping student user seeding.');
            return;
        }

        $password = Hash::make('password123');
        $counter = 1;

        foreach ($campuses as $campus) {
            $srCode = sprintf('SR-%06d', 100000 + $counter);
            while (User::where('sr_code', $srCode)->exists()) {
                $counter++;
                $srCode = sprintf('SR-%06d', 100000 + $counter);
            }

            $studentEmail = strtolower($srCode) . '@g.batstate-u.edu.ph';

            $firstName = ['Ariel', 'Bea', 'Cris', 'Dana', 'Ethan', 'Faye'][$counter % 6];
            $lastName = ['Aguilar', 'Bautista', 'Castro', 'Dela Cruz', 'Enriquez', 'Flores'][$counter % 6];
            $middleName = ['M.', 'A.', 'N.', 'R.', 'L.', 'S.'][$counter % 6];

            $departmentName = 'CICS';

            User::updateOrCreate(
                ['email' => $studentEmail],
                [
                    'name' => "$firstName $middleName $lastName",
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'sex' => $counter % 2 === 0 ? 'Female' : 'Male',
                    'birthdate' => now()->subYears(19 + ($counter % 4))->toDateString(),
                    'contact_number' => '09' . str_pad((string) (900000000 + $counter), 9, '0', STR_PAD_LEFT),
                    'sr_code' => $srCode,
                    'education_level' => 'Undergraduate',
                    'program' => 'BS Information Technology',
                    'college' => $departmentName,
                    'year_level' => '3rd Year',
                    'email_verified_at' => now(),
                    'password' => $password,
                    'role' => 'student',
                    'campus_id' => $campus->id,
                ]
            );

            $counter++;
        }

        $this->command->info('Created student accounts for each campus. No applications were seeded.');
    }
}
