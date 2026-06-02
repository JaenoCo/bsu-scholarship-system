<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Application;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\Campus;
use Carbon\Carbon;

class ApplicationsAndScholarsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Get all students and scholarships
        $students = User::where('role', 'student')->get();
        $scholarships = Scholarship::all();
        
        if ($students->isEmpty() || $scholarships->isEmpty()) {
            $this->command->error('❌ Please run UsersTableSeeder and ScholarshipsTableSeeder first!');
            return;
        }
        
        // Get constituent campuses and their extensions
        $constituentCampuses = Campus::where('type', 'constituent')->get();
        
        foreach ($constituentCampuses as $constituent) {
            $this->command->info("🏛️ Processing {$constituent->name} and its extensions...");
            
            // Get all campuses under this constituent (constituent + extensions)
            $allCampuses = $constituent->getAllCampusesUnder();
            $campusIds = $allCampuses->pluck('id');
            
            // Get students from this constituent and its extensions
            $campusStudents = $students->whereIn('campus_id', $campusIds);
            
            if ($campusStudents->count() === 0) {
                $this->command->warn("⚠️ No students found for {$constituent->name}");
                continue;
            }
            
            $this->command->info("📊 Found {$campusStudents->count()} students for {$constituent->name} group");
            
            // Balance the statuses for this campus group
            $this->balanceCampusGroup($campusStudents, $scholarships, $constituent->name);
        }
        
        $this->command->info('✅ Applications and scholars created successfully!');
    }
    
    private function balanceCampusGroup($students, $scholarships, $campusName)
    {
        $totalStudents = $students->count();
        $this->command->info("🎯 Balancing {$totalStudents} students for {$campusName}...");
        
        // Calculate distribution:
        // 25% scholars (approved applications)
        // 50% applicants (mixed statuses: in_progress, pending, rejected)
        // 25% not_applied (no applications yet)
        $scholarCount = intval($totalStudents * 0.25);
        $applicantCount = intval($totalStudents * 0.5);
        $notAppliedCount = $totalStudents - $scholarCount - $applicantCount;
        
        // Split students
        $scholarStudents = $students->take($scholarCount);
        $applicantStudents = $students->skip($scholarCount)->take($applicantCount);
        $notAppliedStudents = $students->skip($scholarCount + $applicantCount);
        
        $this->command->info("🎓 Creating {$scholarCount} scholars (approved applications)...");
        $this->createScholars($scholarStudents, $scholarships);
        
        $this->command->info("📝 Creating {$applicantCount} applicants (mixed statuses)...");
        $this->createApplicants($applicantStudents, $scholarships);
        
        $this->command->info("🚫 {$notAppliedCount} students will remain not_applied (no applications)");
        
        $this->command->info("✅ {$campusName} group balanced successfully!");
    }
    
    private function createScholars($students, $scholarships)
    {
        foreach ($students as $student) {
            // Create approved application
            $scholarship = $scholarships->random();
            $grantCount = rand(0, 3); // 0-3 grants (0 = new scholar, 1+ = old scholar)
            $application = Application::create([
                'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
                'status' => 'approved',
                'grant_count' => $grantCount,
                'created_at' => now()->subDays(rand(30, 180)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);
            
            // Create scholar record
            $scholarType = $grantCount > 0 ? 'old' : 'new';
            $startDate = $application->created_at->startOfMonth();
            $endDate = $scholarship->renewal_allowed 
                ? $startDate->copy()->addYear() 
                : $startDate->copy()->addMonths(6);
            
            Scholar::create([
                'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
                'application_id' => $application->id,
                'type' => $scholarType,
                'grant_count' => $application->grant_count,
                'total_grant_received' => $application->grant_count * $scholarship->grant_amount,
                'scholarship_start_date' => $startDate,
                'scholarship_end_date' => $endDate,
                'status' => 'active',
                'notes' => 'Created from approved application',
                'grant_history' => $this->generateGrantHistory($application, $scholarship),
            ]);
        }
    }
    
    private function createApplicants($students, $scholarships)
    {
        // Balanced status distribution for applicants (no approved here, they go to scholars)
        $applicationStatuses = ['in_progress', 'pending', 'rejected'];
        $statusWeights = [0.4, 0.4, 0.2]; // 40% in_progress, 40% pending, 20% rejected
        
        foreach ($students as $student) {
            $scholarship = $scholarships->random();
            $status = $this->weightedRandom($applicationStatuses, $statusWeights);
            
            Application::create([
                'user_id' => $student->id,
                'scholarship_id' => $scholarship->id,
                'status' => $status,
                'grant_count' => 0,
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => $status === 'rejected' ? now()->subDays(rand(1, 15)) : null,
            ]);
        }
    }
    
    private function generateGrantHistory($application, $scholarship)
    {
        if ($application->grant_count === 0) {
            return null;
        }
        
        $grantHistory = [];
        $grantAmount = $scholarship->grant_amount;
        $startDate = $application->created_at->startOfMonth();
        
        for ($i = 1; $i <= $application->grant_count; $i++) {
            $grantDate = $startDate->copy()->addMonths($i - 1);
            
            $grantHistory[] = [
                'date' => $grantDate->toDateString(),
                'amount' => $grantAmount,
                'description' => "Grant #{$i} - {$scholarship->scholarship_name}",
                'grant_number' => $i
            ];
        }
        
        return $grantHistory;
    }
    
    private function weightedRandom($items, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = mt_rand() / mt_getrandmax() * $totalWeight;
        
        $currentWeight = 0;
        foreach ($items as $index => $item) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $item;
            }
        }
        
        return $items[0]; // Fallback
    }
}
