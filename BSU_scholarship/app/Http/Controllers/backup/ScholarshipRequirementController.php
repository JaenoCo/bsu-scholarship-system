<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScholarshipRequirement;

class ScholarshipRequirementController extends Controller
{
    // Store a new requirement
    public function store(Request $request)
    {
        $request->validate([
            'scholarship_id' => 'required|exists:scholarships,id',
            'type'           => 'required|in:condition,document',
            'name'           => 'required|string|max:255',
            'value'          => 'nullable|string|max:255',
            'is_mandatory'   => 'required|boolean',
        ]);

        ScholarshipRequirement::create($request->only(['scholarship_id', 'type', 'name', 'value', 'is_mandatory']));

        return back()->with('success', 'Requirement added successfully.');
    }

    // Update an existing requirement
    public function update(Request $request, $id)
    {
        $requirement = ScholarshipRequirement::findOrFail($id);

        $request->validate([
            'type'         => 'required|in:condition,document',
            'name'         => 'required|string|max:255',
            'value'        => 'nullable|string|max:255',
            'is_mandatory' => 'required|boolean',
        ]);

        $requirement->update($request->only(['type', 'name', 'value', 'is_mandatory']));

        return back()->with('success', 'Requirement updated successfully.');
    }

    // Delete a requirement
    public function destroy($id)
    {
        $requirement = ScholarshipRequirement::findOrFail($id);
        $requirement->delete();

        return back()->with('success', 'Requirement removed successfully.');
    }
}
