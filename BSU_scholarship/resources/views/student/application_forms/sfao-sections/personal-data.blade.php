<section>
    <h2 class="text-3xl font-bold text-red-800 mb-6 border-b-2 border-red-700 pb-2">Personal Data</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Name Fields -->
        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-student.ui.input 
                name="last_name" 
                label="Last Name" 
                :value="old('last_name', $existingApplication->last_name ?? '')" 
                required 
            />
            <x-student.ui.input 
                name="first_name" 
                label="First Name" 
                :value="old('first_name', $existingApplication->first_name ?? '')" 
                required 
            />
            <x-student.ui.input 
                name="middle_name" 
                label="Middle Name" 
                :value="old('middle_name', $existingApplication->middle_name ?? '')" 
            />
        </div>

        <!-- Address Fields -->
        <div class="md:col-span-2 grid grid-cols-7 gap-4">
            <div class="col-span-2">
                <x-student.ui.input 
                    name="street_barangay" 
                    label="Street / Barangay" 
                    :value="old('street_barangay', $existingApplication->street_barangay ?? '')" 
                />
            </div>
            <div class="col-span-2">
                <x-student.ui.input 
                    name="town_city" 
                    label="Town / City / Municipality" 
                    :value="old('town_city', $existingApplication->town_city ?? '')" 
                />
            </div>
            <div class="col-span-2">
                <x-student.ui.input 
                    name="province" 
                    label="Province" 
                    :value="old('province', $existingApplication->province ?? '')" 
                />
            </div>
            <div class="col-span-1">
                <x-student.ui.input 
                    name="zip_code" 
                    label="ZIP Code" 
                    type="text"
                    maxlength="4"
                    pattern="\d{4}"
                    :value="old('zip_code', $existingApplication->zip_code ?? '')" 
                    placeholder="1234"
                />
            </div>
        </div>

        <!-- Personal Information -->
        <x-student.ui.input 
            name="age" 
            label="Age" 
            type="number"
            id="age"
            :value="old('age', $existingApplication->age ?? '')" 
            readonly
        />
        <x-student.ui.input 
            name="sex" 
            label="Sex" 
            :value="old('sex', $existingApplication->sex ?? '')" 
        />
        <x-student.ui.input 
            name="civil_status" 
            label="Civil Status" 
            :value="old('civil_status', $existingApplication->civil_status ?? '')" 
        />
        <x-student.ui.input 
            name="birthdate" 
            label="Birthdate" 
            type="date"
            id="birthdate"
            :value="old('birthdate', optional($existingApplication)->birthdate?->format('Y-m-d') ?? '')" 
        />
        <x-student.ui.input 
            name="birthplace" 
            label="Birthplace" 
            :value="old('birthplace', $existingApplication->birthplace ?? '')" 
        />
        <x-student.ui.input 
            name="disability" 
            label="Disability" 
            :value="old('disability', $existingApplication->disability ?? '')" 
        />
        <x-student.ui.input 
            name="tribe" 
            label="Tribe" 
            :value="old('tribe', $existingApplication->tribe ?? '')" 
        />
        <x-student.ui.input 
            name="citizenship" 
            label="Citizenship" 
            :value="old('citizenship', $existingApplication->citizenship ?? '')" 
        />
        <x-student.ui.input 
            name="birth_order" 
            label="Birth Order" 
            :value="old('birth_order', $existingApplication->birth_order ?? '')" 
        />
        <x-student.ui.input 
            name="email" 
            label="Email" 
            type="email"
            :value="old('email', $existingApplication->email ?? '')" 
        />
        <x-student.ui.input 
            name="telephone" 
            label="Telephone" 
            :value="old('telephone', $existingApplication->telephone ?? '')" 
        />
        <x-student.ui.input 
            name="religion" 
            label="Religion" 
            :value="old('religion', $existingApplication->religion ?? '')" 
        />
    </div>
</section>

