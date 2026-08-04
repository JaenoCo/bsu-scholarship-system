<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);

        $this->app['db']->purge('sqlite');
        $this->app['db']->connection()->getSchemaBuilder()->create('campuses', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable();
            $table->foreignId('parent_campus_id')->nullable();
            $table->boolean('has_sfao_admin')->default(false);
            $table->timestamps();
        });

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('student');
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->string('sr_code')->nullable();
            $table->timestamps();
        });

        Schema::create('scholarships', function ($table) {
            $table->id();
            $table->string('scholarship_name');
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('colleges', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('campus_college', function ($table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('college_id')->constrained('colleges')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('programs', function ($table) {
            $table->id();
            $table->foreignId('campus_college_id')->nullable()->constrained('campus_college')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('campus_scholarship', function ($table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->foreignId('scholarship_id')->constrained('scholarships')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function test_sfao_admin_sees_only_campus_scoped_search_results(): void
    {
        $campusA = Campus::create(['name' => 'Campus A']);
        $campusB = Campus::create(['name' => 'Campus B']);

        $sfao = User::create([
            'name' => 'SFAO User',
            'email' => 'sfao@example.com',
            'password' => bcrypt('password'),
            'role' => 'sfao',
            'campus_id' => $campusA->id,
        ]);

        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'campus_id' => $campusA->id,
            'sr_code' => 'SR-1001',
        ]);

        User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'campus_id' => $campusB->id,
            'sr_code' => 'SR-1002',
        ]);

        Scholarship::create(['scholarship_name' => 'Doe Campus A Merit', 'campus_id' => $campusA->id]);
        Scholarship::create(['scholarship_name' => 'Campus B Merit', 'campus_id' => $campusB->id]);

        session(['user_id' => $sfao->id, 'role' => $sfao->role]);

        $response = $this->getJson('/search/suggest?term=doe');

        $response->assertOk();
        $response->assertJsonCount(1, 'students');
        $response->assertJsonPath('students.0.text', 'John Doe (SR-1001)');
        $response->assertJsonCount(1, 'scholarships');
        $response->assertJsonPath('scholarships.0.text', 'Doe Campus A Merit');
    }
}
