<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Campus;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('en_PH');
        $password = Hash::make('password123');
        $now = now();

        // =================================================================
        // 1. CENTRAL ADMIN
        // =================================================================
        $pabloBorbon = Campus::where('name', 'Pablo Borbon')->first();

        // Check if Central Admin exists to prevent duplicates
        if (!User::where('role', 'central')->exists()) {
            User::create([
                'name' => 'Central Admin',
                'first_name' => 'Central',
                'last_name' => 'Admin',
                'email' => 'test.central-admin@g.batstate-u.edu.ph',
                'password' => $password,
                'role' => 'central',
                'campus_id' => $pabloBorbon ? $pabloBorbon->id : 1, // Default to PB or 1
                'email_verified_at' => $now,
                'sr_code' => 'CENTRAL-ADMIN', // Placeholder
                'birthdate' => $now->copy()->subYears(35)->format('Y-m-d'),
                'sex' => 'Male',
                'contact_number' => '09123456789',
            ]);
            $this->command->info('Created Central Admin: test.central-admin@g.batstate-u.edu.ph');
        } else {
            $this->command->info('Central Admin already exists.');
        }

        // =================================================================
        // 2. SFAO ADMINS (One per Constituent Campus)
        // =================================================================
        $constituentCampuses = Campus::where('type', 'constituent')->get();

        foreach ($constituentCampuses as $campus) {
             $slug = \Illuminate\Support\Str::slug($campus->name);
             $email = "test.sfao-{$slug}@g.batstate-u.edu.ph";
             
             if (!User::where('email', $email)->exists()) {
                 User::create([
                    'name' => "SFAO Admin {$campus->name}",
                    'first_name' => 'SFAO',
                    'last_name' => "Admin {$campus->name}",
                    'email' => $email,
                    'password' => $password,
                    'role' => 'sfao',
                    'campus_id' => $campus->id,
                    'email_verified_at' => $now,
                    'sr_code' => 'SFAO-' . strtoupper($slug),
                    'birthdate' => $now->copy()->subYears($faker->numberBetween(25, 50))->format('Y-m-d'),
                    'sex' => $faker->randomElement(['Male', 'Female']),
                    'contact_number' => '09' . $faker->numberBetween(100000000, 999999999),
                 ]);
                 $this->command->info("Created SFAO Admin: $email");
             }
        }
    }
}
