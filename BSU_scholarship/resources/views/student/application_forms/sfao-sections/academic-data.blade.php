<section class="mt-8">
    <h2 class="text-xl font-semibold text-bsu-red mb-6">Academic Data</h2>

    <div class="space-y-4">
        <x-student.ui.input 
            name="highschool_type" 
            label="High School Type" 
            placeholder="Public or Private"
            :value="old('highschool_type', $existingApplication->highschool_type ?? '')" 
        />

        <x-student.ui.input 
            name="monthly_allowance" 
            label="Monthly Allowance" 
            type="number"
            placeholder="₱"
            :value="old('monthly_allowance', $existingApplication->monthly_allowance ?? '')" 
        />

        <x-student.ui.input 
            name="living_arrangement" 
            label="Living Arrangement" 
            placeholder="e.g., With parents, boarding house"
            :value="old('living_arrangement', $existingApplication->living_arrangement ?? '')" 
        />

        <x-student.ui.input 
            name="transportation" 
            label="Mode of Transportation" 
            placeholder="e.g., Jeep, Tricycle"
            :value="old('transportation', $existingApplication->transportation ?? '')" 
        />

        <x-student.ui.input 
            name="education_level" 
            label="Educational Level" 
            placeholder="e.g., Undergraduate"
            :value="old('education_level', $existingApplication->education_level ?? '')" 
        />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-student.ui.input 
                name="program" 
                label="Program" 
                :value="old('program', $existingApplication->program ?? '')" 
            />
            <x-student.ui.input 
                name="college" 
                label="College" 
                :value="old('college', $existingApplication->college ?? '')" 
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-student.ui.input 
                name="year_level" 
                label="Year Level" 
                :value="old('year_level', $existingApplication->year_level ?? '')" 
            />
            <x-student.ui.input 
                name="campus" 
                label="Campus" 
                :value="old('campus', $existingApplication->campus ?? '')" 
            />
            <div>
                <label class="block mb-1 font-medium text-gray-700">GWA</label>
                <input type="number" name="gwa" step="0.01" min="1.00" max="5.00" 
                       placeholder="e.g. 2.25"
                       value="{{ old('gwa', $existingApplication->gwa ?? '') }}"
                       class="w-full border border-red-500 rounded-md p-2">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-student.ui.input 
                name="honors" 
                label="Honors Received" 
                :value="old('honors', $existingApplication->honors ?? '')" 
            />
            <x-student.ui.input 
                name="units_enrolled" 
                label="Units Enrolled" 
                :value="old('units_enrolled', $existingApplication->units_enrolled ?? '')" 
            />
        </div>

        <x-student.ui.input 
            name="academic_year" 
            label="Academic Year" 
            placeholder="e.g., 2025-2026"
            :value="old('academic_year', $existingApplication->academic_year ?? '')" 
        />

        <div class="mt-6">
            <label class="block mb-1 font-medium text-gray-700">Do you have existing scholarships?</label>
            <div class="flex items-center gap-6 mt-1">
                <label class="flex items-center gap-2">
                    <input type="radio" name="has_existing_scholarship" value="1"
                        {{ old('has_existing_scholarship', $existingApplication->has_existing_scholarship ?? '') == 1 ? 'checked' : '' }}>
                    <span>Yes</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="has_existing_scholarship" value="0"
                        {{ old('has_existing_scholarship', $existingApplication->has_existing_scholarship ?? '') == 0 ? 'checked' : '' }}>
                    <span>No</span>
                </label>
            </div>
        </div>

        <x-student.ui.input 
            name="existing_scholarship_details" 
            label="If yes, provide scholarship details" 
            :value="old('existing_scholarship_details', $existingApplication->existing_scholarship_details ?? '')" 
        />
    </div>
</section>

