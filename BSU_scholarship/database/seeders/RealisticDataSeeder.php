<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class RealisticDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Run the comprehensive seeder
        $this->call(ComprehensiveSystemSeeder::class);
    }
}
