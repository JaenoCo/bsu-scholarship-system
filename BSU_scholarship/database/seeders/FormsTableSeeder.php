<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Form;
use App\Models\Scholarship;
use Illuminate\Support\Facades\DB;

class FormsTableSeeder extends Seeder
{
    public function run()
    {
        // clear old data - handle foreign key constraints
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('forms')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get only student users with their campus information
        $studentUsers = User::where('role', 'student')->with('campus')->get();
        
        // Get available scholarships (optional - forms can exist without scholarships)
        $scholarships = Scholarship::all();

        foreach ($studentUsers as $user) {
            // Assign a random scholarship to each form (or null if no scholarships exist)
            $scholarship = $scholarships->isNotEmpty() ? $scholarships->random() : null;
            // Extract name parts from user's name for consistency
            $nameParts = explode(' ', $user->name);
            $firstName = $nameParts[0];
            $lastName = end($nameParts);
            $middleName = count($nameParts) > 2 ? $nameParts[1] : null;

            Form::create([
                'user_id' => $user->id,
                // Personal Data
                // Moved to User: last_name, first_name, middle_name
                'age' => fake()->numberBetween(18, 30),
                // Moved to User: sex
                'civil_status' => fake()->randomElement(['Single', 'Married', 'Widowed', 'Divorced', 'Separated']),
                // Moved to User: birthdate
                // Moved to User: birthplace
                // Moved to User: email, contact_number
                // Address breakdown
                'street_barangay' => fake()->streetAddress,
                'town_city'       => fake()->city,
                'province'        => 'Batangas',
                'zip_code'        => fake()->postcode,
                'citizenship' => 'Filipino',
                'disability' => fake()->optional(0.1)->randomElement(['Visual Impairment', 'Hearing Impairment', 'Mobility Impairment', 'Learning Disability']),
                'tribe' => fake()->optional(0.2)->randomElement(['Tagalog', 'Bisaya', 'Ilocano', 'Bicolano', 'Waray']),

                // Academic Data
                // Moved to User: sr_code, education_level, program, college_department, year_level, campus
                'previous_gwa' => fake()->randomFloat(2, 1.00, 3.00),
                'honors_received' => fake()->optional(0.3)->randomElement(['Dean\'s Lister', 'Honor Student', 'Summa Cum Laude', 'Magna Cum Laude', 'Cum Laude']),
                'units_enrolled' => fake()->numberBetween(18, 30),
                'scholarship_applied' => fake()->optional(0.5)->words(3, true),
                'semester' => fake()->randomElement(['1st Semester', '2nd Semester', 'Summer']),
                'academic_year' => '2024-2025',
                'has_existing_scholarship' => fake()->boolean(20), // 20% chance of having existing scholarship
                'existing_scholarship_details' => fake()->optional(0.2)->randomElement([
                    'CHED Scholarship', 'DOST Scholarship', 'Local Government Scholarship',
                    'Private Foundation Grant', 'University Financial Aid'
                ]),

                // Family Data
                'father_status' => fake()->randomElement(['living', 'deceased']),
                'father_name' => 'Mr. ' . fake()->lastName . ' ' . $lastName, // Realistic father name
                'father_address' => fake()->city,
                'father_contact' => fake()->phoneNumber,
                'father_occupation' => fake()->jobTitle,
                'mother_status' => fake()->randomElement(['living', 'deceased']),
                'mother_name' => 'Mrs. ' . fake()->lastName . ' ' . $lastName, // Realistic mother name
                'mother_address' => fake()->city,
                'mother_contact' => fake()->phoneNumber,
                'mother_occupation' => fake()->jobTitle,
                'estimated_gross_annual_income' => fake()->randomElement(['not_over_250000', 'over_250000_not_over_400000', 'over_400000_not_over_800000', 'over_800000_not_over_2000000', 'over_2000000_not_over_8000000', 'over_8000000']),
                'siblings_count' => fake()->numberBetween(0, 5),

                // Essay / Question
                'reason_for_applying' => fake()->optional(0.8)->paragraph(3),

                // Certification
                'student_signature' => $firstName . ' ' . $lastName, // Use actual name for signature
                'date_signed' => fake()->dateTimeBetween('-30 days', 'now'),

                // Status / Meta
                'form_status' => fake()->randomElement(['draft', 'submitted', 'under_review', 'approved', 'rejected']),
                'reviewer_remarks' => fake()->optional(0.3)->sentence(),
                'reviewed_by' => null, // Can be set to a reviewer user ID if needed

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
