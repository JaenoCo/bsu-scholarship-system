<?php
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the Laravel application so Eloquent and DB are available
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\StudentSubmittedDocument;
use App\Models\Application;

// 1) Users with documents but no application
$usersWithDocsNoApp = User::whereIn('id', function($q){
    $q->select('user_id')->from('student_submitted_documents');
})->whereDoesntHave('applications')->get();

// 2) Users with evaluated SFAO documents but no application
$usersWithEvaluatedDocsNoApp = User::whereIn('id', function($q){
    $q->select('user_id')->from('student_submitted_documents')->whereNotNull('evaluated_at');
})->whereDoesntHave('applications')->get();

// 3) Users with applications but all applications have null/empty status (edge case)
$usersWithAppNoStatus = collect();
$users = User::whereHas('applications')->get();
foreach ($users as $u) {
    $statuses = $u->applications->pluck('status')->filter()->values();
    if ($statuses->isEmpty()) $usersWithAppNoStatus->push($u);
}

echo "Users with submitted documents but NO application: " . $usersWithDocsNoApp->count() . PHP_EOL;
if ($usersWithDocsNoApp->isNotEmpty()) {
    echo "Samples:\n";
    foreach ($usersWithDocsNoApp->take(10) as $u) {
        echo " - {$u->id} | {$u->email} | campus: {$u->campus_id}\n";
    }
}

echo "\nUsers with evaluated documents but NO application: " . $usersWithEvaluatedDocsNoApp->count() . PHP_EOL;
if ($usersWithEvaluatedDocsNoApp->isNotEmpty()) {
    echo "Samples:\n";
    foreach ($usersWithEvaluatedDocsNoApp->take(10) as $u) {
        echo " - {$u->id} | {$u->email} | campus: {$u->campus_id}\n";
    }
}

echo "\nUsers with applications but NO statuses on those applications: " . $usersWithAppNoStatus->count() . PHP_EOL;
if ($usersWithAppNoStatus->isNotEmpty()) {
    echo "Samples:\n";
    foreach ($usersWithAppNoStatus->take(10) as $u) {
        echo " - {$u->id} | {$u->email} | campus: {$u->campus_id}\n";
    }
}

// 4) Overall count of users that would resolve to 'not_applied' by resolveDisplayStatus
$usersAll = User::where('role', 'student')->get();
$notApplied = collect();
foreach ($usersAll as $u) {
    $statuses = $u->applications->pluck('status')->filter()->values();
    if ($statuses->isEmpty()) $notApplied->push($u);
}

echo "\nTotal student users: " . $usersAll->count() . PHP_EOL;
echo "Students with no applications (would be 'not_applied'): " . $notApplied->count() . PHP_EOL;

if ($notApplied->isNotEmpty()) {
    echo "Samples:\n";
    foreach ($notApplied->take(10) as $u) {
        echo " - {$u->id} | {$u->email} | campus: {$u->campus_id}\n";
    }
}

echo "\nDone.\n";
