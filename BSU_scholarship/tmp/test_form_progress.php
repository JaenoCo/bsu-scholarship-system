<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$form = new App\Models\Form([
    'civil_status' => 'Single',
    'street_barangay' => 'Main St.',
    'town_city' => 'Batangas City',
    'province' => 'Batangas',
    'zip_code' => '4200',
    'citizenship' => 'Filipino',
    'previous_gwa' => 1.75,
    'units_enrolled' => 15,
    'semester' => '1st Semester',
    'academic_year' => '2025-2026',
    'has_existing_scholarship' => false,
    'father_status' => 'Employed',
    'father_name' => 'Juan Dela Cruz Sr.',
    'mother_status' => 'Employed',
    'mother_name' => 'Maria Dela Cruz',
    'estimated_gross_annual_income' => 250000,
    'reason_for_applying' => 'Need support',
    'student_signature' => 'John Dela Cruz',
    'date_signed' => '2026-08-31',
]);

$user = new App\Models\User([
    'first_name' => 'John',
    'last_name' => 'Dela Cruz',
    'sex' => 'Male',
    'birthdate' => '2000-01-15',
    'email' => 'john.dela.cruz@g.batstate-u.edu.ph',
    'contact_number' => '09123456789',
    'sr_code' => '20-12345',
    'education_level' => 'Undergraduate',
    'college' => 'CICS',
    'program' => 'BS Computer Science',
    'year_level' => '2',
]);
$user->setRelation('campus', new App\Models\Campus(['name' => 'ARASOF']));
$form->setRelation('user', $user);

echo 'overall=' . round($form->getOverallProgress(), 1) . PHP_EOL;
echo 'required=' . round($form->getRequiredFieldsProgress(), 1) . PHP_EOL;
echo 'complete=' . ($form->isComplete() ? 'yes' : 'no') . PHP_EOL;
