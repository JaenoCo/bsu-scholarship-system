<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // ------------------- Personal Data -------------------
            'age'                 => 'nullable|integer',
            'civil_status'        => 'nullable|string',
            'birthplace'          => 'nullable|string',
            'street_barangay'     => 'nullable|string',
            'town_city'           => 'nullable|string',
            'province'            => 'nullable|string',
            'zip_code'            => 'nullable|string',
            'citizenship'         => 'nullable|string',
            'disability'          => 'nullable|string',
            'tribe'               => 'nullable|string',

            // ------------------- Academic Data -------------------
            'previous_gwa'                 => 'nullable|numeric|between:1.00,5.00',
            'honors_received'              => 'nullable|string',
            'units_enrolled'               => 'nullable|integer',
            'scholarship_applied'          => 'nullable|string',
            'semester'                     => 'nullable|string',
            'academic_year'                => 'nullable|string',
            'has_existing_scholarship'     => 'nullable|boolean',
            'existing_scholarship_details' => 'nullable|string',

            // ------------------- Family Data -------------------
            'father_status'        => 'nullable|in:living,deceased',
            'father_name'          => 'nullable|string',
            'father_address'       => 'nullable|string',
            'father_contact'       => 'nullable|string',
            'father_occupation'    => 'nullable|string',
            'mother_status'        => 'nullable|in:living,deceased',
            'mother_name'          => 'nullable|string',
            'mother_address'       => 'nullable|string',
            'mother_contact'       => 'nullable|string',
            'mother_occupation'    => 'nullable|string',
            'estimated_gross_annual_income' => 'nullable|string|in:not_over_250000,over_250000_not_over_400000,over_400000_not_over_800000,over_800000_not_over_2000000,over_2000000_not_over_8000000,over_8000000',
            'siblings_count'       => 'nullable|integer',

            // ------------------- Essay / Question -------------------
            'reason_for_applying' => 'nullable|string',

            // ------------------- Certification -------------------
            'student_signature' => 'nullable|string',
            'date_signed'       => 'nullable|date',

            // ------------------- User Data -------------------
            'last_name'          => 'nullable|string',
            'first_name'         => 'nullable|string',
            'middle_name'        => 'nullable|string',
            'sex'                => 'nullable|in:Male,Female',
            'birthdate'          => 'nullable|date',
            'email'              => 'nullable|email',
            'contact_number'     => 'nullable|string',
            'sr_code'            => 'nullable|string',
            'education_level'    => 'nullable|string',
            'program'            => 'nullable|string',
            'college'            => 'nullable|string',
            'year_level'         => 'nullable|string',
            'campus_id'          => 'nullable|exists:campuses,id',
            'track'              => 'nullable|string',
            
            // ------------------- Helpers -------------------
            'birth_mm'           => 'nullable|numeric',
            'birth_dd'           => 'nullable|numeric',
            'birth_yyyy'         => 'nullable|numeric',
            'scholarship_id'     => 'nullable|exists:scholarships,id',
            'save_and_navigate'  => 'nullable|string',
            'print_after_save'   => 'nullable|boolean',
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Combine birthdate if needed
        if (!$this->filled('birthdate') && $this->filled(['birth_mm', 'birth_dd', 'birth_yyyy'])) {
            try {
                $month = str_pad($this->birth_mm, 2, '0', STR_PAD_LEFT);
                $day = str_pad($this->birth_dd, 2, '0', STR_PAD_LEFT);
                $year = $this->birth_yyyy;
                
                if (checkdate($month, $day, $year)) {
                    $this->merge([
                        'birthdate' => sprintf('%d-%s-%s', $year, $month, $day)
                    ]);
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
        }
        
        // Calculate age if birthdate is present
        if ($this->filled('birthdate')) {
            try {
                $birthdateObj = \Carbon\Carbon::parse($this->birthdate);
                $age = $birthdateObj->age; // Carbon has a built-in age property
                $this->merge(['age' => $age]);
            } catch (\Exception $e) {
                // Ignore errors
            }
        }
        
        // Boolean conversion
        $this->merge([
            'has_existing_scholarship' => $this->boolean('has_existing_scholarship'),
        ]);
    }
}
