<section>
    <h2 class="text-xl font-semibold text-bsu-red mb-4">Family Data</h2>

    <!-- Father Section -->
    <div class="border border-gray-300 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Father's Information</h3>

        <div class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Is Father Living?</label>
                <select name="father_living" class="w-full border border-red-500 rounded-md px-3 py-2">
                    <option value="1" {{ old('father_living', $existingApplication->father_living ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('father_living', $existingApplication->father_living ?? '') == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <x-student.ui.input 
                name="father_name" 
                label="Father's Name" 
                :value="old('father_name', $existingApplication->father_name ?? '')" 
            />

            <x-student.ui.input 
                name="father_age" 
                label="Father's Age" 
                type="number"
                :value="old('father_age', $existingApplication->father_age ?? '')" 
            />

            <x-student.ui.input 
                name="father_residence" 
                label="Father's Residence" 
                :value="old('father_residence', $existingApplication->father_residence ?? '')" 
            />

            <x-student.ui.input 
                name="father_education" 
                label="Father's Education" 
                :value="old('father_education', $existingApplication->father_education ?? '')" 
            />

            <x-student.ui.input 
                name="father_contact" 
                label="Father's Contact" 
                :value="old('father_contact', $existingApplication->father_contact ?? '')" 
            />

            <x-student.ui.input 
                name="father_occupation" 
                label="Father's Occupation" 
                :value="old('father_occupation', $existingApplication->father_occupation ?? '')" 
            />

            <x-student.ui.input 
                name="father_company" 
                label="Father's Company" 
                :value="old('father_company', $existingApplication->father_company ?? '')" 
            />

            <x-student.ui.input 
                name="father_company_address" 
                label="Father's Company Address" 
                :value="old('father_company_address', $existingApplication->father_company_address ?? '')" 
            />

            <x-student.ui.input 
                name="father_employment_status" 
                label="Father's Employment Status" 
                :value="old('father_employment_status', $existingApplication->father_employment_status ?? '')" 
            />
        </div>
    </div>

    <!-- Mother Section -->
    <div class="border border-gray-300 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Mother's Information</h3>

        <div class="space-y-4">
            <div>
                <label class="block font-medium mb-1">Is Mother Living?</label>
                <select name="mother_living" class="w-full border border-red-500 rounded-md px-3 py-2">
                    <option value="1" {{ old('mother_living', $existingApplication->mother_living ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('mother_living', $existingApplication->mother_living ?? '') == 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <x-student.ui.input 
                name="mother_name" 
                label="Mother's Name" 
                :value="old('mother_name', $existingApplication->mother_name ?? '')" 
            />

            <x-student.ui.input 
                name="mother_age" 
                label="Mother's Age" 
                type="number"
                :value="old('mother_age', $existingApplication->mother_age ?? '')" 
            />

            <x-student.ui.input 
                name="mother_residence" 
                label="Mother's Residence" 
                :value="old('mother_residence', $existingApplication->mother_residence ?? '')" 
            />

            <x-student.ui.input 
                name="mother_education" 
                label="Mother's Education" 
                :value="old('mother_education', $existingApplication->mother_education ?? '')" 
            />

            <x-student.ui.input 
                name="mother_contact" 
                label="Mother's Contact" 
                :value="old('mother_contact', $existingApplication->mother_contact ?? '')" 
            />

            <x-student.ui.input 
                name="mother_occupation" 
                label="Mother's Occupation" 
                :value="old('mother_occupation', $existingApplication->mother_occupation ?? '')" 
            />

            <x-student.ui.input 
                name="mother_company" 
                label="Mother's Company" 
                :value="old('mother_company', $existingApplication->mother_company ?? '')" 
            />

            <x-student.ui.input 
                name="mother_company_address" 
                label="Mother's Company Address" 
                :value="old('mother_company_address', $existingApplication->mother_company_address ?? '')" 
            />

            <x-student.ui.input 
                name="mother_employment_status" 
                label="Mother's Employment Status" 
                :value="old('mother_employment_status', $existingApplication->mother_employment_status ?? '')" 
            />
        </div>
    </div>
</section>

