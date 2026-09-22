<?php

namespace Tests\Unit;

use App\Models\Campus;
use App\Models\Form;
use App\Models\User;
use Tests\TestCase;

class FormRequiredProgressTest extends TestCase
{
    public function test_required_fields_are_the_only_fields_counted_toward_progress(): void
    {
        $form = new Form([
            'civil_status' => 'Single',
            'street_barangay' => 'Main St.',
            'town_city' => 'Batangas City',
            'province' => 'Batangas',
            'zip_code' => '4200',
            'citizenship' => 'Filipino',
            'units_enrolled' => 15,
            'semester' => '1st Semester',
            'academic_year' => '2025-2026',
            'has_existing_scholarship' => false,
            'father_status' => 'Employed',
            'father_name' => 'Juan Dela Cruz Sr.',
            'mother_status' => 'Employed',
            'mother_name' => 'Maria Dela Cruz',
            'estimated_gross_annual_income' => 250000,
            'reason_for_applying' => 'I need financial support to continue my studies.',
            'student_signature' => 'John Dela Cruz',
            'date_signed' => '2026-08-31',
        ]);

        $user = new User([
            'first_name' => 'John',
            'last_name' => 'Dela Cruz',
            'middle_name' => 'Smith',
            'sex' => 'Male',
            'birthdate' => '2000-01-15',
            'email' => 'john.dela.cruz@g.batstate-u.edu.ph',
            'contact_number' => '09123456789',
            'sr_code' => '20-12345',
            'education_level' => 'Undergraduate',
            'college' => 'CICS',
            'program' => 'BS Computer Science',
            'year_level' => '2',
            'campus_id' => 4,
        ]);

        $user->setRelation('campus', new Campus(['name' => 'ARASOF']));
        $form->setRelation('user', $user);

        $this->assertTrue($form->isComplete());
        $this->assertSame(100.0, round($form->getOverallProgress(), 1));
        $this->assertSame(100.0, round($form->getRequiredFieldsProgress(), 1));
    }
}
