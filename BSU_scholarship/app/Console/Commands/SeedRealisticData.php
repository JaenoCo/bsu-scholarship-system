<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\RealisticDataSeeder;

class SeedRealisticData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:realistic {--fresh : Run migrations fresh before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with realistic scholarship system data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Creating realistic scholarship system data...');
        
        if ($this->option('fresh')) {
            $this->info('🧹 Running fresh migrations...');
            Artisan::call('migrate:fresh');
        }
        
        $this->info('🌱 Seeding realistic data...');
        $this->call(RealisticDataSeeder::class);
        
        $this->info('✅ Realistic scholarship system data created successfully!');
        $this->info('');
        $this->info('📊 System Overview:');
        $this->info('   - Multiple campuses with extension campuses');
        $this->info('   - Diverse scholarship programs with conditions');
        $this->info('   - Realistic student population with different profiles');
        $this->info('   - Various application statuses and timelines');
        $this->info('   - Comprehensive student application forms');
        $this->info('   - Realistic notification system');
        $this->info('   - Diverse reporting data');
        $this->info('');
        $this->info('🎯 Ready for realistic system testing!');
        
        return Command::SUCCESS;
    }
}
