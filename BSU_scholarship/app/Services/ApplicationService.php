<?php

namespace App\Services;

use App\Models\Form;
use App\Models\User;
use App\Models\Scholarship;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    /**
     * Process the application submission.
     *
     * @param array $data
     * @param int $userId
     * @return Form
     */
    public function processApplication(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            // Update User Data
            $this->updateUser($userId, $data);

            // Prepare Form Data
            $formData = $this->prepareFormData($data, $userId);

            // Create or Update Form
            return Form::updateOrCreate(
                ['user_id' => $userId],
                $formData
            );
        });
    }

    /**
     * Update the user's profile information.
     */
    protected function updateUser(int $userId, array $data)
    {
        $user = User::find($userId);
        if (!$user) return;

        $user->update([
            'last_name'       => $data['last_name'] ?? $user->last_name,
            'first_name'      => $data['first_name'] ?? $user->first_name,
            'middle_name'     => $data['middle_name'] ?? $user->middle_name,
            'sex'             => $data['sex'] ?? $user->sex,
            'birthdate'       => $data['birthdate'] ?? $user->birthdate,
            'contact_number'  => $data['contact_number'] ?? $user->contact_number,
            'sr_code'         => $data['sr_code'] ?? $user->sr_code,
            'education_level' => $data['education_level'] ?? $user->education_level,
            'program'         => $data['program'] ?? $user->program,
            'college'         => $data['college'] ?? $user->college,
            'year_level'      => $data['year_level'] ?? $user->year_level,
            'campus_id'       => $data['campus_id'] ?? $user->campus_id,
            'track'           => $data['track'] ?? $user->track,
        ]);
        
        // Update concatenated name
        $user->name = trim(($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? ''));
        $user->save();
    }

    /**
     * Prepare data for the Form model.
     */
    protected function prepareFormData(array $data, int $userId)
    {
        // Filter out user-specific fields that aren't in the forms table
        // (This assumes the Form model has $fillable set correctly, but explicit filtering is safer)
        
        $formData = $data;
        $formData['user_id'] = $userId;
        $formData['form_status'] = 'submitted';
        
        // Handle GWA empty string
        if (array_key_exists('previous_gwa', $data) && $data['previous_gwa'] === '') {
            $formData['previous_gwa'] = null;
        }

        // Handle Scholarship Name injection
        if (!empty($data['scholarship_id'])) {
            $scholarship = Scholarship::find($data['scholarship_id']);
            if ($scholarship) {
                $formData['scholarship_applied'] = $scholarship->scholarship_name;
            }
        }

        return $formData;
    }
}
