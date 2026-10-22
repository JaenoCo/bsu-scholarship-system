<?php

namespace Tests\Unit;

use App\Http\Requests\StoreApplicationRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreApplicationRequestTest extends TestCase
{
    public function test_declaration_confirmation_must_be_accepted(): void
    {
        $rules = (new StoreApplicationRequest())->rules();

        $this->assertTrue(Validator::make([], [
            'declaration_confirmed' => $rules['declaration_confirmed'],
        ])->fails());

        $this->assertFalse(Validator::make(['declaration_confirmed' => '1'], [
            'declaration_confirmed' => $rules['declaration_confirmed'],
        ])->fails());
    }
}
