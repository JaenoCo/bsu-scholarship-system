@php
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;

// Check if user is logged in
if (!Session::has('user_id')) {
    return redirect()->route('login');
}

// Get logged-in user
$user = User::find(session('user_id'));

// If user not found, clear session and redirect
if (!$user) {
    Session::flush();
    return redirect()->route('login');
}
@endphp

<!DOCTYPE html>
<html lang="en"
    :class="{ 'dark': darkMode }"
    x-data="{ darkMode: localStorage.getItem('darkMode_{{ $user->id }}') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode_{{ $user->id }}', val))">
<head>
    <script>
        if (localStorage.getItem('darkMode_{{ $user->id }}') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TDP Application Form</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white font-sans py-10 px-4">
  <div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 shadow-md rounded-xl p-8">
    
    @include('student.partials.page-header', [
      'title' => 'TDP Application Form'
    ])

    <!-- Progress Indicator -->
    <div class="mb-8">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-2 flex-1">
          <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
            <div id="progressBar" class="bg-bsu-red h-2.5 rounded-full transition-all duration-300" style="width: 20%"></div>
          </div>
        </div>
        <span id="progressText" class="ml-4 text-sm font-medium text-gray-700 dark:text-gray-300">Stage 1 of 5</span>
      </div>
      <div class="flex justify-center space-x-2">
        <div class="flex space-x-1" id="stageIndicators">
          <div class="w-3 h-3 rounded-full bg-bsu-red stage-indicator" data-stage-indicator="1"></div>
          <div class="w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 stage-indicator" data-stage-indicator="2"></div>
          <div class="w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 stage-indicator" data-stage-indicator="3"></div>
          <div class="w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 stage-indicator" data-stage-indicator="4"></div>
          <div class="w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 stage-indicator" data-stage-indicator="5"></div>
        </div>
      </div>
      <div class="flex justify-center mt-2">
        <div class="flex space-x-8 text-xs text-gray-600 dark:text-gray-400">
          <span>Personal</span>
          <span>School</span>
          <span>Family</span>
          <span>Financial Aid</span>
          <span>Certification</span>
        </div>
      </div>
    </div>

    <form action="{{ url('/student/submit-application') }}" method="POST" id="mainForm" class="space-y-10">
      @csrf
      <!-- TDP Scholarship ID (assuming specific ID or logic needed, but for now generic submission) -->
      <!-- If there's a specific TDP scholarship ID, it should be passed here. For now, we rely on the controller or hidden input if needed. -->
      <!-- Adding a hidden input to identify this as TDP form if needed by backend, though currently backend uses generic submit -->
      <input type="hidden" name="form_type" value="tdp">

      <!-- Stage 1: Personal Data Section -->
      <div class="form-stage" data-stage="1">
      <section class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-8 border border-gray-200 dark:border-gray-700 shadow-sm">
        <h2 class="text-3xl font-bold text-red-800 dark:text-red-400 mb-6 border-b-2 border-red-700 dark:border-red-500 pb-2">Personal Data</h2>
        
        <div class="space-y-5">
        <!-- Row 1: Last Name, First Name, Middle Name -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Last Name: <span class="text-red-500">*</span></label>
            <input type="text" name="last_name" required value="{{ old('last_name', $user->last_name ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">First Name: <span class="text-red-500">*</span></label>
            <input type="text" name="first_name" required value="{{ old('first_name', $user->first_name ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Middle Name:</label>
            <input type="text" name="middle_name" value="{{ old('middle_name', $user->middle_name ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>

        <!-- Row 2: Age, Sex, Civil Status -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-1">
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Age:</label>
            <input type="number" name="age" id="age" value="{{ old('age', $existingApplication->age ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-gray-100 dark:bg-gray-600 dark:text-white transition-colors" readonly title="Age will be automatically calculated when birthdate is entered">
          </div>
          <div class="md:col-span-1">
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Sex:</label>
            <select name="sex" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <option value="">-- Select --</option>
              <option value="Male" {{ old('sex', $user->sex ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
              <option value="Female" {{ old('sex', $user->sex ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Civil Status:</label>
            <select name="civil_status" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <option value="">-- Select --</option>
              <option value="Single" {{ old('civil_status', $existingApplication->civil_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
              <option value="Married" {{ old('civil_status', $existingApplication->civil_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
              <option value="Widowed" {{ old('civil_status', $existingApplication->civil_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
              <option value="Divorced" {{ old('civil_status', $existingApplication->civil_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
              <option value="Separated" {{ old('civil_status', $existingApplication->civil_status ?? '') == 'Separated' ? 'selected' : '' }}>Separated</option>
            </select>
          </div>
        </div>

        <!-- Row 3: Birthdate (mm/dd/yyyy) and Birthplace -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Birthdate:</label>
            @php
              $birthdate = old('birthdate', optional($user)->birthdate?->format('Y-m-d') ?? '');
              $birthMonth = $birthdate ? date('m', strtotime($birthdate)) : '';
              $birthDay = $birthdate ? date('d', strtotime($birthdate)) : '';
              $birthYear = $birthdate ? date('Y', strtotime($birthdate)) : '';
            @endphp
            <div class="flex items-start gap-1">
              <div>
                <input type="number" id="birth_mm" name="birth_mm" min="1" max="12" placeholder="mm" value="{{ $birthMonth }}" class="w-16 border-b-2 border-gray-300 dark:border-gray-600 px-1 py-1 text-center focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors" maxlength="2">
                <label class="block text-xs text-gray-600 dark:text-gray-400 text-center mt-1">mm</label>
              </div>
              <span class="pt-1 text-gray-600 dark:text-gray-400">/</span>
              <div>
                <input type="number" id="birth_dd" name="birth_dd" min="1" max="31" placeholder="dd" value="{{ $birthDay }}" class="w-16 border-b-2 border-gray-300 dark:border-gray-600 px-1 py-1 text-center focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors" maxlength="2">
                <label class="block text-xs text-gray-600 dark:text-gray-400 text-center mt-1">dd</label>
              </div>
              <span class="pt-1 text-gray-600 dark:text-gray-400">/</span>
              <div>
                <input type="number" id="birth_yyyy" name="birth_yyyy" min="1900" max="2010" placeholder="yyyy" value="{{ $birthYear }}" class="w-20 border-b-2 border-gray-300 dark:border-gray-600 px-1 py-1 text-center focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors" maxlength="4">
                <label class="block text-xs text-gray-600 dark:text-gray-400 text-center mt-1">yyyy</label>
              </div>
              <input type="hidden" name="birthdate" id="birthdate" value="{{ $birthdate }}">
            </div>
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Birthplace:</label>
            <input type="text" name="birthplace" value="{{ old('birthplace', $existingApplication->birthplace ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>

        <!-- Row 4: Email Address and Contact Number -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Email Address:</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Contact Number:</label>
            <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>

        <!-- Row 5: Permanent Home Address -->
        <div class="space-y-2">
          <label class="block font-medium text-gray-700 dark:text-gray-300">Permanent Home Address:</label>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <input type="text" name="street_barangay" value="{{ old('street_barangay', $existingApplication->street_barangay ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none text-center bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <label class="block text-sm text-gray-600 dark:text-gray-400 text-center mt-1">Street / Barangay</label>
            </div>
            <div>
              <input type="text" name="town_city" value="{{ old('town_city', $existingApplication->town_city ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none text-center bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <label class="block text-sm text-gray-600 dark:text-gray-400 text-center mt-1">Town / City / Municipality</label>
            </div>
            <div>
              <input type="text" name="province" value="{{ old('province', $existingApplication->province ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none text-center bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <label class="block text-sm text-gray-600 dark:text-gray-400 text-center mt-1">Province</label>
            </div>
          </div>
        </div>

        <!-- Row 6: Zip Code and Citizenship -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Zip Code:</label>
            <input type="text" name="zip_code" maxlength="4" pattern="\d{4}" value="{{ old('zip_code', $existingApplication->zip_code ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 text-center focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors" title="ZIP Code must be 4 digits">
          </div>
          <div class="md:col-span-2">
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Citizenship:</label>
            <input type="text" name="citizenship" value="{{ old('citizenship', $existingApplication->citizenship ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>

        <!-- Row 7: Type of Disability and Tribal Membership -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Type of Disability (if applicable):</label>
            <input type="text" name="disability" placeholder="If Applicable" value="{{ old('disability', $existingApplication->disability ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Tribal Membership:</label>
            <input type="text" name="tribe" value="{{ old('tribe', $existingApplication->tribe ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>
        </div>
      </section>
      </div>

      <!-- Stage 2: School Information Section -->
      <div class="form-stage hidden" data-stage="2">
      <section class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-8 border border-gray-200 dark:border-gray-700 shadow-sm">
        <h2 class="text-3xl font-bold text-red-800 dark:text-red-400 mb-6 border-b-2 border-red-700 dark:border-red-500 pb-2">School Information</h2>
        
        <div class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-1">
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">SR Code</label>
            <input type="text" name="sr_code" 
              value="{{ old('sr_code', $user->sr_code ?? '') }}"
              class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>

          <div class="md:col-span-3">
             <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Name of School</label>
             <input type="text" value="Batangas State University" readonly class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 bg-gray-100 dark:bg-gray-600 dark:text-white transition-colors cursor-not-allowed">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Program</label>
            <select name="program" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
                <option value="">-- Select Program --</option>
                @foreach (['BS Computer Science', 'BS Information Technology', 'BS Computer Engineering', 'BS Electronics Engineering', 'BS Civil Engineering', 'BS Mechanical Engineering', 'BS Electrical Engineering', 'BS Industrial Engineering', 'BS Accountancy', 'BS Business Administration', 'BS Tourism Management', 'BS Hospitality Management', 'BS Psychology', 'BS Education', 'BS Nursing', 'BS Medical Technology', 'BS Pharmacy', 'BS Biology', 'BS Chemistry', 'BS Mathematics', 'BS Physics', 'BS Environmental Science', 'BS Agriculture', 'BS Fisheries', 'BS Forestry', 'BS Architecture', 'BS Interior Design', 'BS Fine Arts', 'BS Communication', 'BS Social Work', 'BS Criminology', 'BS Political Science', 'BS History', 'BS Literature', 'BS Philosophy', 'BS Economics', 'BS Sociology', 'BS Anthropology'] as $program)
                    <option value="{{ $program }}" 
                        {{ old('program', $user->program ?? '') == $program ? 'selected' : '' }}>
                        {{ $program }}
                    </option>
                @endforeach
            </select>
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">College</label>
            <select name="college" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
                <option value="">-- Select College --</option>
                <option value="CICS" {{ old('college', $user->college ?? '') == 'CICS' ? 'selected' : '' }}>CICS</option>
                <option value="CTE" {{ old('college', $user->college ?? '') == 'CTE' ? 'selected' : '' }}>CTE</option>
                <option value="CABEIHM" {{ old('college', $user->college ?? '') == 'CABEIHM' ? 'selected' : '' }}>CABEIHM</option>
                <option value="CAS" {{ old('college', $user->college ?? '') == 'CAS' ? 'selected' : '' }}>CAS</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Grade/Year Level</label>
            <select name="year_level" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
                <option value="">-- Select Grade/Year Level --</option>
                <option value="1st Year" {{ old('year_level', $user->year_level ?? '') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                <option value="2nd Year" {{ old('year_level', $user->year_level ?? '') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                <option value="3rd Year" {{ old('year_level', $user->year_level ?? '') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                <option value="4th Year" {{ old('year_level', $user->year_level ?? '') == '4th Year' ? 'selected' : '' }}>4th Year</option>
            </select>
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Campus</label>
            <select name="campus_id" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <option value="">-- Select Campus --</option>
              @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" {{ old('campus_id', $user->campus_id) == $campus->id ? 'selected' : '' }}>
                  {{ $campus->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Previous GWA</label>
            <input type="number" name="previous_gwa" step="0.01" min="1.00" max="5.00" 
                   placeholder="0.00"
                   value="{{ old('previous_gwa', $existingApplication->previous_gwa ?? '') }}"
                   class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
        </div>
        </div>
        </div>
      </section>
      </div>

      <!-- Stage 3: Family Data Section -->
      <div class="form-stage hidden" data-stage="3">
      <section class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-8 border border-gray-200 dark:border-gray-700 shadow-sm">
        <h2 class="text-3xl font-bold text-red-800 dark:text-red-400 mb-6 border-b-2 border-red-700 dark:border-red-500 pb-2">Family Data</h2>
        
        <div class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Father Section -->
        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-4">Father's Information</h3>
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Father's Name:</label>
              <input type="text" name="father_name" value="{{ old('father_name', $existingApplication->father_name ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Father's Address:</label>
              <input type="text" name="father_address" value="{{ old('father_address', $existingApplication->father_address ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Father's Occupation:</label>
              <input type="text" name="father_occupation" value="{{ old('father_occupation', $existingApplication->father_occupation ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>
          </div>
        </div>

        <!-- Mother Section -->
        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
          <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-4">Mother's Information</h3>
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Mother's Name:</label>
              <input type="text" name="mother_name" value="{{ old('mother_name', $existingApplication->mother_name ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Mother's Address:</label>
              <input type="text" name="mother_address" value="{{ old('mother_address', $existingApplication->mother_address ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Mother's Occupation:</label>
              <input type="text" name="mother_occupation" value="{{ old('mother_occupation', $existingApplication->mother_occupation ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>
          </div>
        </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Estimated Gross Annual Income Section -->
          <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
            <label class="block font-medium mb-3 text-gray-700 dark:text-gray-300">Estimated gross annual income:</label>
            <div class="space-y-3">
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="estimated_gross_annual_income" value="not_over_250000" {{ old('estimated_gross_annual_income', $existingApplication->estimated_gross_annual_income ?? '') == 'not_over_250000' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Not over P 250,000.00</span>
              </label>
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="estimated_gross_annual_income" value="over_250000_not_over_400000" {{ old('estimated_gross_annual_income', $existingApplication->estimated_gross_annual_income ?? '') == 'over_250000_not_over_400000' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Over P 250,000 but not over P 400,000</span>
              </label>
              <!-- Add more options if needed -->
            </div>
          </div>
        </div>
        </div>
      </section>
      </div>

      <!-- Stage 4: Financial Assistance Section -->
      <div class="form-stage hidden" data-stage="4">
      <section class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-8 border border-gray-200 dark:border-gray-700 shadow-sm">
        <h2 class="text-3xl font-bold text-red-800 dark:text-red-400 mb-6 border-b-2 border-red-700 dark:border-red-500 pb-2">Other Financial Assistance</h2>
        <div>
          <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">Do you have existing scholarships or financial assistance?</label>
          <div class="flex items-center gap-6 mt-1 mb-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="has_existing_scholarship" value="1" onchange="toggleScholarshipDetails()"
                {{ old('has_existing_scholarship', $existingApplication->has_existing_scholarship ?? '') == 1 ? 'checked' : '' }}
                class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
              <span class="text-gray-700 dark:text-gray-300">Yes</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="has_existing_scholarship" value="0" onchange="toggleScholarshipDetails()"
                {{ old('has_existing_scholarship', $existingApplication->has_existing_scholarship ?? '') == 0 ? 'checked' : '' }}
                class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
              <span class="text-gray-700 dark:text-gray-300">No</span>
            </label>
          </div>

          <div id="scholarship_details_container">
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">If Yes, please specify:</label>
            <input type="text" name="existing_scholarship_details" id="existing_scholarship_details"
              value="{{ old('existing_scholarship_details', $existingApplication->existing_scholarship_details ?? '') }}"
              class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>
      </section>
      </div>

      <!-- Stage 5: Certification Section -->
      <div class="form-stage hidden" data-stage="5">
      <section class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-8 border border-gray-200 dark:border-gray-700 shadow-sm">
        <h2 class="text-3xl font-bold text-red-800 dark:text-red-400 mb-6 border-b-2 border-red-700 dark:border-red-500 pb-2">Certification</h2>
        <div class="space-y-4">
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-800">
              <strong>Note:</strong> By submitting this form, you certify that all information provided is true and accurate. 
              Any false information may result in disqualification from scholarship consideration.
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-medium mb-1 text-gray-700 dark:text-gray-300">Student Signature</label>
              <input type="text" name="student_signature" placeholder="Type your full name as digital signature" value="{{ old('student_signature', $existingApplication->student_signature ?? '') }}" class="w-full border-2 border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Type your full name as your digital signature</p>
            </div>

            <div>
              <label class="block font-medium mb-1 text-gray-700 dark:text-gray-300">Date Signed</label>
              <input type="date" name="date_signed" value="{{ old('date_signed', optional($existingApplication)->date_signed?->format('Y-m-d') ?? date('Y-m-d')) }}" class="w-full border-2 border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>
          </div>
        </div>
      </section>
      </div>

      <!-- Navigation Buttons -->
      <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <button type="button" id="backBtn" onclick="previousStage()" class="hidden flex items-center gap-2 bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition duration-300 font-semibold shadow-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back
        </button>
        <div class="flex-1"></div>
        <button type="button" id="nextBtn" onclick="nextStage()" class="flex items-center gap-2 bg-bsu-red text-white px-6 py-3 rounded-lg hover:bg-red-700 transition duration-300 font-semibold shadow-lg">
          Next
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <button type="submit" id="completeBtn" form="mainForm" onclick="removePrintFlag()" class="hidden flex items-center gap-2 bg-bsu-red text-white px-8 py-3 rounded-lg hover:bg-red-700 transition duration-300 text-lg font-semibold shadow-lg">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
          </svg>
          Complete Application
        </button>
      </div>
    </form>
  </div>

  <!-- Unsaved Changes Modal -->
  <div id="unsavedChangesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4" onclick="if(event.target === this) hideUnsavedChangesModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 z-[10000]" onclick="event.stopPropagation()">
      <div class="flex items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Unsaved Changes</h3>
      </div>
      <p class="text-gray-600 dark:text-gray-300 mb-6">
        Changes have been made to the form. Do you want to save the current changes before leaving?
      </p>
      <div class="flex justify-end space-x-3">
        <button type="button" id="cancelLeaveBtn" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
          Cancel
        </button>
        <button type="button" id="discardChangesBtn" class="px-4 py-2 text-sm font-medium text-white bg-gray-600 dark:bg-gray-500 rounded-lg hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
          Discard Changes
        </button>
        <button type="button" id="saveChangesBtn" class="px-4 py-2 text-sm font-medium text-white bg-bsu-red rounded-lg hover:bg-red-700 transition-colors">
          Save Changes
        </button>
      </div>
    </div>
  </div>

  <script>
    // Form change tracking
    let formHasChanges = false;
    let initialFormValues = {};
    let pendingNavigationUrl = null;

    // Track form changes
    function trackFormChanges() {
      const form = document.getElementById('mainForm');
      if (!form) return;

      // Get initial form values
      const inputs = form.querySelectorAll('input, select, textarea');
      inputs.forEach(input => {
        const name = input.name;
        if (name) {
          if (input.type === 'checkbox' || input.type === 'radio') {
            initialFormValues[name] = input.checked;
          } else {
            initialFormValues[name] = input.value || '';
          }
        }
      });

      // Track all form inputs
      inputs.forEach(input => {
        const trackChange = () => {
          formHasChanges = true;
          console.log('Form change detected on:', input.name);
        };
        
        input.addEventListener('input', trackChange);
        input.addEventListener('change', trackChange);
        
        // For radio buttons and checkboxes
        if (input.type === 'radio' || input.type === 'checkbox') {
          input.addEventListener('click', trackChange);
        }
      });
      
      console.log('Form change tracking initialized, inputs:', inputs.length);
    }

    // Check if form has changes
    function hasFormChanges() {
      return formHasChanges;
    }

    // Show unsaved changes modal
    function showUnsavedChangesModal(navigationUrl) {
      pendingNavigationUrl = navigationUrl;
      const modal = document.getElementById('unsavedChangesModal');
      if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'flex'; // Force display
      }
    }

    // Hide unsaved changes modal
    function hideUnsavedChangesModal() {
      const modal = document.getElementById('unsavedChangesModal');
      if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none'; // Force hide
      }
      pendingNavigationUrl = null;
    }
    window.hideUnsavedChangesModal = hideUnsavedChangesModal;

    // Save form and navigate
    function saveAndNavigate() {
      const form = document.getElementById('mainForm');
      if (!form) return;

      // Remove print flag for save
      const printInput = document.getElementById('printAfterSave');
      if (printInput) {
        printInput.remove();
      }

      const saveAndNavigateInput = document.createElement('input');
      saveAndNavigateInput.type = 'hidden';
      saveAndNavigateInput.name = 'save_and_navigate';
      saveAndNavigateInput.value = pendingNavigationUrl || '{{ route("student.dashboard") }}';
      form.appendChild(saveAndNavigateInput);
      
      formHasChanges = false;
      form.submit();
    }

    // Navigate without saving
    function navigateWithoutSaving() {
      formHasChanges = false;
      window.location.href = pendingNavigationUrl || '{{ route("student.dashboard") }}';
    }

    // Multi-stage form navigation
    let currentStage = 1;
    const totalStages = 5;

    function showStage(stage) {
      document.querySelectorAll('.form-stage').forEach(s => s.classList.add('hidden'));
      const currentStageElement = document.querySelector(`.form-stage[data-stage="${stage}"]`);
      if (currentStageElement) currentStageElement.classList.remove('hidden');
      
      const progress = (stage / totalStages) * 100;
      document.getElementById('progressBar').style.width = progress + '%';
      document.getElementById('progressText').textContent = `Stage ${stage} of ${totalStages}`;
      
      document.querySelectorAll('.stage-indicator').forEach((indicator, index) => {
        if (index + 1 <= stage) {
          indicator.classList.remove('bg-gray-300', 'dark:bg-gray-600');
          indicator.classList.add('bg-bsu-red');
        } else {
          indicator.classList.remove('bg-bsu-red');
          indicator.classList.add('bg-gray-300', 'dark:bg-gray-600');
        }
      });
      
      const backBtn = document.getElementById('backBtn');
      const nextBtn = document.getElementById('nextBtn');
      const completeBtn = document.getElementById('completeBtn');
      
      if (stage === 1) backBtn.classList.add('hidden'); else backBtn.classList.remove('hidden');
      if (stage === totalStages) {
        nextBtn.classList.add('hidden');
        completeBtn.classList.remove('hidden');
      } else {
        nextBtn.classList.remove('hidden');
        completeBtn.classList.add('hidden');
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function nextStage() {
      if (validateCurrentStage()) {
        if (currentStage < totalStages) {
          currentStage++;
          showStage(currentStage);
        }
      }
    }

    function previousStage() {
      if (currentStage > 1) {
        currentStage--;
        showStage(currentStage);
      }
    }

    function validateCurrentStage() {
      const currentStageElement = document.querySelector(`.form-stage[data-stage="${currentStage}"]`);
      if (!currentStageElement) return true;
      const requiredFields = currentStageElement.querySelectorAll('[required]');
      let isValid = true;
      requiredFields.forEach(field => {
        if (!field.value || field.value.trim() === '') {
          isValid = false;
          field.classList.add('border-red-500', 'ring-2', 'ring-red-300');
          field.addEventListener('input', function() {
            this.classList.remove('border-red-500', 'ring-2', 'ring-red-300');
          }, { once: true });
        }
      });
      if (!isValid) {
        alert('Please fill in all required fields before proceeding.');
        const firstInvalid = currentStageElement.querySelector('[required]:invalid, [required].border-red-500');
        if (firstInvalid) {
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstInvalid.focus();
        }
      }
      return isValid;
    }

    function removePrintFlag() {
      const printInput = document.getElementById('printAfterSave');
      if (printInput) printInput.remove();
    }

    function toggleScholarshipDetails() {
      const yesRadio = document.querySelector('input[name="has_existing_scholarship"][value="1"]');
      const detailsContainer = document.getElementById('scholarship_details_container');
      const detailsInput = document.getElementById('existing_scholarship_details');
      
      if (yesRadio && yesRadio.checked) {
        detailsContainer.style.display = 'block';
        detailsInput.disabled = false;
        detailsInput.required = true;
      } else if (detailsContainer) {
        detailsContainer.style.display = 'none';
        detailsInput.disabled = true;
        detailsInput.required = false;
        detailsInput.value = '';
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      trackFormChanges();
      
      // Verify modal exists
      const modal = document.getElementById('unsavedChangesModal');
      if (!modal) console.error('CRITICAL: Unsaved changes modal not found in DOM!');

      // Intercept back to dashboard link
      let backToDashboardLink = document.getElementById('backToDashboardLink');
      if (!backToDashboardLink) {
        const links = document.querySelectorAll('a[href*="dashboard"]');
        if (links.length > 0) backToDashboardLink = links[0];
      }
      
      if (backToDashboardLink) {
        backToDashboardLink.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          if (hasFormChanges()) {
            showUnsavedChangesModal(this.href);
          } else {
            window.location.href = this.href;
          }
        });
      }

      const cancelBtn = document.getElementById('cancelLeaveBtn');
      const discardBtn = document.getElementById('discardChangesBtn');
      const saveBtn = document.getElementById('saveChangesBtn');

      if (cancelBtn) cancelBtn.addEventListener('click', hideUnsavedChangesModal);
      if (discardBtn) discardBtn.addEventListener('click', navigateWithoutSaving);
      if (saveBtn) saveBtn.addEventListener('click', saveAndNavigate);

      const form = document.getElementById('mainForm');
      if (form) form.addEventListener('submit', () => formHasChanges = false);

      window.addEventListener('beforeunload', function(e) {
        if (hasFormChanges()) {
          e.preventDefault();
          e.returnValue = '';
          return '';
        }
      });

      const urlParams = new URLSearchParams(window.location.search);
      const stageParam = urlParams.get('stage');
      if (stageParam && parseInt(stageParam) >= 1 && parseInt(stageParam) <= totalStages) {
        currentStage = parseInt(stageParam);
        showStage(currentStage);
      } else {
        showStage(1);
      }
      
      toggleScholarshipDetails();
    });

    // Birthdate logic (simplified for brevity)
    const birthMonthInput = document.getElementById('birth_mm');
    const birthDayInput = document.getElementById('birth_dd');
    const birthYearInput = document.getElementById('birth_yyyy');
    const birthdateHiddenInput = document.getElementById('birthdate');
    const ageInput = document.getElementById('age');

    function updateBirthdate() {
      const month = birthMonthInput.value;
      const day = birthDayInput.value;
      const year = birthYearInput.value;
      if (month && day && year && year.length === 4) {
        const dateString = `${year}-${month.padStart(2,'0')}-${day.padStart(2,'0')}`;
        birthdateHiddenInput.value = dateString;
        // Simple age calc
        const birthDate = new Date(dateString);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        ageInput.value = age > 0 ? age : '';
      }
    }
    if(birthMonthInput) {
        birthMonthInput.addEventListener('input', updateBirthdate);
        birthDayInput.addEventListener('input', updateBirthdate);
        birthYearInput.addEventListener('input', updateBirthdate);
    }
  </script>
</body>
</html>
