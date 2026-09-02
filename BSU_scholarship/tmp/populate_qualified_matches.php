<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Application;
use App\Models\GradeSubmission;
use App\Models\Scholarship;
use App\Models\User;

$students = User::where('role', 'student')
    ->whereNotNull('campus_id')
    ->orderBy('id')
    ->limit(10)
    ->get();

$scholarships = Scholarship::with('conditions')
    ->where('is_active', true)
    ->get()
    ->keyBy('scholarship_name');

$verifierId = User::whereIn('role', ['central', 'sfao'])->value('id') ?? 1;

$data = [
    ['index' => 0, 'scholarship' => 'Academic Excellence Scholarship', 'gwa' => 1.50, 'year' => '2025-2026', 'semester' => '1st Semester'],
    ['index' => 1, 'scholarship' => 'Leadership Grant', 'gwa' => 1.75, 'year' => '2025-2026', 'semester' => '2nd Semester'],
    ['index' => 2, 'scholarship' => 'STEM Excellence Scholarship', 'gwa' => 1.50, 'year' => '2025-2026', 'semester' => '1st Semester'],
    ['index' => 3, 'scholarship' => 'Community Service Grant', 'gwa' => 2.00, 'year' => '2025-2026', 'semester' => '2nd Semester'],
    ['index' => 4, 'scholarship' => 'Research Excellence Grant', 'gwa' => 1.25, 'year' => '2025-2026', 'semester' => '1st Semester'],
];

$created = 0;

foreach ($data as $entry) {
    $student = $students->get($entry['index']);
    if (! $student) {
        continue;
    }

    $scholarship = $scholarships->get($entry['scholarship']);
    if (! $scholarship) {
        continue;
    }

    GradeSubmission::updateOrCreate(
        [
            'user_id' => $student->id,
            'school_year' => $entry['year'],
            'semester' => $entry['semester'],
        ],
        [
            'status' => 'approved',
            'verified_gwa' => $entry['gwa'],
            'gwa_verified_by' => $verifierId,
            'gwa_verified_at' => now(),
            'remarks' => 'Demo qualified match',
        ]
    );

    Application::updateOrCreate(
        [
            'user_id' => $student->id,
            'scholarship_id' => $scholarship->id,
        ],
        [
            'status' => 'approved',
            'grant_count' => 0,
            'remarks' => 'Demo qualified match',
        ]
    );

    echo $student->name . ' -> ' . $scholarship->scholarship_name . ' (GWA ' . $entry['gwa'] . ")\n";
    $created++;
}

echo "Qualified demo matches created: $created\n";
