<?php

namespace Database\Factories;

use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Scholar> */
class ScholarFactory extends Factory
{
    protected $model = Scholar::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'student']),
            'scholarship_id' => Scholarship::factory(),
            'type' => 'new',
            'grant_count' => 0,
            'total_grant_received' => 0,
            'scholarship_start_date' => now()->toDateString(),
            'status' => 'active',
        ];
    }
}
