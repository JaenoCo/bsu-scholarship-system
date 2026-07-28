<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\Application;
use App\Models\Scholar;
use App\Models\RejectedApplicant;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ApplicationSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('en_PH');
        
        $scholarships = Scholarship::all();
        if ($scholarships->isEmpty()) {
            $this->command->warn('No scholarships found. Skipping application generation.');
            return;
        }

        $students = User::where('role', 'student')->get();
        if ($students->isEmpty()) {
            $this->command->warn('No students found. Skipping application generation.');
            return;
        }

        foreach ($students as $student) {
            if ($student->applications()->exists()) {
                continue;
            }

            $scholarship = $scholarships->random();
            $createdAt = $student->created_at ?? now();

            Application::create([
                'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
                'status' => 'pending',
                'grant_count' => 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
