<?php

namespace Tests\Unit;

use App\Http\Controllers\ApplicationController;
use PHPUnit\Framework\TestCase;

class ApplicationControllerStatusTest extends TestCase
{
    public function test_sfao_evaluation_maps_to_in_progress_for_approve_and_pending(): void
    {
        $controller = new ApplicationController();

        $method = new \ReflectionMethod($controller, 'resolveSfaoApplicationStatus');
        $method->setAccessible(true);

        $this->assertSame('in_progress', $method->invoke($controller, 'approve'));
        $this->assertSame('in_progress', $method->invoke($controller, 'pending'));
        $this->assertSame('rejected', $method->invoke($controller, 'reject'));
    }
}
