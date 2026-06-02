<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Campus;
use App\Models\StudentProfile;
use App\Models\Form;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('en_PH');
        $password = Hash::make('password123');
        $now = now();

        $constituentCampuses = Campus::where('type', 'constituent')->get();
        $studentCounter = 1;

        foreach ($constituentCampuses as $constituent) {
            // Get constituent + extensions
            $groupCampuses = Campus::where('id', $constituent->id)
                                    ->orWhere('parent_campus_id', $constituent->id)
                                    ->get();
            
            $totalInGroup = 60; // Target number of students per constituent group
            $campusCount = $groupCampuses->count();
            
            // Distribute students across these campuses
            $baseCount = intval($totalInGroup / $campusCount);
            $remainder = $totalInGroup % $campusCount;

            foreach ($groupCampuses as $index => $campus) {
                $countForThisCampus = $baseCount + ($index < $remainder ? 1 : 0);
                
                // Get valid colleges for this campus
                $validColleges = $campus->colleges;

                if ($validColleges->isEmpty()) {
                   $this->command->warn("No colleges found for campus {$campus->name}. Skipping student generation for this campus.");
                   continue;
                }

                for ($i = 0; $i < $countForThisCampus; $i++) {
                    
                    // ACADEMIC YEAR LOGIC
                    $ayYearStart = $faker->randomElement([2023, 2024]);
                    $ayYearEnd = $ayYearStart + 1;
                    
                    $startDate = Carbon::create($ayYearStart, 8, 1, 0, 0, 0);
                    $endDate = Carbon::create($ayYearEnd, 5, 31, 23, 59, 59);
                    
                    $createdAt = Carbon::createFromTimestamp(mt_rand($startDate->timestamp, $endDate->timestamp));
                    $updatedAt = $createdAt->copy(); 

                    // Format: SR-00001
                    // Generate a unique SR Code for this run. 
                    // Note: If seeding on top of existing data, this counter resets and might conflict. 
                    // Ideally we should check max SR code or just rely on random strings, but format requires SR-XXXXX
                    // We will just use the counter + a large offset or check existence if we wanted to be perfectly safe, 
                    // but standard seeding usually assumes a fresh DB or handles truncation. 
                    // For now, I'll stick to the counter but formatted nicely.
                    
                    // To avoid duplicates if re-running without fresh, let's append a random suffix or checks.
                    // But simpler: just generate.
                    $srCode = sprintf("SR-%05d", (User::where('role', 'student')->count() + $studentCounter)); 
                    $email = "{$srCode}@g.batstate-u.edu.ph";
                    
                    // Increment local counter
                    $studentCounter++;

                    // Skip if exists
                    if (User::where('email', $email)->exists()) {
                        continue;
                    }

                    $firstName = $faker->firstName;
                    $lastName = $faker->lastName;
                    $gender = $faker->randomElement(['Male', 'Female']);

                    // Select Random College from Valid List
                    $randomCollege = $validColleges->random();
                    $collegeShortName = $randomCollege->short_name;
                    
                    // Find the Pivot ID
                    $campusCollege = \App\Models\CampusCollege::where('campus_id', $campus->id)
                                        ->where('college_id', $randomCollege->id)
                                        ->first();

                    $availablePrograms = [];
                    if ($campusCollege) {
                        $availablePrograms = \App\Models\Program::where('campus_college_id', $campusCollege->id)->pluck('name')->toArray();
                    }
                    
                    if (empty($availablePrograms)) {
                         $availablePrograms = ["Bachelor of {$collegeShortName}"];
                    }
                    $program = $faker->randomElement($availablePrograms);

                    // Assign Track/Major if available
                    $track = null;
                    if ($campusCollege) {
                         $progModel = \App\Models\Program::where('campus_college_id', $campusCollege->id)
                                        ->where('name', $program)
                                        ->first();
                         if ($progModel) {
                             $tracks = \Illuminate\Support\Facades\DB::table('program_tracks')
                                        ->where('program_id', $progModel->id)
                                        ->pluck('name')
                                        ->toArray();
                             if (!empty($tracks)) {
                                 $track = $faker->randomElement($tracks);
                             }
                         }
                    }

                    $student = User::create([
                        'name' => "{$firstName} {$lastName}",
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'middle_name' => $faker->lastName,
                        'email' => $email,
                        'password' => $password,
                        'role' => 'student',
                        'campus_id' => $campus->id,
                        'email_verified_at' => $createdAt,
                        'sr_code' => $srCode,
                        'birthdate' => $now->copy()->subYears($faker->numberBetween(18, 24))->format('Y-m-d'),
                        'sex' => $gender,
                        'contact_number' => '09' . $faker->numberBetween(100000000, 999999999),
                        'program' => $program,
                        'track' => $track,
                        'college' => $collegeShortName,
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
                }
            }
        }
    }
}
