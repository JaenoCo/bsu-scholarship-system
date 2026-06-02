<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Generate student number format: YY-XXXXX
        $yearPrefix   = $this->faker->numberBetween(20, 25);   // e.g. 20–25 = 2020–2025 entry year
        $studentId    = $this->faker->unique()->numberBetween(10000, 99999); // 5 digits
        $studentEmail = sprintf("%02d-%05d@g.batstate-u.edu.ph", $yearPrefix, $studentId);

        return [
            'name'              => $this->faker->name(),
            'email'             => $studentEmail,
            'email_verified_at' => now(),
            'password'          => bcrypt('password123'), // default password for all seeded users
            'remember_token'    => \Illuminate\Support\Str::random(10),
            'role'              => $this->faker->randomElement(['student', 'sfao', 'central']),
            'campus_id'         => $this->faker->numberBetween(1, 11), // adjust to match your 11 seeded campuses
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
