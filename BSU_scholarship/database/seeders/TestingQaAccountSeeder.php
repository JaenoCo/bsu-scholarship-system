<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** Deterministic, non-production accounts for automated journey checks. */
class TestingQaAccountSeeder extends Seeder
{
    public const PASSWORD = 'password123';

    public function run(): void
    {
        $campus = Campus::query()->updateOrCreate(
            ['name' => 'QA Test Campus'],
            ['type' => 'constituent']
        );

        foreach ([
            ['qa.student@example.test', 'QA Student', 'student'],
            ['qa.sfao@example.test', 'QA SFAO', 'sfao'],
            ['qa.central@example.test', 'QA Central', 'central'],
        ] as [$email, $name, $role]) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'first_name' => 'QA',
                    'last_name' => ucfirst($role),
                    'password' => Hash::make(self::PASSWORD),
                    'email_verified_at' => now(),
                    'role' => $role,
                    'campus_id' => $campus->id,
                ]
            );
        }
    }
}
