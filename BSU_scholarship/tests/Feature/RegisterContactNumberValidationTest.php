<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterContactNumberValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_an_invalid_philippine_mobile_number(): void
    {
        $this->from('/register')
            ->post('/register', ['contact_number' => '09123'])
            ->assertRedirect('/register')
            ->assertSessionHasErrors('contact_number');
    }
}
