<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scholarship;
use App\Models\ScholarshipRequiredCondition;
use Carbon\Carbon;

class ScholarshipsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing central admin
        $centralAdmin = \App\Models\User::where('role', 'central')->first();

        $scholarships = [
            [
                'scholarship_name' => 'Academic Excellence Scholarship',
                'scholarship_type' => 'private',
                'description'      => 'Awarded to students with excellent academic performance.',
                'submission_deadline' => Carbon::now()->addMonths(2),
                'application_start_date' => Carbon::now()->subDays(7),
                'slots_available'  => 50,
                'grant_amount'     => 10000,
                'renewal_allowed'  => true,
                'grant_type' => 'recurring',
                'allow_existing_scholarship' => true,
                'eligibility_notes' => 'Must maintain excellent academic standing throughout the scholarship period.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'gwa', 'value' => '1.50', 'is_mandatory' => true],
                    ['name' => 'year_level', 'value' => 'Second Year', 'is_mandatory' => true],
                    ['name' => 'program', 'value' => 'BS Computer Science', 'is_mandatory' => false],
                ],
            ],
            [
                'scholarship_name' => 'Athletic Scholarship',
                'scholarship_type' => 'private',
                'description'      => 'For students excelling in sports and athletics.',
                'submission_deadline' => Carbon::now()->addMonths(1),
                'application_start_date' => Carbon::now()->subDays(5),
                'slots_available'  => 30,
                'grant_amount'     => 8000,
                'renewal_allowed'  => true,
                'grant_type' => 'recurring',
                'eligibility_notes' => 'Must be an active member of a university sports team.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'gwa', 'value' => '2.50', 'is_mandatory' => true],
                    ['name' => 'age', 'value' => '18', 'is_mandatory' => true],
                ],
            ],
            [
                'scholarship_name' => 'Leadership Grant',
                'scholarship_type' => 'private',
                'description'      => 'For students who have demonstrated strong leadership skills.',
                'submission_deadline' => Carbon::now()->addMonths(3),
                'application_start_date' => Carbon::now()->subDays(10),
                'slots_available'  => 20,
                'grant_amount'     => 7000,
                'renewal_allowed'  => false,
                'grant_type' => 'one_time',
                'eligibility_notes' => 'Must provide evidence of leadership roles in student organizations.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'gwa', 'value' => '2.00', 'is_mandatory' => true],
                    ['name' => 'income', 'value' => '15000', 'is_mandatory' => true],
                    ['name' => 'year_level', 'value' => 'Third Year', 'is_mandatory' => false],
                ],
            ],
            [
                'scholarship_name' => 'Cultural Arts Scholarship',
                'scholarship_type' => 'private',
                'description'      => 'Supports students active in cultural and performing arts.',
                'submission_deadline' => Carbon::now()->addWeeks(6),
                'application_start_date' => Carbon::now()->subDays(3),
                'slots_available'  => 15,
                'grant_amount'     => 6000,
                'renewal_allowed'  => true,
                'grant_type' => 'recurring',
                'eligibility_notes' => 'Must be actively involved in cultural or performing arts activities.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'gwa', 'value' => '2.75', 'is_mandatory' => true],
                    ['name' => 'program', 'value' => 'BS Computer Science', 'is_mandatory' => true],
                    ['name' => 'campus', 'value' => 'Alangilan', 'is_mandatory' => false],
                ],
            ],
            [
                'scholarship_name' => 'Financial Assistance Grant',
                'scholarship_type' => 'government',
                'description'      => 'Aimed to help financially challenged students continue their studies.',
                'submission_deadline' => Carbon::now()->addMonths(1),
                'application_start_date' => Carbon::now()->subDays(1),
                'slots_available'  => null, // unlimited
                'grant_amount'     => 5000,
                'renewal_allowed'  => true,
                'grant_type' => 'recurring',
                'allow_existing_scholarship' => true,
                'eligibility_notes' => 'Income verification documents required.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'income', 'value' => '10000', 'is_mandatory' => true],
                    ['name' => 'disability', 'value' => 'no', 'is_mandatory' => false],
                ],
            ],
            [
                'scholarship_name' => 'STEM Excellence Scholarship',
                'scholarship_type' => 'private',
                'description'      => 'For students pursuing STEM programs with outstanding performance.',
                'submission_deadline' => Carbon::now()->addMonths(2),
                'application_start_date' => Carbon::now()->subDays(14),
                'slots_available'  => 25,
                'grant_amount'     => 12000,
                'renewal_allowed'  => true,
                'grant_type' => 'recurring',
                'eligibility_notes' => 'Open to all STEM programs including Computer Science, Engineering, and Mathematics.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'gwa', 'value' => '1.75', 'is_mandatory' => true],
                    ['name' => 'program', 'value' => 'BS Computer Science', 'is_mandatory' => true],
                    ['name' => 'year_level', 'value' => 'Second Year', 'is_mandatory' => true],
                    ['name' => 'sex', 'value' => 'female', 'is_mandatory' => false],
                ],
            ],
            [
                'scholarship_name' => 'Community Service Grant',
                'scholarship_type' => 'private',
                'description'      => 'For students actively involved in community service and volunteer work.',
                'submission_deadline' => Carbon::now()->addWeeks(8),
                'application_start_date' => Carbon::now()->subDays(7),
                'slots_available'  => 18,
                'grant_amount'     => 5500,
                'renewal_allowed'  => true,
                'grant_type' => 'recurring',
                'eligibility_notes' => 'Must provide documentation of community service hours.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'gwa', 'value' => '2.25', 'is_mandatory' => true],
                    ['name' => 'year_level', 'value' => 'Third Year', 'is_mandatory' => true],
                    ['name' => 'campus', 'value' => 'Pablo Borbon', 'is_mandatory' => false],
                ],
            ],
            [
                'scholarship_name' => 'First Generation Scholar',
                'scholarship_type' => 'private',
                'description'      => 'Supporting first-generation college students in their academic journey.',
                'submission_deadline' => Carbon::now()->addMonths(1),
                'application_start_date' => Carbon::now()->subDays(2),
                'slots_available'  => 40,
                'grant_amount'     => 4500,
                'renewal_allowed'  => true,
                'grant_type' => 'recurring',
                'eligibility_notes' => 'For students whose parents did not complete college education.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'gwa', 'value' => '2.50', 'is_mandatory' => true],
                    ['name' => 'year_level', 'value' => 'First Year', 'is_mandatory' => true],
                    ['name' => 'income', 'value' => '12000', 'is_mandatory' => true],
                ],
            ],
            [
                'scholarship_name' => 'Research Excellence Grant',
                'scholarship_type' => 'private',
                'description'      => 'For students engaged in research activities and academic projects.',
                'submission_deadline' => Carbon::now()->addMonths(2),
                'application_start_date' => Carbon::now()->subDays(5),
                'slots_available'  => 12,
                'grant_amount'     => 15000,
                'renewal_allowed'  => true,
                'grant_type' => 'recurring',
                'eligibility_notes' => 'Must provide research proposal and faculty recommendation.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'gwa', 'value' => '1.25', 'is_mandatory' => true],
                    ['name' => 'year_level', 'value' => 'Third Year', 'is_mandatory' => true],
                    ['name' => 'program', 'value' => 'BS Computer Science', 'is_mandatory' => false],
                    ['name' => 'age', 'value' => '20', 'is_mandatory' => false],
                ],
            ],
            [
                'scholarship_name' => 'International Student Support',
                'scholarship_type' => 'government',
                'description'      => 'Financial assistance for international students pursuing their studies.',
                'submission_deadline' => Carbon::now()->addWeeks(10),
                'application_start_date' => Carbon::now()->subDays(1),
                'slots_available'  => 8,
                'grant_amount'     => 20000,
                'renewal_allowed'  => true,
                'grant_type' => 'recurring',
                'eligibility_notes' => 'Open to all international students with valid student visa.',
                'created_by'       => $centralAdmin->id,
                'conditions' => [
                    ['name' => 'gwa', 'value' => '2.00', 'is_mandatory' => true],
                    ['name' => 'citizenship', 'value' => 'foreign', 'is_mandatory' => true],
                    ['name' => 'year_level', 'value' => 'Second Year', 'is_mandatory' => false],
                ],
            ],
        ];

        // Get all campuses
        $campuses = \App\Models\Campus::all();

        foreach ($scholarships as $data) {
            $conditions = $data['conditions'] ?? [];
            unset($data['conditions']); // Remove conditions from scholarship data
            
            $scholarship = Scholarship::create($data);
            
            // ATTACH TO RANDOM CAMPUSES
            // Randomly pick 1 to 3 campuses to attach this scholarship to
            if ($campuses->count() > 0) {
                $randomCampuses = $campuses->random(rand(1, min(3, $campuses->count())));
                $scholarship->campuses()->attach($randomCampuses);
            }

            // Add multiple conditions for each scholarship
            foreach ($conditions as $condition) {
                ScholarshipRequiredCondition::create([
                    'scholarship_id' => $scholarship->id,
                    'name' => $condition['name'],
                    'value' => $condition['value'],
                    'is_mandatory' => $condition['is_mandatory'],
                ]);
            }
        }
    }
}
