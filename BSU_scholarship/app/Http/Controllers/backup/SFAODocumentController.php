<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\SfaoRequirement;
use App\Models\Application;

class SFAODocumentController extends Controller
{
    /**
     * Show document upload form
     */
    public function showUploadForm($scholarship_id)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $scholarship = \App\Models\Scholarship::findOrFail($scholarship_id);
        return view('student.upload-documents', compact('scholarship'));
    }

    /**
     * Handle document uploads
     */
    public function uploadDocuments(Request $request, $scholarship_id)
    {
        if (!session()->has('user_id') || session('role') !== 'student') {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'form_137'         => 'required|file|mimes:pdf,jpg,png|max:10240',
            'grades'           => 'required|file|mimes:pdf,jpg,png|max:10240',
            'certificate'      => 'nullable|file|mimes:pdf,jpg,png|max:10240',
            'application_form' => 'required|file|mimes:pdf,jpg,png|max:10240',
        ]);

        $userId = session('user_id');

        $files = [
            'form_137'         => $request->file('form_137'),
            'grades'           => $request->file('grades'),
            'certificate'      => $request->file('certificate'),
            'application_form' => $request->file('application_form'),
        ];

        $filePaths = [];
        foreach ($files as $key => $file) {
            if ($file) {
                $filePaths[$key] = $file->store("documents/{$userId}", 'public');
            }
        }

        // Ensure scholarship_id is stored
        $filePaths['scholarship_id'] = $scholarship_id;

        SfaoRequirement::updateOrCreate(
            ['user_id' => $userId, 'scholarship_id' => $scholarship_id],
            $filePaths
        );

        // Mark student as applied
        Application::updateOrCreate(
            [
                'user_id'        => $userId,
                'scholarship_id' => $scholarship_id,
            ],
            [
                'status' => 'pending',
            ]
        );

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Documents uploaded successfully and you are now applied to this scholarship.');
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request, $role)
    {
        if (!session()->has('user_id') || session('role') !== $role) {
            return redirect('/login')->with('session_expired', true);
        }

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::find(session('user_id'));

        if ($user->profile_picture && Storage::exists('public/profile_pictures/' . $user->profile_picture)) {
            Storage::delete('public/profile_pictures/' . $user->profile_picture);
        }

        $file = $request->file('profile_picture');
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('public/profile_pictures', $filename);

        $user->profile_picture = $filename;
        $user->save();

        return back()->with('success', 'Profile picture updated.');
    }
}
