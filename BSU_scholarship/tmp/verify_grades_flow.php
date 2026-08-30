<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StudentGrade;
use App\Models\User;
use Illuminate\Http\Request;

$user = User::firstOrCreate(
    ['email' => 'verify.grade.flow@example.com'],
    [
        'name' => 'Flow User',
        'first_name' => 'Flow',
        'last_name' => 'User',
        'password' => bcrypt('password123'),
        'role' => 'student',
    ]
);

session(['user_id' => $user->id, 'role' => 'student']);
StudentGrade::where('user_id', $user->id)->delete();
StudentGrade::create([
    'user_id' => $user->id,
    'school_year' => '2025-2026',
    'semester' => '1st Semester',
    'subject_code' => 'CS101',
    'subject_name' => 'Intro',
    'grade' => 95.00,
    'document_path' => 'student_grades/1/grades.pdf',
    'status' => 'pending',
]);

$controller = new App\Http\Controllers\StudentGradesController();
$viewData = $controller->create(new Request(['view' => '1']))->getData();
$editData = $controller->edit()->getData();

echo ($viewData['isReadOnly'] ? 'READONLY' : 'EDITABLE') . ':' . ($viewData['isEditMode'] ? 'EDIT' : 'VIEW') . PHP_EOL;
echo ($editData['isReadOnly'] ? 'READONLY' : 'EDITABLE') . ':' . ($editData['isEditMode'] ? 'EDIT' : 'VIEW') . PHP_EOL;
