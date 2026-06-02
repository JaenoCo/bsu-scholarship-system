<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Campus;
use App\Models\Scholarship;
use App\Models\Application;
use App\Models\Report;
use App\Models\Form;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class RealisticSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🌱 Creating realistic system data...');
        
        $faker = Faker::create();
        
        // Get all campuses
        $campuses = Campus::all();
        $scholarships = Scholarship::all();
        
        if ($campuses->isEmpty() || $scholarships->isEmpty()) {
            $this->command->error('❌ Please run CampusSeeder and ScholarshipsTableSeeder first!');
            return;
        }
        
        // Skip user creation - handled by UsersTableSeeder
        // $this->createSfaoUsers($campuses);
        // $this->createStudents($campuses, $scholarships, $faker);
        
        // Create reports for SFAO users
        $this->createReports($campuses);
        
        $this->command->info('✅ Realistic system data created successfully!');
    }
    
    private function createSfaoUsers($campuses)
    {
        $this->command->info('👥 Creating SFAO users...');
        
        foreach ($campuses as $campus) {
            $email = 'sfao.' . strtolower(str_replace([' ', '(', ')', '–'], ['', '', '', ''], $campus->name)) . '@g.batstate-u.edu.ph';
            
            // Check if SFAO user already exists for this campus
            $existingSfao = User::where('campus_id', $campus->id)
                              ->where('role', 'sfao')
                              ->first();
            
            if (!$existingSfao) {
                // Create SFAO user for each campus
                User::create([
                    'name' => 'SFAO Admin - ' . $campus->name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'sfao',
                    'campus_id' => $campus->id,
                    'email_verified_at' => now()->subMonths(6),
                ]);
            }
        }
    }
    
    private function createStudents($campuses, $scholarships, $faker)
    {
        $this->command->info('🎓 Creating students with realistic application patterns...');
        
        $applicationStatuses = ['not_applied', 'applied', 'approved', 'pending', 'rejected'];
        $genders = ['male', 'female'];
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
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
            'Bachelor of Science in Accountancy'
        ];
        
        foreach ($campuses as $campus) {
            $this->command->info("📚 Creating students for {$campus->name}...");
            
            // Create 10 students per campus
            for ($i = 1; $i <= 10; $i++) {
                $gender = $genders[array_rand($genders)];
                $yearLevel = $yearLevels[array_rand($yearLevels)];
                $program = $programs[array_rand($programs)];
                
                $studentId = $faker->unique()->numberBetween(100000, 999999);
                $email = sprintf("99-%06d@g.batstate-u.edu.ph", $studentId);
                
                // Check if student already exists
                $existingStudent = User::where('email', $email)->first();
                
                if ($existingStudent) {
                    $student = $existingStudent;
                } else {
                    // Create student user
                    $student = User::create([
                        'name' => $this->generateStudentName($gender),
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'role' => 'student',
                        'campus_id' => $campus->id,
                        'email_verified_at' => now()->subMonths(rand(1, 12)),
                    ]);
                }
                
                // Create form for student
                $this->createStudentForm($student, $gender, $yearLevel, $program);
                
                // For constituent campuses, ensure first few students have specific application types
                $applicationType = null;
                if ($campus->type === 'constituent') {
                    if ($i <= 2) {
                        $applicationType = 'new'; // First 2 students are new applicants
                    } elseif ($i === 3) {
                        $applicationType = 'continuing'; // 3rd student is continuing
                    }
                }
                
                // Create applications based on status distribution
                $this->createStudentApplications($student, $scholarships, $applicationStatuses[$i % 5], $applicationType);
            }
            
            // Ensure constituent campuses have both new and continuing applicants
            if ($campus->type === 'constituent') {
                $this->ensureConstituentCampusApplicationTypes($campus, $scholarships);
            }
        }
    }
    
    private function generateStudentName($gender)
    {
        $maleNames = [
            'Juan Carlos', 'Miguel Santos', 'Andres Cruz', 'Jose Maria', 'Carlos Rodriguez',
            'Antonio Lopez', 'Francisco Garcia', 'Manuel Torres', 'Rafael Martinez', 'Diego Herrera'
        ];
        
        $femaleNames = [
            'Maria Elena', 'Ana Sofia', 'Carmen Rosa', 'Isabella Grace', 'Valentina Rose',
            'Gabriela Marie', 'Camila Faith', 'Sofia Grace', 'Valeria Hope', 'Natalia Joy'
        ];
        
        $lastNames = [
            'Santos', 'Cruz', 'Garcia', 'Rodriguez', 'Lopez', 'Martinez', 'Gonzalez', 'Perez', 'Sanchez', 'Ramirez'
        ];
        
        $firstNames = $gender === 'male' ? $maleNames : $femaleNames;
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        
        return $firstName . ' ' . $lastName;
    }
    
    private function createStudentForm($student, $gender, $yearLevel, $program)
    {
        // Check if form already exists
        $existingForm = Form::where('user_id', $student->id)->first();
        
        if ($existingForm) {
            return; // Form already exists
        }
        
        Form::create([
            'user_id' => $student->id,
            'last_name' => explode(' ', $student->name)[1],
            'first_name' => explode(' ', $student->name)[0],
            'middle_name' => 'M.',
            'street_barangay' => 'Sample Barangay ' . rand(1, 10),
            'town_city' => 'Batangas City',
            'province' => 'Batangas',
            'zip_code' => '4200',
            'age' => rand(18, 25),
            'sex' => $gender,
            'civil_status' => 'single',
            'disability' => 'none',
            'tribe' => 'Filipino',
            'citizenship' => 'Filipino',
            'birthdate' => now()->subYears(rand(18, 25))->subDays(rand(1, 365)),
            'birthplace' => 'Batangas',
            'birth_order' => rand(1, 3),
            'email' => $student->email,
            'telephone' => '09' . rand(100000000, 999999999),
            'religion' => 'Catholic',
            'highschool_type' => 'Public',
            'monthly_allowance' => rand(2000, 10000),
            'living_arrangement' => 'with_family',
            'transportation' => 'public',
            'education_level' => 'college',
            'program' => $program,
            'college' => 'College of Engineering',
            'year_level' => $yearLevel,
            'campus' => $student->campus->name,
            'gwa' => rand(85, 98) / 10,
            'honors' => rand(0, 1) ? 'Dean\'s Lister' : null,
            'units_enrolled' => rand(15, 21),
            'academic_year' => '2024-2025',
            'has_existing_scholarship' => rand(0, 1),
            'existing_scholarship_details' => rand(0, 1) ? 'CHED Scholarship' : null,
            'father_living' => true,
            'father_name' => 'Father ' . explode(' ', $student->name)[1],
            'father_age' => rand(45, 65),
            'father_residence' => 'Batangas',
            'father_education' => 'College Graduate',
            'father_contact' => '09' . rand(100000000, 999999999),
            'father_occupation' => 'Engineer',
            'father_company' => 'Sample Company',
            'father_company_address' => 'Batangas City',
            'father_employment_status' => 'employed',
            'mother_living' => true,
            'mother_name' => 'Mother ' . explode(' ', $student->name)[1],
            'mother_age' => rand(40, 60),
            'mother_residence' => 'Batangas',
            'mother_education' => 'College Graduate',
            'mother_contact' => '09' . rand(100000000, 999999999),
            'mother_occupation' => 'Teacher',
            'mother_company' => 'Public School',
            'mother_company_address' => 'Batangas City',
            'mother_employment_status' => 'employed',
            'family_members_count' => rand(4, 6),
            'siblings_count' => rand(1, 3),
            'family_form' => 'nuclear',
            'monthly_family_income_bracket' => rand(15000, 50000),
            'other_income_sources' => 'Part-time job',
            'vehicle_ownership' => 'motorcycle',
            'appliances' => 'TV, Refrigerator, Washing Machine',
            'house_ownership' => 'owned',
            'house_material' => 'concrete',
            'house_type' => 'single_detached',
            'cooking_utilities' => 'LPG',
            'water_source' => 'public',
            'electricity_source' => 'public',
            'monthly_bills_electric' => rand(2000, 5000),
            'monthly_bills_telephone' => rand(500, 1500),
            'monthly_bills_internet' => rand(1000, 3000),
            'student_signature' => 'Digital Signature',
            'date_signed' => now()->subDays(rand(1, 30)),
        ]);
    }
    
    private function createStudentApplications($student, $scholarships, $status, $applicationType = null)
    {
        if ($status === 'not_applied') {
            return; // Student hasn't applied
        }
        
        // Check if application already exists
        $existingApplication = Application::where('user_id', $student->id)->first();
        
        if ($existingApplication) {
            return; // Application already exists
        }
        
        // Random scholarship
        $scholarship = $scholarships->random();
        
        // Determine application type
        $type = $applicationType ?: (rand(0, 1) ? 'new' : 'continuing');
        
        // Create application with realistic timeline
        $applicationData = [
            'user_id' => $student->id,
            'scholarship_id' => $scholarship->id,
            'type' => $type,
            'grant_count' => $status === 'approved' ? rand(1, 3) : 0,
        ];
        
        // Set status and timeline based on status
        switch ($status) {
            case 'applied':
                $applicationData['status'] = 'pending';
                $applicationData['created_at'] = now()->subDays(rand(1, 30));
                break;
                
            case 'approved':
                $applicationData['status'] = 'approved';
                $applicationData['created_at'] = now()->subDays(rand(30, 90));
                $applicationData['updated_at'] = now()->subDays(rand(1, 15));
                break;
                
            case 'pending':
                $applicationData['status'] = 'pending';
                $applicationData['created_at'] = now()->subDays(rand(1, 60));
                break;
                
            case 'rejected':
                $applicationData['status'] = 'rejected';
                $applicationData['created_at'] = now()->subDays(rand(15, 45));
                $applicationData['updated_at'] = now()->subDays(rand(1, 10));
                break;
        }
        
        Application::create($applicationData);
    }
    
    private function ensureConstituentCampusApplicationTypes($campus, $scholarships)
    {
        $this->command->info("🎯 Ensuring application type diversity for {$campus->name}...");
        
        // Get existing applications for this campus
        $existingApplications = Application::whereHas('user', function($query) use ($campus) {
            $query->where('campus_id', $campus->id);
        })->get();
        
        $newApplications = $existingApplications->where('type', 'new')->count();
        $continuingApplications = $existingApplications->where('type', 'continuing')->count();
        
        $this->command->info("📊 Current applications for {$campus->name}: New: {$newApplications}, Continuing: {$continuingApplications}");
        
        // If we don't have at least 2 new applications, create some
        if ($newApplications < 2) {
            $studentsNeeded = 2 - $newApplications;
            $this->createAdditionalApplications($campus, $scholarships, 'new', $studentsNeeded);
        }
        
        // If we don't have at least 1 continuing application, create some
        if ($continuingApplications < 1) {
            $studentsNeeded = 1 - $continuingApplications;
            $this->createAdditionalApplications($campus, $scholarships, 'continuing', $studentsNeeded);
        }
        
        // Verify final counts
        $finalApplications = Application::whereHas('user', function($query) use ($campus) {
            $query->where('campus_id', $campus->id);
        })->get();
        
        $finalNew = $finalApplications->where('type', 'new')->count();
        $finalContinuing = $finalApplications->where('type', 'continuing')->count();
        
        $this->command->info("✅ Final applications for {$campus->name}: New: {$finalNew}, Continuing: {$finalContinuing}");
    }
    
    private function createAdditionalApplications($campus, $scholarships, $type, $count)
    {
        // Get students from this campus who don't have applications yet
        $studentsWithoutApplications = User::where('campus_id', $campus->id)
            ->where('role', 'student')
            ->whereDoesntHave('applications')
            ->limit($count)
            ->get();
        
        if ($studentsWithoutApplications->count() < $count) {
            $this->command->warning("⚠️  Not enough students without applications for {$campus->name}. Creating additional students...");
            
            // Create additional students if needed
            $additionalNeeded = $count - $studentsWithoutApplications->count();
            for ($i = 1; $i <= $additionalNeeded; $i++) {
                $gender = ['male', 'female'][array_rand([0, 1])];
                $yearLevel = ['1st Year', '2nd Year', '3rd Year', '4th Year'][array_rand([0, 1, 2, 3])];
                $program = 'Bachelor of Science in Computer Engineering';
                
                $email = 'additional' . $i . '.' . strtolower(str_replace([' ', '(', ')', '–'], ['', '', '', ''], $campus->name)) . '@g.batstate-u.edu.ph';
                
                $student = User::create([
                    'name' => $this->generateStudentName($gender),
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'campus_id' => $campus->id,
                    'email_verified_at' => now()->subMonths(rand(1, 12)),
                ]);
                
                // Create form for student
                $this->createStudentForm($student, $gender, $yearLevel, $program);
                
                $studentsWithoutApplications->push($student);
            }
        }
        
        // Create applications for the students
        foreach ($studentsWithoutApplications->take($count) as $student) {
            $scholarship = $scholarships->random();
            
            $applicationData = [
                'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
                'type' => $type,
                'grant_count' => $type === 'continuing' ? rand(1, 3) : 0,
                'status' => 'approved', // Make them approved to show they're active
                'created_at' => now()->subDays(rand(30, 180)), // Created some time ago
                'updated_at' => now()->subDays(rand(1, 30)), // Recently updated
            ];
            
            Application::create($applicationData);
            $this->command->info("✅ Created {$type} application for student {$student->name} at {$campus->name}");
        }
    }
    
    private function createReports($campuses)
    {
        $this->command->info('📊 Creating SFAO reports...');
        
        $reportTypes = ['monthly', 'quarterly', 'annual'];
        $reportStatuses = ['submitted', 'reviewed', 'approved', 'draft'];
        
        foreach ($campuses as $campus) {
            $sfaoUser = User::where('campus_id', $campus->id)
                           ->where('role', 'sfao')
                           ->first();
            
            if (!$sfaoUser) continue;
            
            // Create 3-5 reports per campus
            $reportCount = rand(3, 5);
            
            for ($i = 0; $i < $reportCount; $i++) {
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
                    'report_data' => $this->generateReportData($campus, $startDate, $endDate),
                    'status' => $status,
                    'notes' => $status === 'draft' ? 'Report is still being prepared.' : null,
                    'central_feedback' => $status === 'reviewed' ? 'Report reviewed and approved by central administration.' : null,
                    'submitted_at' => $status !== 'draft' ? now()->subDays(rand(1, 30)) : null,
                    'reviewed_at' => in_array($status, ['reviewed', 'approved']) ? now()->subDays(rand(1, 15)) : null,
                    'reviewed_by' => in_array($status, ['reviewed', 'approved']) ? 1 : null, // Assuming central admin has ID 1
                ];
                
                Report::create($reportData);
            }
        }
    }
    
    private function generateReportData($campus, $startDate, $endDate)
    {
        return [
            'total_applications' => rand(15, 50),
            'approved_applications' => rand(5, 20),
            'rejected_applications' => rand(3, 15),
            'pending_applications' => rand(2, 10),
            'total_students' => rand(100, 300),
            'scholarship_distribution' => [
                'academic_excellence' => rand(5, 15),
                'financial_assistance' => rand(10, 25),
                'sports_scholarship' => rand(2, 8),
            ],
            'monthly_breakdown' => [
                'applications_received' => rand(5, 20),
                'applications_processed' => rand(3, 15),
                'new_scholarships_awarded' => rand(2, 10),
            ],
            'campus_highlights' => [
                'top_performing_programs' => ['Engineering', 'Business', 'Education'],
                'notable_achievements' => 'Increased scholarship application rate by 15%',
                'challenges_identified' => 'Limited scholarship slots for high-demand programs',
            ]
        ];
    }
}
