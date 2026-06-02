<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\Scholar;
use Carbon\Carbon;

class ScholarsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all approved applications
        $approvedApplications = Application::where('status', 'approved')
            ->with(['user', 'scholarship'])
            ->get();

        foreach ($approvedApplications as $application) {
            // Check if scholar record already exists
            $existingScholar = Scholar::where('user_id', $application->user_id)
                ->where('scholarship_id', $application->scholarship_id)
                ->first();

            if ($existingScholar) {
                continue; // Skip if scholar record already exists
            }

            // Determine scholar type based on grant count
            $scholarType = $application->grant_count > 0 ? 'old' : 'new';

            // Calculate scholarship dates
            $startDate = $application->created_at->startOfMonth();
            $endDate = $application->scholarship->renewal_allowed 
                ? $startDate->copy()->addYear() 
                : $startDate->copy()->addMonths(6);

            // Create scholar record
            Scholar::create([
                'user_id' => $application->user_id,
                'scholarship_id' => $application->scholarship_id,
                'application_id' => $application->id,
                'type' => $scholarType,
                'grant_count' => $application->grant_count,
                'total_grant_received' => $application->grant_count * $application->scholarship->grant_amount,
                'scholarship_start_date' => $startDate,
                'scholarship_end_date' => $endDate,
                'status' => 'active',
                'notes' => 'Migrated from approved application',
                'grant_history' => $this->generateGrantHistory($application),
            ]);
        }
    }

    /**
     * Generate grant history for existing applications
     */
    private function generateGrantHistory($application)
    {
        if ($application->grant_count === 0) {
            return null;
        }

        $grantHistory = [];
        $grantAmount = $application->scholarship->grant_amount;
        $startDate = $application->created_at->startOfMonth();

        for ($i = 1; $i <= $application->grant_count; $i++) {
            $grantDate = $startDate->copy()->addMonths($i - 1);
            
            $grantHistory[] = [
                'date' => $grantDate->toDateString(),
                'amount' => $grantAmount,
                'description' => "Grant #{$i} - {$application->scholarship->scholarship_name}",
                'grant_number' => $i
            ];
        }

        return $grantHistory;
    }
}
