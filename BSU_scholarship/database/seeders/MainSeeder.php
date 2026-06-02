<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Campus;
use App\Models\Scholarship;
use App\Models\Scholar;
use App\Models\Application;
use App\Models\StudentProfile;
use App\Models\RejectedApplicant;
use App\Models\Form;
use Faker\Factory as Faker;
use Carbon\Carbon;

class MainSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('en_PH'); 
        $password = Hash::make('password123');
        $now = now();

        // =================================================================
        // 1. CENTRAL ADMIN
        // =================================================================
        $pabloBorbon = Campus::where('name', 'Pablo Borbon')->first(); 
        
        User::create([
            'name' => 'Central Admin',
            'first_name' => 'Central',
            'last_name' => 'Admin',
            'email' => 'test.central-admin@g.batstate-u.edu.ph',
            'password' => $password,
            'role' => 'central',
            'campus_id' => $pabloBorbon ? $pabloBorbon->id : 1, // Default to PB or 1
            'email_verified_at' => $now,
            'sr_code' => 'CENTRAL-ADMIN', // Placeholder
            'birthdate' => $now->copy()->subYears(35)->format('Y-m-d'),
            'sex' => 'Male',
            'contact_number' => '09123456789',
        ]);
        
        $this->command->info('Created Central Admin: test.central-admin@g.batstate-u.edu.ph');

        // =================================================================
        // 2. SFAO ADMINS (One per Constituent Campus)
        // =================================================================
        $constituentCampuses = Campus::where('type', 'constituent')->get();

        foreach ($constituentCampuses as $campus) {
             // Generate slug for email: "Pablo Borbon" -> "pablo-borbon"
             // But user request specifically said: "test.sfao-/campus name/@..."
             // Assuming "test.sfao-alangilan" for Alangilan, "test.sfao-pablo-borbon" etc.
             $slug = \Illuminate\Support\Str::slug($campus->name);
             $email = "test.sfao-{$slug}@g.batstate-u.edu.ph";
             
             User::create([
                'name' => "SFAO Admin {$campus->name}",
                'first_name' => 'SFAO',
                'last_name' => "Admin {$campus->name}",
                'email' => $email,
                'password' => $password,
                'role' => 'sfao',
                'campus_id' => $campus->id,
                'email_verified_at' => $now,
                'sr_code' => 'SFAO-' . strtoupper($slug),
                'birthdate' => $now->copy()->subYears($faker->numberBetween(25, 50))->format('Y-m-d'),
                'sex' => $faker->randomElement(['Male', 'Female']),
                'contact_number' => '09' . $faker->numberBetween(100000000, 999999999),
             ]);
             
             $this->command->info("Created SFAO Admin: $email");
        }

        // =================================================================
        // 3. STUDENTS (30 per Constituent Group)
        // =================================================================
        
        // Get all scholarships for applications
        $scholarships = Scholarship::all();
        if ($scholarships->isEmpty()) {
            $this->command->warn('No scholarships found. Running ScholarshipsTableSeeder...');
            $this->call(ScholarshipsTableSeeder::class);
            $scholarships = Scholarship::all();
        }

        $studentCounter = 1;

        foreach ($constituentCampuses as $constituent) {
            // Get constituent + extensions
            $groupCampuses = Campus::where('id', $constituent->id)
                                    ->orWhere('parent_campus_id', $constituent->id)
                                    ->get();
            
            $totalInGroup = 60;
            $campusCount = $groupCampuses->count();
            
            // Distribute 30 students across these campuses
            // Simple logic: 30 / count. Remainder distributed.
            $baseCount = intval($totalInGroup / $campusCount);
            $remainder = $totalInGroup % $campusCount;

            foreach ($groupCampuses as $index => $campus) {
                $countForThisCampus = $baseCount + ($index < $remainder ? 1 : 0);
                
                // Get valid departments for this campus
                $validDepartments = $campus->departments;

                // Fallback if no departments (shouldn't happen with correct seeding, but safe)
                if ($validDepartments->isEmpty()) {
                   $this->command->warn("No departments found for campus {$campus->name}. Skipping student generation.");
                   continue;
                }

                // Program Mapping (Same as Controller)
                // ... (Same mapping as before, omitted for brevity if unchanged, but Replace tool needs exact context so I will keep it clean or just target the loop)
                // Actually, let's just replace the INSIDE of the student loop to handle the dates.
                
                // Let's redefine the loop content:
                for ($i = 0; $i < $countForThisCampus; $i++) {
                    
                    // ACADEMIC YEAR LOGIC
                    // We want to simulate data for: 
                    // AY 2025-2026 (Current)
                    // AY 2024-2025 (Previous)
                    // AY 2023-2024 (Legacy)
                    
                    $ayYearStart = $faker->randomElement([2023, 2024]); // Removed 2025 to avoid future dates (2025-2026)
                    $ayYearEnd = $ayYearStart + 1;
                    
                    // Strict Range: August 1st of YearStart to May 31st of YearEnd
                    $startDate = Carbon::create($ayYearStart, 8, 1, 0, 0, 0);
                    $endDate = Carbon::create($ayYearEnd, 5, 31, 23, 59, 59);
                    
                    // Random CreatedAt within this range
                    $createdAt = Carbon::createFromTimestamp(mt_rand($startDate->timestamp, $endDate->timestamp));
                    $updatedAt = $createdAt->copy(); // Assume updated same time for simplicity

                    // Format: SR-00001
                    $srCode = sprintf("SR-%05d", $studentCounter);
                    $email = "{$srCode}@g.batstate-u.edu.ph";
                    $studentCounter++;

                    $firstName = $faker->firstName;
                    $lastName = $faker->lastName;
                    $gender = $faker->randomElement(['Male', 'Female']);

                    // Select Random Department from Valid List
                    $randomDept = $validDepartments->random();
                    $deptShortName = $randomDept->short_name;
                    
                    // Select Random Program from DB
                    // Query programs for this department (college)
                    // Using Full Name as requested
                    $availablePrograms = \App\Models\Program::where('college', $deptShortName)->pluck('name')->toArray();
                    
                    if (empty($availablePrograms)) {
                         // Fallback
                         $availablePrograms = ["Bachelor of {$deptShortName}"];
                    }
                    $program = $faker->randomElement($availablePrograms);

                    $student = User::create([
                        'name' => "{$firstName} {$lastName}",
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'middle_name' => $faker->lastName,
                        'email' => $email,
                        'password' => $password,
                        'role' => 'student',
                        'campus_id' => $campus->id,
                        'email_verified_at' => $createdAt, // Verified at creation
                        'sr_code' => $srCode,
                        'birthdate' => $now->copy()->subYears($faker->numberBetween(18, 24))->format('Y-m-d'),
                        'sex' => $gender,
                        'contact_number' => '09' . $faker->numberBetween(100000000, 999999999),
                        'program' => $program,
                        'college' => $deptShortName,
                        'year_level' => $faker->randomElement(['1st Year', '2nd Year', '3rd Year', '4th Year']),
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);

                    // Create Student Profile
                    StudentProfile::create([
                        'user_id' => $student->id,
                        'street' => $faker->streetAddress,
                        'barangay' => $faker->word,
                        'town' => $faker->city,
                        'province' => $faker->state,
                        'zip_code' => $faker->postcode,
                        'gwa' => $faker->randomFloat(2, 1.0, 2.5),
                        'units_enrolled' => 21,
                        'father_name' => $faker->name('Male'),
                        'mother_name' => $faker->name('Female'),
                        'father_occupation' => $faker->jobTitle,
                        'mother_occupation' => $faker->jobTitle,
                        'annual_gross_income' => $faker->numberBetween(100000, 500000),
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);

                    // Create Form (Required for Reports)
                    Form::create([
                        'user_id' => $student->id,
                        'units_enrolled' => 21,
                        'town_city' => $faker->city,
                        'province' => $faker->state,
                        'disability' => $faker->randomElement(['None', 'None', 'None', 'Visual', 'Hearing', 'Mobility']),
                        'zip_code' => $faker->postcode,
                        'street_barangay' => $faker->streetName,
                        'citizenship' => 'Filipino',
                        'father_name' => $faker->name('Male'),
                        'mother_name' => $faker->name('Female'),
                        'father_occupation' => $faker->jobTitle,
                        'mother_occupation' => $faker->jobTitle,
                        'estimated_gross_annual_income' => $faker->numberBetween(100000, 500000),
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);

                    // Assign STATUS (Random)
                    // 0.00 - 0.15: Not Applied
                    // 0.15 - 0.30: In Progress
                    // 0.30 - 0.45: Pending
                    // 0.45 - 0.55: Rejected
                    // 0.55 - 0.75: Approved (SFAO Endorsed ONLY - Not yet a Scholar)
                    // 0.75 - 1.00: Scholar (Central Accepted - Has Scholar Record)
                    
                    $statusRoll = $faker->randomFloat(2, 0, 1);
                    
                    if ($statusRoll < 0.15) {
                        continue; // Not Applied
                    }

                    $scholarship = $scholarships->random();

                    // In Progress
                    if ($statusRoll < 0.30) {
                        Application::create([
                            'user_id' => $student->id,
                            'scholarship_id' => $scholarship->id,
                            'status' => 'in_progress',
                            'grant_count' => 0,
                            'created_at' => $createdAt,
                            'updated_at' => $updatedAt,
                        ]);
                        continue;
                    }

                    // Pending
                    if ($statusRoll < 0.45) {
                        Application::create([
                            'user_id' => $student->id,
                            'scholarship_id' => $scholarship->id,
                            'status' => 'pending',
                            'grant_count' => 0,
                            'created_at' => $createdAt,
                            'updated_at' => $updatedAt,
                        ]);
                        continue;
                    }

                    // Rejected
                    if ($statusRoll < 0.55) {
                         $app = Application::create([
                            'user_id' => $student->id,
                            'scholarship_id' => $scholarship->id,
                            'status' => 'rejected',
                            'remarks' => 'Did not meet requirements.',
                            'grant_count' => 0,
                            'created_at' => $createdAt,
                            'updated_at' => $updatedAt,
                        ]);
                        
                        RejectedApplicant::create([
                            'user_id' => $student->id,
                            'scholarship_id' => $scholarship->id,
                            'application_id' => $app->id,
                            'rejected_by' => 'sfao',
                            'rejected_by_user_id' => 1,
                            'rejected_at' => $createdAt, // Rejected same day
                            'remarks' => 'Did not meet requirements.',
                            'created_at' => $createdAt,
                            'updated_at' => $updatedAt,
                        ]);
                        continue;
                    }

                    // Approved (Endorsed Only)
                    if ($statusRoll < 0.75) {
                        Application::create([
                            'user_id' => $student->id,
                            'scholarship_id' => $scholarship->id,
                            'status' => 'approved',
                            'remarks' => $faker->randomElement(['Completes all requirements', 'SFAO Verified', 'Endorsed for approval', 'Good standing', 'Documents valid']),
                            'grant_count' => 0,
                            'created_at' => $createdAt,
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
                        'remarks' => $faker->randomElement(['Scholarship Awarded', 'Qualified for Grant', 'Excellent academic performance', 'Requirements complete']),
                        'grant_count' => 1, // Current application is always count 1 or just the latest
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);

                    $scholarStatus = $faker->randomElement(['active', 'active', 'inactive', 'suspended', 'completed']);
                    
                    Scholar::create([
                        'user_id' => $student->id,
                        'scholarship_id' => $scholarship->id,
                        'application_id' => $app->id,
                        'status' => $scholarStatus,
                        'scholarship_start_date' => $scholarType === 'new' ? $createdAt : $createdAt->copy()->subYear(), // If old, started earlier
                        'scholarship_end_date' => $scholarStatus === 'completed' ? $createdAt->copy()->addMonths(5) : null,
                        'type' => $scholarType,
                        'grant_count' => $grantCount,
                        'total_grant_received' => $grantCount * 5000.00, // Mock amount
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ]);

                } // End Student Loop
            } // End Campus Loop
        } // End Constituent Loop
        
        $this->command->info("Seeding Completed. Created {$studentCounter} students.");
    }
}
