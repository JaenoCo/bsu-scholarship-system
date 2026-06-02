<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $table = 'forms';

    protected $fillable = [
        // ------------------- Personal Data -------------------
        'user_id',
        // Moved to User: last_name, first_name, middle_name
        'age',
        // Moved to User: sex
        'civil_status',
        // Moved to User: birthdate
        'birthplace',
        // Moved to User: email, contact_number
        'street_barangay',
        'town_city',
        'province',
        'zip_code',
        'citizenship',
        'disability',
        'tribe',

        // ------------------- Academic Data -------------------
        // Moved to User: sr_code, education_level, program, college_department, year_level, campus
        'previous_gwa',
        'honors_received',
        'units_enrolled',
        'scholarship_applied',
        'semester',
        'academic_year',
        'has_existing_scholarship',
        'existing_scholarship_details',

        // ------------------- Family Data -------------------
        'father_status',
        'father_name',
        'father_address',
        'father_contact',
        'father_occupation',
        'mother_status',
        'mother_name',
        'mother_address',
        'mother_contact',
        'mother_occupation',
        'estimated_gross_annual_income',
        'siblings_count',

        // ------------------- Essay / Question -------------------
        'reason_for_applying',

        // ------------------- Certification -------------------
        'student_signature',
        'date_signed',

        // ------------------- Status / Meta -------------------
        'form_status',
        'reviewer_remarks',
        'reviewed_by',
    ];

    protected $casts = [
        'has_existing_scholarship' => 'boolean',
        'previous_gwa' => 'float',
        'birthdate' => 'date',
        'date_signed' => 'date',
    ];

    /**
     * Get the user that owns this form.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Accessors for fields moved to User model
    public function getLastNameAttribute() { return $this->user->last_name ?? null; }
    public function getFirstNameAttribute() { return $this->user->first_name ?? null; }
    public function getMiddleNameAttribute() { return $this->user->middle_name ?? null; }
    public function getSexAttribute() { return $this->user->sex ?? null; }
    public function getBirthdateAttribute($value) { return $value ?? $this->user->birthdate ?? null; } // Handle if it's still in form or user
    public function getEmailAttribute() { return $this->user->email ?? null; }
    public function getContactNumberAttribute() { return $this->user->contact_number ?? null; }
    public function getSrCodeAttribute() { return $this->user->sr_code ?? null; }
    public function getEducationLevelAttribute() { return $this->user->education_level ?? null; }
    public function getProgramAttribute() { return $this->user->program ?? null; }
    public function getCollegeDepartmentAttribute() { return $this->user->college ?? null; }
    public function getYearLevelAttribute() { return $this->user->year_level ?? null; }
    public function getCampusAttribute() { return $this->user->campus?->name ?? null; } // Return campus name for display

    /**
     * Get required fields for the application form
     * These are the minimum fields needed for the form to be considered complete
     * Note: Only fields marked with * in the form are truly required, but we include
     * essential fields for a functional scholarship application
     */
    public static function getRequiredFields()
    {
        return [
            // Personal Data - Required (marked with * in form)
            'last_name',
            'first_name',
            // Essential personal information
            'sex',
            'birthdate',
            'email',
            'contact_number',
            'town_city',
            'province',
            
            // Academic Data - Required for scholarship eligibility
            'program',
            'year_level',
            'previous_gwa',
            'campus',
            
            // Family Data - Required for financial assessment
            'father_name',
            'mother_name',
            'estimated_gross_annual_income',
            
            // Essay - Required for application review
            'reason_for_applying',
            
            // Certification - Required for submission
            'student_signature',
            'date_signed',
        ];
    }

    /**
     * Get all fields (required + optional) for progress calculation
     */
    public static function getAllFields()
    {
        return [
            // Personal Data
            'last_name', 'first_name', 'middle_name', 'age', 'sex', 'civil_status',
            'birthdate', 'birthplace', 'email', 'contact_number', 'street_barangay',
            'town_city', 'province', 'zip_code', 'citizenship', 'disability', 'tribe',
            
            // Academic Data
            'sr_code', 'education_level', 'program', 'college_department', 'year_level',
            'campus', 'previous_gwa', 'honors_received', 'units_enrolled',
            'scholarship_applied', 'semester', 'academic_year', 'has_existing_scholarship',
            'existing_scholarship_details',
            
            // Family Data
            'father_status', 'father_name', 'father_address', 'father_contact', 'father_occupation',
            'mother_status', 'mother_name', 'mother_address', 'mother_contact', 'mother_occupation',
            'estimated_gross_annual_income', 'siblings_count',
            
            // Essay / Question
            'reason_for_applying',
            
            // Certification
            'student_signature', 'date_signed',
        ];
    }

    /**
     * Calculate overall progress percentage (all fields)
     */
    public function getOverallProgress()
    {
        $allFields = self::getAllFields();
        $filledFields = 0;
        
        foreach ($allFields as $field) {
            // Access attribute directly (triggers accessors if defined)
            $value = $this->$field;
            
            // Check if field is filled
            if ($value === null) {
                continue;
            }
            
            // For date fields
            if ($value instanceof \Carbon\Carbon) {
                $filledFields++;
                continue;
            }
            
            // For boolean fields, both true and false count as filled
            if (is_bool($value)) {
                $filledFields++;
                continue;
            }
            
            // For numeric fields
            if (is_numeric($value)) {
                // For GWA, 0 is not considered filled
                if ($field === 'previous_gwa' && $value == 0) {
                    continue;
                }
                // For siblings_count, 0 is valid
                if ($field === 'siblings_count' || $value > 0) {
                    $filledFields++;
                }
                continue;
            }
            
            // For string fields, check if it's not empty after trimming
            if (is_string($value) && trim($value) !== '') {
                $filledFields++;
            }
        }
        
        $totalFields = count($allFields);
        return $totalFields > 0 ? round(($filledFields / $totalFields) * 100, 1) : 0;
    }

    /**
     * Check if all required fields are filled
     */
    public function hasAllRequiredFields()
    {
        $requiredFields = self::getRequiredFields();
        
        foreach ($requiredFields as $field) {
            // Access attribute directly (triggers accessors if defined)
            $value = $this->$field;
            
            // Check if required field is empty
            if ($value === null) {
                return false;
            }
            
            // For date fields, check if it's a valid date
            if ($value instanceof \Carbon\Carbon) {
                continue; // Valid date
            }
            
            // For boolean fields, both true and false are valid
            if (is_bool($value)) {
                continue;
            }
            
            // For numeric fields, 0 is not valid (except for siblings_count which can be 0)
            if (is_numeric($value)) {
                // For GWA, 0 is not valid
                if ($field === 'previous_gwa' && ($value == 0 || $value == null)) {
                    return false;
                }
                // For other numeric fields, check if it's greater than 0 or if it's siblings_count
                if ($value == 0 && $field !== 'siblings_count') {
                    return false;
                }
                continue;
            }
            
            // For string fields, check if it's not empty after trimming
            if (is_string($value) && trim($value) === '') {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get required fields progress
     */
    public function getRequiredFieldsProgress()
    {
        $requiredFields = self::getRequiredFields();
        $filledRequired = 0;
        
        foreach ($requiredFields as $field) {
            // Access attribute directly (triggers accessors if defined)
            $value = $this->$field;
            
            if ($value === null) {
                continue;
            }
            
            // For date fields
            if ($value instanceof \Carbon\Carbon) {
                $filledRequired++;
                continue;
            }
            
            // For boolean fields, both true and false count as filled
            if (is_bool($value)) {
                $filledRequired++;
                continue;
            }
            
            // For numeric fields
            if (is_numeric($value)) {
                // For GWA, 0 is not considered filled
                if ($field === 'previous_gwa' && $value == 0) {
                    continue;
                }
                // For siblings_count, 0 is valid
                if ($field === 'siblings_count' || $value > 0) {
                    $filledRequired++;
                }
                continue;
            }
            
            // For string fields, check if it's not empty after trimming
            if (is_string($value) && trim($value) !== '') {
                $filledRequired++;
            }
        }
        
        $totalRequired = count($requiredFields);
        return $totalRequired > 0 ? round(($filledRequired / $totalRequired) * 100, 1) : 0;
    }

    /**
     * Check if form is complete (all required fields filled)
     */
    public function isComplete()
    {
        return $this->hasAllRequiredFields();
    }

}
