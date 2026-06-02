<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Campus;
use App\Models\Scholarship;
use App\Models\Application;
use App\Models\Report;
use App\Models\Form;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class ComprehensiveSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // Get all campuses and scholarships
        $campuses = Campus::all();
        $scholarships = Scholarship::all();
        
        if ($campuses->isEmpty() || $scholarships->isEmpty()) {
            return;
        }
        
        // Create diverse student population with realistic scenarios
        $this->createComprehensiveStudentData($campuses, $scholarships, $faker);
        
        // Create realistic application workflows
        $this->createApplicationWorkflows($campuses, $scholarships, $faker);
        
        // Create comprehensive forms with realistic data
        $this->createComprehensiveForms($faker);
        
        // Create realistic notification system
        $this->createNotificationSystem($faker);
        
        // Create diverse reporting data
        $this->createReportingSystem($campuses, $faker);
    }
    
    private function createComprehensiveStudentData($campuses, $scholarships, $faker)
    {
        
        $programs = [
            'Bachelor of Science in Computer Engineering',
            'Bachelor of Science in Civil Engineering', 
            'Bachelor of Science in Electrical Engineering',
            'Bachelor of Science in Mechanical Engineering',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Business Administration',
            'Bachelor of Science in Education',
            'Bachelor of Science in Nursing',
            'Bachelor of Science in Psychology',
            'Bachelor of Science in Accountancy',
            'Bachelor of Science in Architecture',
            'Bachelor of Science in Agriculture',
            'Bachelor of Science in Biology',
            'Bachelor of Science in Chemistry',
            'Bachelor of Science in Mathematics',
            'Bachelor of Science in Physics',
            'Bachelor of Science in Statistics',
            'Bachelor of Science in Tourism',
            'Bachelor of Science in Hospitality Management',
            'Bachelor of Science in Social Work'
        ];
        
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];
        $genders = ['male', 'female'];
        
        foreach ($campuses as $campus) {
            // Create different student profiles
            $this->createStudentProfiles($campus, $programs, $yearLevels, $genders, $faker);
        }
    }
    
    private function createStudentProfiles($campus, $programs, $yearLevels, $genders, $faker)
    {
        $studentProfiles = [
            'academic_excellence' => 15,    // High-performing students
            'average_students' => 25,       // Regular students
            'struggling_students' => 8,      // Students with academic challenges
            'working_students' => 12,       // Students who work part-time
            'athletes' => 6,                // Student athletes
            'leaders' => 8,                 // Student leaders
            'international' => 4,           // International students
            'transfer_students' => 6,       // Transfer students
            'first_generation' => 10,       // First-generation college students
            'financially_challenged' => 15, // Students with financial needs
        ];
        
        $totalStudents = array_sum($studentProfiles);
        $studentCount = 0;
        
        foreach ($studentProfiles as $profile => $count) {
            for ($i = 0; $i < $count; $i++) {
                $studentCount++;
                $this->createStudentByProfile($campus, $profile, $programs, $yearLevels, $genders, $faker, $studentCount);
            }
        }
    }
    
    private function createStudentByProfile($campus, $profile, $programs, $yearLevels, $genders, $faker, $studentNumber)
    {
        $gender = $genders[array_rand($genders)];
        $program = $programs[array_rand($programs)];
        $yearLevel = $yearLevels[array_rand($yearLevels)];
        
        // Generate realistic student ID and email
        $studentId = $this->generateStudentIdByProfile($profile, $studentNumber);
        $email = $this->generateStudentEmail($studentId, $campus);
        
        // Check if student already exists
        $existingStudent = User::where('email', $email)->first();
        if ($existingStudent) {
            return $existingStudent;
        }
        
        // Create student with profile-specific attributes
        $studentData = $this->getStudentDataByProfile($profile, $gender, $program, $yearLevel, $campus, $studentId, $faker);
        
        $student = User::create($studentData);
        
        // Add profile-specific attributes
        $this->addProfileAttributes($student, $profile, $faker);
        
        return $student;
    }
    
    private function generateStudentIdByProfile($profile, $studentNumber)
    {
        $year = date('Y');
        $prefix = match($profile) {
            'academic_excellence' => '24',
            'average_students' => '23',
            'struggling_students' => '22',
            'working_students' => '23',
            'athletes' => '24',
            'leaders' => '23',
            'international' => '24',
            'transfer_students' => '23',
            'first_generation' => '24',
            'financially_challenged' => '23',
            default => '24'
        };
        
        return $prefix . str_pad($studentNumber, 6, '0', STR_PAD_LEFT);
    }
    
    private function generateStudentEmail($studentId, $campus)
    {
        $campusCode = strtolower(str_replace([' ', '(', ')', '–'], ['', '', '', ''], $campus->name));
        return "{$studentId}@g.batstate-u.edu.ph";
    }
    
    private function getStudentDataByProfile($profile, $gender, $program, $yearLevel, $campus, $studentId, $faker)
    {
        $baseData = [
            'name' => $this->generateRealisticName($gender),
            'email' => $this->generateStudentEmail($studentId, $campus),
            'password' => Hash::make('password'),
            'role' => 'student',
            'campus_id' => $campus->id,
        ];
        
        return match($profile) {
            'academic_excellence' => array_merge($baseData, [
                'email_verified_at' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(30, 90)),
            ]),
            'average_students' => array_merge($baseData, [
                'email_verified_at' => now()->subMonths(rand(6, 12)),
                'created_at' => now()->subMonths(rand(12, 18)),
            ]),
            'struggling_students' => array_merge($baseData, [
                'email_verified_at' => now()->subMonths(rand(12, 24)),
                'created_at' => now()->subMonths(rand(18, 30)),
            ]),
            'working_students' => array_merge($baseData, [
                'email_verified_at' => now()->subMonths(rand(6, 18)),
                'created_at' => now()->subMonths(rand(12, 24)),
            ]),
            'athletes' => array_merge($baseData, [
                'email_verified_at' => now()->subDays(rand(1, 60)),
                'created_at' => now()->subDays(rand(60, 180)),
            ]),
            'leaders' => array_merge($baseData, [
                'email_verified_at' => now()->subMonths(rand(6, 24)),
                'created_at' => now()->subMonths(rand(12, 30)),
            ]),
            'international' => array_merge($baseData, [
                'email_verified_at' => now()->subDays(rand(1, 90)),
                'created_at' => now()->subDays(rand(90, 365)),
            ]),
            'transfer_students' => array_merge($baseData, [
                'email_verified_at' => now()->subDays(rand(1, 60)),
                'created_at' => now()->subDays(rand(60, 180)),
            ]),
            'first_generation' => array_merge($baseData, [
                'email_verified_at' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(30, 90)),
            ]),
            'financially_challenged' => array_merge($baseData, [
                'email_verified_at' => now()->subMonths(rand(6, 18)),
                'created_at' => now()->subMonths(rand(12, 24)),
            ]),
            default => array_merge($baseData, [
                'email_verified_at' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(30, 90)),
            ])
        };
    }
    
    private function addProfileAttributes($student, $profile, $faker)
    {
        // Add profile picture for some students
        if (rand(0, 3) === 0) { // 25% chance
            $student->update(['profile_picture' => 'default-avatar-' . rand(1, 5) . '.png']);
        }
    }
    
    private function generateRealisticName($gender)
    {
        $maleNames = [
            'Juan Carlos', 'Miguel Santos', 'Andres Cruz', 'Jose Maria', 'Carlos Rodriguez',
            'Antonio Lopez', 'Francisco Garcia', 'Manuel Torres', 'Rafael Martinez', 'Diego Herrera',
            'Sebastian Reyes', 'Gabriel Morales', 'Daniel Vargas', 'Alejandro Jimenez', 'Fernando Castillo',
            'Roberto Silva', 'Eduardo Moreno', 'Sergio Ramos', 'Luis Fernandez', 'Ricardo Gutierrez',
            'Alejandro Cruz', 'Fernando Santos', 'Roberto Garcia', 'Eduardo Lopez', 'Sergio Martinez',
            'Luis Rodriguez', 'Ricardo Torres', 'Alejandro Flores', 'Fernando Reyes', 'Roberto Morales'
        ];
        
        $femaleNames = [
            'Maria Elena', 'Ana Sofia', 'Carmen Rosa', 'Isabella Grace', 'Valentina Rose',
            'Gabriela Marie', 'Camila Faith', 'Sofia Grace', 'Valeria Hope', 'Natalia Joy',
            'Alejandra Faith', 'Isabella Rose', 'Gabriela Hope', 'Valentina Grace', 'Sofia Marie',
            'Camila Rose', 'Valeria Faith', 'Natalia Grace', 'Alejandra Joy', 'Isabella Marie',
            'Maria Sofia', 'Ana Grace', 'Carmen Hope', 'Isabella Faith', 'Valentina Joy',
            'Gabriela Rose', 'Camila Grace', 'Sofia Hope', 'Valeria Faith', 'Natalia Rose'
        ];
        
        $lastNames = [
            'Santos', 'Cruz', 'Garcia', 'Rodriguez', 'Lopez', 'Martinez', 'Gonzalez', 'Perez', 'Sanchez', 'Ramirez',
            'Torres', 'Flores', 'Rivera', 'Gomez', 'Diaz', 'Reyes', 'Morales', 'Jimenez', 'Herrera', 'Moreno',
            'Castillo', 'Ramos', 'Silva', 'Vargas', 'Fernandez', 'Gutierrez', 'Mendoza', 'Aguilar', 'Vega', 'Rojas'
        ];
        
        $firstNames = $gender === 'male' ? $maleNames : $femaleNames;
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        
        return $firstName . ' ' . $lastName;
    }
    
    private function createApplicationWorkflows($campuses, $scholarships, $faker)
    {
        foreach ($campuses as $campus) {
            $students = User::where('campus_id', $campus->id)
                          ->where('role', 'student')
                          ->get();
            
            foreach ($students as $index => $student) {
                $this->createStudentApplicationWorkflow($student, $scholarships, $faker, $index);
            }
        }
    }
    
    private function createStudentApplicationWorkflow($student, $scholarships, $faker, $studentIndex)
    {
        // Different application workflows based on student index
        $workflows = [
            'no_applications' => 0.10,           // 10% haven't applied
            'single_pending' => 0.20,            // 20% have one pending application
            'multiple_pending' => 0.15,          // 15% have multiple pending applications
            'approved_scholar' => 0.15,          // 15% are approved scholars
            'rejected_applicant' => 0.10,        // 10% were rejected
            'mixed_status' => 0.15,              // 15% have mixed status applications
            'continuing_scholar' => 0.10,        // 10% are continuing scholars
            'recent_applicant' => 0.05,          // 5% are recent applicants
        ];
        
        $workflow = $this->selectScenario($workflows);
        
        switch ($workflow) {
            case 'no_applications':
                // Student hasn't applied to any scholarships
                break;
                
            case 'single_pending':
                $this->createSingleApplication($student, $scholarships, 'pending', $faker);
                break;
                
            case 'multiple_pending':
                $this->createMultipleApplications($student, $scholarships, ['pending'], $faker);
                break;
                
            case 'approved_scholar':
                $this->createSingleApplication($student, $scholarships, 'approved', $faker);
                break;
                
            case 'rejected_applicant':
                $this->createSingleApplication($student, $scholarships, 'rejected', $faker);
                break;
                
            case 'mixed_status':
                $this->createMultipleApplications($student, $scholarships, ['approved', 'pending', 'rejected'], $faker);
                break;
                
            case 'continuing_scholar':
                $this->createContinuingScholarApplications($student, $scholarships, $faker);
                break;
                
            case 'recent_applicant':
                $this->createRecentApplication($student, $scholarships, $faker);
                break;
        }
    }
    
    private function selectScenario($scenarios)
    {
        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0;
        
        foreach ($scenarios as $scenario => $probability) {
            $cumulative += $probability;
            if ($rand <= $cumulative) {
                return $scenario;
            }
        }
        
        return 'no_applications';
    }
    
    private function createSingleApplication($student, $scholarships, $status, $faker)
    {
        $scholarship = $scholarships->random();
        
        $applicationData = [
            'user_id' => $student->id,
            'scholarship_id' => $scholarship->id,
            'type' => 'new',
            'status' => $status,
            'grant_count' => $status === 'approved' ? rand(1, 2) : 0,
        ];
        
        // Set realistic timeline based on status
        $this->setApplicationTimeline($applicationData, $status, $faker);
        
        Application::create($applicationData);
    }
    
    private function createMultipleApplications($student, $scholarships, $statuses, $faker)
    {
        $applicationCount = rand(2, 4);
        $selectedScholarships = $scholarships->random($applicationCount);
        
        foreach ($selectedScholarships as $index => $scholarship) {
            $status = $statuses[$index % count($statuses)];
            
            $applicationData = [
                'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
                'type' => 'new',
                'status' => $status,
                'grant_count' => $status === 'approved' ? rand(1, 2) : 0,
            ];
            
            $this->setApplicationTimeline($applicationData, $status, $faker);
            
            Application::create($applicationData);
        }
    }
    
    private function createContinuingScholarApplications($student, $scholarships, $faker)
    {
        // Create a previous approved application
        $scholarship = $scholarships->random();
        
        $previousApplication = [
            'user_id' => $student->id,
            'scholarship_id' => $scholarship->id,
            'type' => 'new',
            'status' => 'approved',
            'grant_count' => rand(2, 4),
            'created_at' => now()->subMonths(rand(6, 18)),
            'updated_at' => now()->subMonths(rand(1, 6)),
        ];
        
        Application::create($previousApplication);
        
        // Create current continuing application
        $currentApplication = [
            'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
            'type' => 'continuing',
            'status' => 'pending',
            'grant_count' => 0,
            'created_at' => now()->subDays(rand(1, 30)),
        ];
        
        Application::create($currentApplication);
    }
    
    private function createRecentApplication($student, $scholarships, $faker)
    {
        $scholarship = $scholarships->random();
        
        $applicationData = [
            'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
            'type' => 'new',
            'status' => 'in_progress',
            'grant_count' => 0,
            'created_at' => now()->subDays(rand(1, 7)),
        ];
        
        Application::create($applicationData);
    }
    
    private function setApplicationTimeline(&$applicationData, $status, $faker)
    {
        switch ($status) {
            case 'in_progress':
                $applicationData['created_at'] = now()->subDays(rand(1, 7));
                break;
                
            case 'pending':
                $applicationData['created_at'] = now()->subDays(rand(1, 60));
                break;
                
            case 'approved':
                $applicationData['created_at'] = now()->subDays(rand(30, 120));
                $applicationData['updated_at'] = now()->subDays(rand(1, 30));
                break;
                
            case 'rejected':
                $applicationData['created_at'] = now()->subDays(rand(15, 90));
                $applicationData['updated_at'] = now()->subDays(rand(1, 15));
                break;
                
            case 'claimed':
                $applicationData['created_at'] = now()->subDays(rand(60, 180));
                $applicationData['updated_at'] = now()->subDays(rand(1, 7));
                break;
        }
    }
    
    private function createComprehensiveForms($faker)
    {
        $students = User::where('role', 'student')->get();

        foreach ($students as $student) {
            // Check if form already exists
            if (Form::where('user_id', $student->id)->exists()) {
                continue;
            }
            
            $this->createDetailedForm($student, $faker);
        }
    }
    
    private function createDetailedForm($student, $faker)
    {
        $nameParts = explode(' ', $student->name);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? $nameParts[0];
        
        $genders = ['male', 'female'];
        $gender = $genders[array_rand($genders)];
        
        $programs = [
            'Bachelor of Science in Computer Engineering',
            'Bachelor of Science in Civil Engineering',
            'Bachelor of Science in Electrical Engineering',
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Business Administration',
            'Bachelor of Science in Education',
            'Bachelor of Science in Nursing',
            'Bachelor of Science in Psychology'
        ];
        
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        $program = $programs[array_rand($programs)];
        $yearLevel = $yearLevels[array_rand($yearLevels)];
        
        Form::create([
                        'user_id' => $student->id,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'middle_name' => $faker->firstName(),
            'street_barangay' => 'Barangay ' . $faker->randomElement(['Poblacion', 'San Jose', 'Santa Maria', 'San Juan', 'San Pedro']),
            'town_city' => $faker->randomElement(['Batangas City', 'Lipa City', 'Tanauan City', 'Calamba City', 'San Pablo City']),
            'province' => 'Batangas',
            'zip_code' => $faker->randomElement(['4200', '4217', '4232', '4000', '4001']),
            'age' => rand(18, 25),
            'sex' => $gender,
            'civil_status' => 'single',
            'disability' => rand(0, 10) === 0 ? $faker->randomElement(['visual', 'hearing', 'mobility', 'learning']) : null,
            'tribe' => 'Filipino',
            'citizenship' => 'Filipino',
            'birthdate' => now()->subYears(rand(18, 25))->subDays(rand(1, 365)),
            'birthplace' => $faker->city(),
            'birth_order' => rand(1, 4),
            'email' => $student->email,
            'telephone' => '09' . rand(100000000, 999999999),
            'religion' => $faker->randomElement(['Catholic', 'Protestant', 'Islam', 'Buddhist', 'Other']),
            'highschool_type' => $faker->randomElement(['Public', 'Private']),
            'monthly_allowance' => rand(2000, 15000),
            'living_arrangement' => $faker->randomElement(['with_family', 'dormitory', 'boarding_house', 'relatives']),
            'transportation' => $faker->randomElement(['public', 'private', 'walking', 'bicycle']),
            'education_level' => 'college',
            'program' => $program,
            'college' => $this->getCollegeByProgram($program),
            'year_level' => $yearLevel,
            'campus' => $student->campus->name,
            'gwa' => rand(85, 98) / 10,
            'honors' => rand(0, 3) === 0 ? $faker->randomElement(['Dean\'s Lister', 'Magna Cum Laude', 'Summa Cum Laude']) : null,
            'units_enrolled' => rand(15, 21),
            'academic_year' => '2024-2025',
            'has_existing_scholarship' => rand(0, 4) === 0,
            'existing_scholarship_details' => rand(0, 4) === 0 ? $faker->randomElement(['CHED Scholarship', 'DOST Scholarship', 'Private Foundation']) : null,
            'father_living' => rand(0, 10) !== 0,
            'father_name' => 'Father ' . $lastName,
            'father_age' => rand(45, 65),
            'father_residence' => $faker->city(),
            'father_education' => $faker->randomElement(['High School', 'College', 'Graduate School']),
            'father_contact' => '09' . rand(100000000, 999999999),
            'father_occupation' => $faker->randomElement(['Engineer', 'Teacher', 'Farmer', 'Business Owner', 'Government Employee']),
            'father_company' => $faker->company(),
            'father_company_address' => $faker->address(),
            'father_employment_status' => $faker->randomElement(['employed', 'unemployed', 'retired']),
            'mother_living' => rand(0, 15) !== 0,
            'mother_name' => 'Mother ' . $lastName,
            'mother_age' => rand(40, 60),
            'mother_residence' => $faker->city(),
            'mother_education' => $faker->randomElement(['High School', 'College', 'Graduate School']),
            'mother_contact' => '09' . rand(100000000, 999999999),
            'mother_occupation' => $faker->randomElement(['Teacher', 'Nurse', 'Housewife', 'Business Owner', 'Government Employee']),
            'mother_company' => $faker->company(),
            'mother_company_address' => $faker->address(),
            'mother_employment_status' => $faker->randomElement(['employed', 'unemployed', 'retired']),
            'family_members_count' => rand(4, 8),
            'siblings_count' => rand(1, 4),
            'family_form' => $faker->randomElement(['nuclear', 'extended', 'single_parent']),
            'monthly_family_income_bracket' => $faker->randomElement(['<10957', '10957-21194', '21195-35000', '35001-50000', '>50000']),
            'other_income_sources' => $faker->randomElement(['Part-time job', 'Relatives support', 'Government assistance', 'None']),
            'vehicle_ownership' => $faker->randomElement(['Motorcycle', 'Car', 'Both', 'None']),
            'appliances' => $faker->randomElement(['TV, Refrigerator', 'TV, Refrigerator, Washing Machine', 'Basic appliances only']),
            'house_ownership' => $faker->randomElement(['owned', 'rented', 'government']),
            'house_material' => $faker->randomElement(['concrete', 'wood', 'bamboo', 'mixed']),
            'house_type' => $faker->randomElement(['single_detached', 'duplex', 'apartment']),
            'cooking_utilities' => $faker->randomElement(['LPG', 'wood', 'kerosene', 'electric']),
            'water_source' => $faker->randomElement(['public', 'private', 'well']),
            'electricity_source' => $faker->randomElement(['public', 'generator', 'solar']),
            'monthly_bills_electric' => rand(1000, 8000),
            'monthly_bills_telephone' => rand(500, 2000),
            'monthly_bills_internet' => rand(1000, 4000),
            'student_signature' => 'Digital Signature',
            'date_signed' => now()->subDays(rand(1, 30)),
        ]);
    }
    
    private function getCollegeByProgram($program)
    {
        $collegeMap = [
            'Bachelor of Science in Computer Engineering' => 'College of Engineering',
            'Bachelor of Science in Civil Engineering' => 'College of Engineering',
            'Bachelor of Science in Electrical Engineering' => 'College of Engineering',
            'Bachelor of Science in Mechanical Engineering' => 'College of Engineering',
            'Bachelor of Science in Information Technology' => 'College of Engineering',
            'Bachelor of Science in Business Administration' => 'College of Business Administration',
            'Bachelor of Science in Education' => 'College of Education',
            'Bachelor of Science in Nursing' => 'College of Nursing',
            'Bachelor of Science in Psychology' => 'College of Arts and Sciences',
        ];
        
        return $collegeMap[$program] ?? 'College of Engineering';
    }
    
    private function createNotificationSystem($faker)
    {
        $students = User::where('role', 'student')->get();
        
        foreach ($students as $student) {
            // Create 0-8 notifications per student
            $notificationCount = rand(0, 8);
            
            for ($i = 0; $i < $notificationCount; $i++) {
                $this->createStudentNotification($student, $faker);
            }
        }
    }
    
    private function createStudentNotification($student, $faker)
    {
        $notificationTypes = [
            'scholarship_created' => 0.25,
            'application_status' => 0.35,
            'sfao_comment' => 0.20,
            'deadline_reminder' => 0.10,
            'document_request' => 0.10,
        ];
        
        $type = $this->selectScenario($notificationTypes);
        
        $notificationData = [
            'user_id' => $student->id,
            'type' => $type,
            'is_read' => rand(0, 3) === 0, // 25% chance of being read
            'created_at' => now()->subDays(rand(1, 120)),
        ];
        
        switch ($type) {
            case 'scholarship_created':
                $notificationData['title'] = 'New Scholarship Available';
                $notificationData['message'] = 'A new scholarship program has been posted. Check it out!';
                $notificationData['data'] = [
                    'scholarship_name' => $faker->randomElement(['Academic Excellence Scholarship', 'STEM Excellence Scholarship', 'Leadership Grant']),
                    'deadline' => now()->addDays(rand(30, 90))->format('Y-m-d')
                ];
                break;
                
            case 'application_status':
                $status = $faker->randomElement(['approved', 'rejected', 'pending']);
                $notificationData['title'] = 'Application Status Update';
                $notificationData['message'] = match($status) {
                    'approved' => 'Congratulations! Your scholarship application has been approved.',
                    'rejected' => 'Your scholarship application has been reviewed and unfortunately not approved.',
                    'pending' => 'Your application is currently being reviewed.',
                    default => 'Your application status has been updated.'
                };
                $notificationData['data'] = [
                    'scholarship_name' => $faker->randomElement(['Academic Excellence Scholarship', 'STEM Excellence Scholarship']),
                    'status' => $status,
                    'remarks' => $status === 'rejected' ? 'Please review the requirements and reapply next semester.' : null
                ];
                break;
                
            case 'sfao_comment':
                $notificationData['title'] = 'SFAO Comment on Your Application';
                $notificationData['message'] = $faker->randomElement([
                    'Please submit additional documents for your application.',
                    'Your application is missing some required information.',
                    'Thank you for your application. We will review it soon.'
                ]);
                $notificationData['data'] = [
                    'commenter_role' => 'sfao',
                    'application_id' => rand(1, 100)
                ];
                break;
                
            case 'deadline_reminder':
                $notificationData['title'] = 'Application Deadline Reminder';
                $notificationData['message'] = 'Don\'t forget! The scholarship application deadline is approaching.';
                $notificationData['data'] = [
                    'deadline' => now()->addDays(rand(1, 7))->format('Y-m-d'),
                    'scholarship_name' => $faker->randomElement(['Academic Excellence Scholarship', 'STEM Excellence Scholarship'])
                ];
                break;
                
            case 'document_request':
                $notificationData['title'] = 'Document Submission Required';
                $notificationData['message'] = 'Please submit the required documents for your scholarship application.';
                $notificationData['data'] = [
                    'required_documents' => ['Transcript of Records', 'Certificate of Good Moral Character'],
                    'deadline' => now()->addDays(rand(3, 14))->format('Y-m-d')
                ];
                break;
        }
        
        if ($notificationData['is_read']) {
            $notificationData['read_at'] = $notificationData['created_at']->addDays(rand(1, 30));
        }
        
        Notification::create($notificationData);
    }
    
    private function createReportingSystem($campuses, $faker)
    {
        foreach ($campuses as $campus) {
            $sfaoUser = User::where('campus_id', $campus->id)
                           ->where('role', 'sfao')
                           ->first();
            
            if (!$sfaoUser) continue;
            
            // Create 3-6 reports per campus with different statuses
            $reportCount = rand(3, 6);
            
            for ($i = 0; $i < $reportCount; $i++) {
                $this->createCampusReport($sfaoUser, $campus, $faker, $i);
            }
        }
    }
    
    private function createCampusReport($sfaoUser, $campus, $faker, $index)
    {
        $reportTypes = ['monthly', 'quarterly', 'annual'];
        $reportStatuses = ['draft', 'submitted', 'reviewed', 'approved'];
        
        $reportType = $reportTypes[array_rand($reportTypes)];
        $status = $reportStatuses[array_rand($reportStatuses)];
        
        // Generate realistic date ranges
        $startDate = now()->subMonths(rand(1, 12))->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        if ($reportType === 'quarterly') {
            $endDate = $startDate->copy()->addMonths(2)->endOfMonth();
        } elseif ($reportType === 'annual') {
            $endDate = $startDate->copy()->addMonths(11)->endOfMonth();
        }
        
        $reportData = [
            'sfao_user_id' => $sfaoUser->id,
            'campus_id' => $campus->id,
            'original_campus_selection' => $campus->name,
            'report_type' => $reportType,
            'title' => ucfirst($reportType) . ' Report - ' . $campus->name . ' - ' . $startDate->format('M Y'),
            'description' => 'Comprehensive ' . $reportType . ' report covering scholarship applications, approvals, and student statistics for ' . $campus->name,
            'report_period_start' => $startDate,
            'report_period_end' => $endDate,
            'report_data' => $this->generateComprehensiveReportData($campus, $startDate, $endDate),
            'status' => $status,
            'notes' => $status === 'draft' ? 'Report is still being prepared.' : 'Report completed and ready for review.',
            'central_feedback' => $status === 'reviewed' ? 'Report reviewed and approved by central administration.' : null,
            'submitted_at' => $status !== 'draft' ? now()->subDays(rand(1, 30)) : null,
            'reviewed_at' => in_array($status, ['reviewed', 'approved']) ? now()->subDays(rand(1, 15)) : null,
            'reviewed_by' => in_array($status, ['reviewed', 'approved']) ? 1 : null,
        ];
        
        Report::create($reportData);
    }
    
    private function generateComprehensiveReportData($campus, $startDate, $endDate)
    {
        $faker = \Faker\Factory::create();
        
        return [
            'summary' => [
                'total_applications' => rand(30, 100),
                'approved_applications' => rand(12, 40),
                'rejected_applications' => rand(8, 25),
                'pending_applications' => rand(5, 20),
                'claimed_applications' => rand(8, 30),
                'approval_rate' => rand(60, 85),
                'rejection_rate' => rand(15, 40)
            ],
            'application_types' => [
                'new_applications' => rand(20, 60),
                'continuing_applications' => rand(10, 40),
                'new_percentage' => rand(60, 80),
                'continuing_percentage' => rand(20, 40)
            ],
            'by_scholarship' => [
                'academic_excellence' => [
                    'total' => rand(15, 35),
                    'approved' => rand(8, 20),
                    'rejected' => rand(3, 12),
                    'pending' => rand(2, 8)
                ],
                'stem_excellence' => [
                    'total' => rand(12, 25),
                    'approved' => rand(6, 15),
                    'rejected' => rand(3, 8),
                    'pending' => rand(2, 6)
                ],
                'financial_assistance' => [
                    'total' => rand(20, 45),
                    'approved' => rand(12, 25),
                    'rejected' => rand(5, 15),
                    'pending' => rand(3, 10)
                ]
            ],
            'student_stats' => [
                'total_students' => rand(300, 600),
                'students_with_applications' => rand(80, 200),
                'application_rate' => rand(25, 40)
            ],
            'campus_analysis' => [
                [
                    'campus_name' => $campus->name,
                    'campus_type' => $campus->type,
                    'total_applications' => rand(30, 100),
                    'approved_applications' => rand(12, 40),
                    'rejected_applications' => rand(8, 25),
                    'pending_applications' => rand(5, 20),
                    'approval_rate' => rand(60, 85),
                    'rejection_rate' => rand(15, 40)
                ]
            ],
            'performance_insights' => [
                'overall_approval_rate' => rand(65, 85),
                'campus_consistency' => $faker->randomElement(['Good', 'Fair', 'Poor']),
                'scholarship_utilization' => $faker->randomElement(['High', 'Medium', 'Low']),
                'warnings' => [
                    'Some scholarships are underutilized',
                    'Approval rate variation detected between programs',
                    'Document submission delays noted'
                ],
                'recommendations' => [
                    'Increase awareness campaigns for underutilized scholarships',
                    'Review evaluation criteria consistency',
                    'Implement document tracking system'
                ],
                'performance_score' => rand(70, 95)
            ]
        ];
    }
}