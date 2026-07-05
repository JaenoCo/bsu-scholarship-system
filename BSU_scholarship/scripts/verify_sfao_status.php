<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Controllers\ApplicationController;

$controller = new ApplicationController();
$ref = new ReflectionMethod($controller, 'resolveSfaoApplicationStatus');
$ref->setAccessible(true);

echo "approve -> " . $ref->invoke($controller, 'approve') . PHP_EOL;
echo "pending -> " . $ref->invoke($controller, 'pending') . PHP_EOL;
echo "reject  -> " . $ref->invoke($controller, 'reject') . PHP_EOL;
