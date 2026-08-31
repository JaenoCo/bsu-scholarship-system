<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = App\Models\User::where('email', 'test.sfao-arasof@g.batstate-u.edu.ph')->first();
echo json_encode(['user' => $u ? ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role, 'campus_id' => $u->campus_id] : null], JSON_PRETTY_PRINT);
