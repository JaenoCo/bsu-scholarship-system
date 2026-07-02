<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = $argv[1] ?? '22-71318@g.batstate-u.edu.ph';
$user = User::with('applications')->where('email', $email)->first();
if (!$user) {
    echo "User not found for email: {$email}\n";
    exit(0);
}

echo "User: {$user->id} | {$user->email} | role: {$user->role}\n";
echo "Applications: " . $user->applications->count() . "\n";
foreach ($user->applications as $app) {
    echo " - app id: {$app->id} scholarship_id: {$app->scholarship_id} status: {$app->status} created_at: {$app->created_at}\n";
}

echo "Done.\n";
