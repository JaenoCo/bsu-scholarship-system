<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Scholarship;
use App\Models\Application;

class ApplicationSeeder extends Seeder
{
    public function run()
    {
        $scholarships = Scholarship::all();
        if ($scholarships->isEmpty()) {
            $this->command->warn('No scholarships found. Skipping application generation.');
            return;
        }

        $students = $this->getSeededStudents();
        if ($students->isEmpty()) {
            $this->command->warn('No seeded students found. Skipping application generation.');
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

    private function getSeededStudents()
    {
        return User::query()
            ->where('role', 'student')
            ->where(function ($query) {
                $query->where('sr_code', 'like', 'SR-%')
                    ->orWhere('email', 'like', '99-%@g.batstate-u.edu.ph');
            })
            ->get();
    }
}
