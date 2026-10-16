<?php

namespace Database\Factories;

use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Scholarship> */
class ScholarshipFactory extends Factory
{
    protected $model = Scholarship::class;

    public function definition(): array
    {
        return [
            'scholarship_name' => $this->faker->unique()->words(3, true) . ' Scholarship',
            'scholarship_type' => 'private',
            'description' => $this->faker->sentence(),
            'submission_deadline' => now()->addMonth(),
            'application_start_date' => now(),
            'grant_amount' => 5000,
            'grant_type' => 'recurring',
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
