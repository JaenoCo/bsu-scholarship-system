<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PurgeStudentsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_only_student_users_when_purged(): void
    {
        $student = User::create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->artisan('students:purge', ['--force' => true])
            ->expectsOutputToContain('Deleted 1 student users and related records.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
