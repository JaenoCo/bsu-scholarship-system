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
            // Check if student already has applications to avoid duplicates in case of re-seed
            if ($student->applications()->exists()) {
                continue;
            }

            // Reuse the creation timestamp of the student for consistency or generate new?
            // Let's use the student's created_at as a base.
            $baseDate = $student->created_at;
            $updatedAt = $baseDate;

            // Assign STATUS (Adjusted Probabilities)
            // 0.00 - 0.10: Not Applied (10%)
            // 0.10 - 0.35: In Progress (25%) # Applicant
            // 0.35 - 0.65: Pending (30%)     # Applicant
            // 0.65 - 0.75: Rejected (10%)    # Applicant (Rejected)
            // 0.75 - 0.85: Approved (10%)    # Applicant (SFAO Endorsed)
            // 0.85 - 1.00: Scholar (15%)     # Scholar
            
            $statusRoll = $faker->randomFloat(2, 0, 1);
            
            // Not Applied
            if ($statusRoll < 0.10) {
                continue; 
            }

            $scholarship = $scholarships->random();

            // In Progress
            if ($statusRoll < 0.35) {
                Application::create([
                    'user_id' => $student->id,
                    'scholarship_id' => $scholarship->id,
                    'status' => 'in_progress',
                    'grant_count' => 0,
                    'created_at' => $baseDate,
                    'updated_at' => $updatedAt,
                ]);
                continue;
            }

            // Pending
            if ($statusRoll < 0.65) {
                Application::create([
                    'user_id' => $student->id,
                    'scholarship_id' => $scholarship->id,
                    'status' => 'pending',
                    'grant_count' => 0,
                    'created_at' => $baseDate,
                    'updated_at' => $updatedAt,
                ]);
                continue;
            }

            // Rejected
            if ($statusRoll < 0.75) {
                    $app = Application::create([
                    'user_id' => $student->id,
                    'scholarship_id' => $scholarship->id,
                    'status' => 'rejected',
                    'remarks' => 'Did not meet requirements.',
                    'grant_count' => 0,
                    'created_at' => $baseDate,
                    'updated_at' => $updatedAt,
                ]);
                
                // Find a central admin to blame
                $centralAdmin = User::where('role', 'central')->first();
                $rejectedById = $centralAdmin ? $centralAdmin->id : 1;

                RejectedApplicant::create([
                    'user_id' => $student->id,
                    'scholarship_id' => $scholarship->id,
                    'application_id' => $app->id,
                    'rejected_by' => 'central',
                    'rejected_by_user_id' => $rejectedById,
                    'rejected_at' => $baseDate, 
                    'remarks' => 'Did not meet requirements.',
                    'created_at' => $baseDate,
                    'updated_at' => $updatedAt,
                ]);
                continue;
            }

            // Approved (Endorsed Only)
            if ($statusRoll < 0.85) {
                Application::create([
                    'user_id' => $student->id,
                    'scholarship_id' => $scholarship->id,
                    'status' => 'approved',
                    'remarks' => 'SFAO Endorsed - Awaiting Scholar Selection',
                    'grant_count' => 0,
                    'created_at' => $baseDate,
                    'updated_at' => $updatedAt, 
                ]);
                continue;
            }

            // Scholar (Accepted)
            // Application is Approved AND Scholar record exists
            
            $scholarType = $faker->randomElement(['new', 'old']);
            $grantCount = $scholarType === 'new' ? 1 : $faker->numberBetween(2, 5);

            $app = Application::create([
                'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
                'status' => 'approved',
                'remarks' => 'Scholarship Awarded',
                'grant_count' => 1, 
                'created_at' => $baseDate,
                'updated_at' => $updatedAt,
            ]);

            $scholarStatus = $faker->randomElement(['active', 'active', 'inactive', 'suspended', 'completed']);
            
            Scholar::create([
                'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
                'application_id' => $app->id,
                'status' => $scholarStatus,
                'scholarship_start_date' => $scholarType === 'new' ? $baseDate : $baseDate->copy()->subYear(),
                'scholarship_end_date' => $scholarStatus === 'completed' ? $baseDate->copy()->addMonths(5) : null,
                'type' => $scholarType,
                'grant_count' => $grantCount,
                'total_grant_received' => $grantCount * 5000.00,
                'created_at' => $baseDate,
                'updated_at' => $updatedAt,
            ]);

        }
    }
}
