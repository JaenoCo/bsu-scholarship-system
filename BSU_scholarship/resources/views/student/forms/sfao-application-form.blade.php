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
  <title>Scholarship Application Form</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white font-sans">
    
    <!-- Custom Navbar -->
    <x-layout.navbar 
        :user="$user" 
        title="Scholarship Application Form"
        :sidebar="false"
        :logout="true"
        :profile="false"
        :settings="true"
        back-url="#"
        back-text="Back to Dashboard"
        onclick="event.preventDefault(); showUnsavedChangesModal('{{ route('student.dashboard') }}')"
    />

  <div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 shadow-md rounded-xl p-8 mt-8 mb-12">
    


    <!-- Error Display -->
    @if ($errors->any())
      <div class="mb-6 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-md animate-pulse">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
              Please correct the following errors:
            </h3>
            <div class="mt-2 text-sm text-red-700 dark:text-red-300">
              <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>
    @endif

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
          <span>Academic</span>
          <span>Family</span>
          <span>Essay</span>
          <span>Certification</span>
        </div>
      </div>
    </div>

    <form action="{{ url('/student/submit-application') }}" method="POST" id="mainForm" class="space-y-10">
      @csrf
      @if(isset($scholarship))
        <input type="hidden" name="scholarship_id" value="{{ $scholarship->id }}">
      @endif

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
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Sex: <span class="text-red-500">*</span></label>
            <select name="sex" required class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <option value="">-- Select --</option>
              <option value="Male" {{ old('sex', $user->sex ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
              <option value="Female" {{ old('sex', $user->sex ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Civil Status: <span class="text-red-500">*</span></label>
            <select name="civil_status" required class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
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
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Email Address: <span class="text-red-500">*</span></label>
            <input type="email" name="email" required value="{{ old('email', $user->email ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Contact Number: <span class="text-red-500">*</span></label>
            <input type="text" name="contact_number" required value="{{ old('contact_number', $user->contact_number ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>

        <!-- Row 5: Permanent Home Address -->
        <div class="space-y-2">
          <label class="block font-medium text-gray-700 dark:text-gray-300">Permanent Home Address: <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <input type="text" name="street_barangay" required value="{{ old('street_barangay', $existingApplication->street_barangay ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none text-center bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <label class="block text-sm text-gray-600 dark:text-gray-400 text-center mt-1">Street / Barangay</label>
            </div>
            <div>
              <input type="text" name="town_city" required value="{{ old('town_city', $existingApplication->town_city ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none text-center bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <label class="block text-sm text-gray-600 dark:text-gray-400 text-center mt-1">Town / City / Municipality</label>
            </div>
            <div>
              <input type="text" name="province" required value="{{ old('province', $existingApplication->province ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none text-center bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <label class="block text-sm text-gray-600 dark:text-gray-400 text-center mt-1">Province</label>
            </div>
          </div>
        </div>

        <!-- Row 6: Zip Code and Citizenship -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Zip Code: <span class="text-red-500">*</span></label>
            <input type="text" name="zip_code" required value="{{ old('zip_code', $existingApplication->zip_code ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 text-center focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors" title="ZIP Code must be 4 digits">
          </div>
          <div class="md:col-span-2">
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Citizenship: <span class="text-red-500">*</span></label>
            <input type="text" name="citizenship" required value="{{ old('citizenship', $existingApplication->citizenship ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
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

      <!-- Stage 2: Academic Data Section -->
      <div class="form-stage hidden" data-stage="2">
      <section class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-8 border border-gray-200 dark:border-gray-700 shadow-sm"
               x-data="academicDropdowns">
        <h2 class="text-3xl font-bold text-red-800 dark:text-red-400 mb-6 border-b-2 border-red-700 dark:border-red-500 pb-2">Academic Data</h2>
        
        <div class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-1">
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">SR Code <span class="text-red-500">*</span></label>
            <input type="text" name="sr_code" required
              value="{{ old('sr_code', $user->sr_code ?? '') }}"
              class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>

          <div class="md:col-span-3">
            <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">Educational Level <span class="text-red-500">*</span></label>
            <div class="flex flex-wrap items-center justify-between w-full">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="education_level" required value="Undergraduate" {{ old('education_level', $user->education_level ?? '') == 'Undergraduate' ? 'checked' : '' }} class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Undergraduate</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="education_level" value="Graduate School" {{ old('education_level', $user->education_level ?? '') == 'Graduate School' ? 'checked' : '' }} class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Graduate School</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="education_level" value="Integrated School" {{ old('education_level', $user->education_level ?? '') == 'Integrated School' ? 'checked' : '' }} class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Integrated School</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Campus (Hidden) / College / Year Level Row -->
        <input type="hidden" name="campus_id" x-model="selectedCampus">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- College -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">College <span class="text-red-500">*</span></label>
                <select name="college" x-model="selectedCollege" @change="updateCollege()" required class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors disabled:opacity-50" :disabled="!selectedCampus">
                    <option value="">-- Select College --</option>
                    <template x-for="college in colleges" :key="college">
                        <option :value="college" x-text="college"></option>
                    </template>
                </select>
            </div>
            
            <!-- Year Level -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Grade/Year Level <span class="text-red-500">*</span></label>
                <select name="year_level" required class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
                    <option value="">-- Select Grade/Year Level --</option>
                    <option value="1st Year" {{ old('year_level', $user->year_level ?? '') == '1st Year' ? 'selected' : '' }}>1st Year</option>
                    <option value="2nd Year" {{ old('year_level', $user->year_level ?? '') == '2nd Year' ? 'selected' : '' }}>2nd Year</option>
                    <option value="3rd Year" {{ old('year_level', $user->year_level ?? '') == '3rd Year' ? 'selected' : '' }}>3rd Year</option>
                    <option value="4th Year" {{ old('year_level', $user->year_level ?? '') == '4th Year' ? 'selected' : '' }}>4th Year</option>
                </select>
            </div>
        </div>

        <!-- Program / Track Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Program -->
            <div>
                <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Program <span class="text-red-500">*</span></label>
                <select name="program" x-model="selectedProgram" @change="updateProgram()" required class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors disabled:opacity-50" :disabled="!selectedCollege">
                    <option value="">-- Select Program --</option>
                    <template x-for="prog in programs" :key="prog.name">
                        <option :value="prog.name" x-text="prog.name"></option>
                    </template>
                </select>
            </div>

            <!-- Track -->
            <div>
                 <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Track / Major <span x-show="tracks.length > 0" class="text-red-500">*</span></label>
                 <select name="track" x-model="selectedTrack" :required="tracks.length > 0" :disabled="!selectedProgram || tracks.length === 0" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">-- Select Track --</option>
                    <template x-for="track of tracks" :key="track">
                        <option :value="track" x-text="track"></option>
                    </template>
                    <option value="No Track Yet" x-show="tracks.length > 0">No Track Yet</option>
                 </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
           <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Previous GWA <span class="text-red-500">*</span></label>
            <input type="number" name="previous_gwa" required step="0.01" min="1.00" max="5.00" 
                   placeholder="0.00"
                   value="{{ old('previous_gwa', $existingApplication->previous_gwa ?? '') }}"
                   class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
           </div>
           <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Units Enrolled <span class="text-red-500">*</span></label>
            <input type="number" name="units_enrolled" required min="1" max="30"
              value="{{ old('units_enrolled', $existingApplication->units_enrolled ?? '') }}"
              class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
           <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Honors Received</label>
            <input type="text" name="honors_received" placeholder="If any"
              value="{{ old('honors_received', $existingApplication->honors_received ?? '') }}"
              class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Scholarship Applied</label>
            @if(isset($scholarship))
              <input type="text" name="scholarship_applied" 
                value="{{ old('scholarship_applied', $scholarship->scholarship_name) }}"
                readonly
                class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-gray-100 dark:bg-gray-600 dark:text-white transition-colors cursor-not-allowed">
            @else
              <input type="text" name="scholarship_applied" 
                value=""
                disabled
                class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-gray-100 dark:bg-gray-600 dark:text-gray-400 transition-colors cursor-not-allowed">
            @endif
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Semester <span class="text-red-500">*</span></label>
            <input type="text" name="semester" required placeholder="e.g., 1st Semester"
              value="{{ old('semester', $existingApplication->semester ?? '') }}"
              class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
          <div>
            <label class="block mb-1 font-medium text-gray-700 dark:text-gray-300">Academic Year <span class="text-red-500">*</span></label>
            <input type="text" name="academic_year" required placeholder="e.g., 2025-2026"
              value="{{ old('academic_year', $existingApplication->academic_year ?? '') }}"
              class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>

        <div>
          <label class="block mb-2 font-medium text-gray-700 dark:text-gray-300">Do you have existing scholarships? <span class="text-red-500">*</span></label>
          <div class="flex items-center gap-6 mt-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="radio" name="has_existing_scholarship" required value="1" onchange="toggleScholarshipDetails()"
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
              <label class="font-medium text-gray-700 dark:text-gray-300">Father Status: <span class="text-red-500">*</span></label>
              <div class="col-span-1 md:col-span-2 flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" name="father_status" required value="living" {{ old('father_status', $existingApplication->father_status ?? '') == 'living' ? 'checked' : '' }} class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                  <span class="text-gray-700 dark:text-gray-300">Living</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" name="father_status" value="deceased" {{ old('father_status', $existingApplication->father_status ?? '') == 'deceased' ? 'checked' : '' }} class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                  <span class="text-gray-700 dark:text-gray-300">Deceased</span>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Father's Name: <span class="text-red-500">*</span></label>
              <input type="text" name="father_name" required value="{{ old('father_name', $existingApplication->father_name ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Father's Address:</label>
              <input type="text" name="father_address" value="{{ old('father_address', $existingApplication->father_address ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Father's Contact:</label>
              <input type="text" name="father_contact" value="{{ old('father_contact', $existingApplication->father_contact ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
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
              <label class="font-medium text-gray-700 dark:text-gray-300">Mother Status: <span class="text-red-500">*</span></label>
              <div class="col-span-1 md:col-span-2 flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" name="mother_status" required value="living" {{ old('mother_status', $existingApplication->mother_status ?? '') == 'living' ? 'checked' : '' }} class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                  <span class="text-gray-700 dark:text-gray-300">Living</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" name="mother_status" value="deceased" {{ old('mother_status', $existingApplication->mother_status ?? '') == 'deceased' ? 'checked' : '' }} class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                  <span class="text-gray-700 dark:text-gray-300">Deceased</span>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Mother's Name: <span class="text-red-500">*</span></label>
              <input type="text" name="mother_name" required value="{{ old('mother_name', $existingApplication->mother_name ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Mother's Address:</label>
              <input type="text" name="mother_address" value="{{ old('mother_address', $existingApplication->mother_address ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
              <label class="font-medium text-gray-700 dark:text-gray-300">Mother's Contact:</label>
              <input type="text" name="mother_contact" value="{{ old('mother_contact', $existingApplication->mother_contact ?? '') }}" class="col-span-1 md:col-span-2 w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
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
            <label class="block font-medium mb-3 text-gray-700 dark:text-gray-300">Estimated gross annual income: <span class="text-red-500">*</span></label>
            <div class="space-y-3">
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="estimated_gross_annual_income" required value="not_over_250000" {{ old('estimated_gross_annual_income', $existingApplication->estimated_gross_annual_income ?? '') == 'not_over_250000' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Not over P 250,000.00</span>
              </label>
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="estimated_gross_annual_income" value="over_250000_not_over_400000" {{ old('estimated_gross_annual_income', $existingApplication->estimated_gross_annual_income ?? '') == 'over_250000_not_over_400000' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Over P 250,000 but not over P 400,000</span>
              </label>
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="estimated_gross_annual_income" value="over_400000_not_over_800000" {{ old('estimated_gross_annual_income', $existingApplication->estimated_gross_annual_income ?? '') == 'over_400000_not_over_800000' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Over P 400,000 but not over P 800,000</span>
              </label>
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="estimated_gross_annual_income" value="over_800000_not_over_2000000" {{ old('estimated_gross_annual_income', $existingApplication->estimated_gross_annual_income ?? '') == 'over_800000_not_over_2000000' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Over P 800,000 but not over P 2,000,000</span>
              </label>
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="estimated_gross_annual_income" value="over_2000000_not_over_8000000" {{ old('estimated_gross_annual_income', $existingApplication->estimated_gross_annual_income ?? '') == 'over_2000000_not_over_8000000' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Over P 2,000,000 but not over P 8,000,000</span>
              </label>
              <label class="flex items-start gap-2 cursor-pointer">
                <input type="radio" name="estimated_gross_annual_income" value="over_8000000" {{ old('estimated_gross_annual_income', $existingApplication->estimated_gross_annual_income ?? '') == 'over_8000000' ? 'checked' : '' }} class="mt-1 w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                <span class="text-gray-700 dark:text-gray-300">Over P 8,000,000</span>
              </label>
            </div>
          </div>

          <!-- Number of Siblings -->
          <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
            <label class="block font-medium mb-1 text-gray-700 dark:text-gray-300">Number of Siblings</label>
            <input type="number" name="siblings_count" value="{{ old('siblings_count', $existingApplication->siblings_count ?? '') }}" class="w-full border-b-2 border-gray-300 dark:border-gray-600 px-2 py-1 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
          </div>
        </div>
        </div>
      </section>
      </div>

      <!-- Stage 4: Essay / Question Section -->
      <div class="form-stage hidden" data-stage="4">
      <section class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-8 border border-gray-200 dark:border-gray-700 shadow-sm">
        <h2 class="text-3xl font-bold text-red-800 dark:text-red-400 mb-6 border-b-2 border-red-700 dark:border-red-500 pb-2">Essay / Question</h2>
        <div>
          <label class="block font-medium mb-2 text-gray-700 dark:text-gray-300">PLEASE ANSWER THE FOLLOWING QUESTIONS IN YOUR OWN HANDWRITING</label>
          <label class="block font-medium mb-1 text-gray-700 dark:text-gray-300">Reason for Applying</label>
          <textarea name="reason_for_applying" rows="8" 
            class="w-full border-2 border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors"
            placeholder="Please explain your reason for applying for this scholarship...">{{ old('reason_for_applying', $existingApplication->reason_for_applying ?? '') }}</textarea>
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
              <label class="block font-medium mb-1 text-gray-700 dark:text-gray-300">Student Signature <span class="text-red-500">*</span></label>
              <input type="text" name="student_signature" required placeholder="Type your full name as digital signature" value="{{ old('student_signature', $existingApplication->student_signature ?? '') }}" class="w-full border-2 border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Type your full name as your digital signature</p>
            </div>

            <div>
              <label class="block font-medium mb-1 text-gray-700 dark:text-gray-300">Date Signed</label>
              <input type="date" name="date_signed" value="{{ old('date_signed', optional($existingApplication)->date_signed?->format('Y-m-d') ?? date('Y-m-d')) }}" class="w-full border-2 border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 focus:border-red-500 dark:focus:border-red-600 focus:outline-none bg-white dark:bg-gray-700 dark:text-white transition-colors">
            </div>
          </div>

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-semibold text-blue-800 mb-2">Declaration</h4>
            <p class="text-sm text-blue-700">
              I hereby declare that the information provided in this application form is true, complete, and accurate to the best of my knowledge. 
              I understand that any false information may result in the rejection of my application or termination of any scholarship granted.
            </p>
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
          <!-- Save Icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
          </svg>
          Complete Application
        </button>
      </div>
      
      <!-- Hidden input to trigger print after save (only for other submissions, not Complete button) -->
      <input type="hidden" form="mainForm" name="print_after_save" id="printAfterSave" value="1">
    </form>
  </div>

  <!-- Validation Error Modal -->
  <div id="validationErrorModal" class="hidden fixed inset-0 z-[99999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-screen items-center justify-center p-4 text-center">
      <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-xs p-6">
        <div class="flex flex-col items-center justify-center">
          <!-- Icon -->
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
            <svg class="h-8 w-8 text-red-600 dark:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>

          <!-- Content -->
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2" id="validationErrorTitle">
            Validation Error
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6" id="validationErrorMessage">
            Please check your input.
          </p>

          <!-- Button -->
          <button type="button" 
                  onclick="closeValidationErrorModal()" 
                  class="w-full inline-flex justify-center rounded-xl border border-transparent bg-bsu-red px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
            Okay, I'll fix it
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Missing Information Modal -->
  <div id="missingInfoModal" class="hidden fixed inset-0 z-[99999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-screen items-center justify-center p-4 text-center">
      <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-xs p-6">
        <div class="flex flex-col items-center justify-center">
            <!-- Icon -->
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
              <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>

            <!-- Content -->
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2" id="missingInfoTitle">
              Missing Information
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6" id="missingInfoMessage">
              Please fill in all required fields to proceed.
            </p>

            <!-- Button -->
            <button type="button" 
                    onclick="closeMissingInfoModal()" 
                    class="w-full inline-flex justify-center rounded-xl border border-transparent bg-bsu-red px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
              Okay, I'll check
            </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Unsaved Changes Modal -->
  <div id="unsavedChangesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4" onclick="if(event.target === this) hideUnsavedChangesModal()">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6 z-[10000]" onclick="event.stopPropagation()">
      <div class="flex items-center mb-4">
        <div class="flex-shrink-0 w-12 h-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-full flex items-center justify-center mr-4">
          <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
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
      // Simple check - if formHasChanges flag is true, we have changes
      // This is set when any input is modified
      return formHasChanges;
    }

    // Show unsaved changes modal
    function showUnsavedChangesModal(navigationUrl) {
      pendingNavigationUrl = navigationUrl;
      const modal = document.getElementById('unsavedChangesModal');
      console.log('showUnsavedChangesModal called, modal element:', modal);
      if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'flex'; // Force display
        console.log('Modal should be visible now, classes:', modal.className);
        console.log('Modal display style:', window.getComputedStyle(modal).display);
      } else {
        console.error('Modal element not found!');
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
    
    // Make function globally accessible for onclick handlers
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

      // Create a hidden input to indicate this is a save-and-navigate action
      const saveAndNavigateInput = document.createElement('input');
      saveAndNavigateInput.type = 'hidden';
      saveAndNavigateInput.name = 'save_and_navigate';
      saveAndNavigateInput.value = pendingNavigationUrl || '{{ route("student.dashboard") }}';
      form.appendChild(saveAndNavigateInput);

      // Reset form change tracking
      formHasChanges = false;
      
      // Submit form
      form.submit();
    }

    // Navigate without saving
    function navigateWithoutSaving() {
      formHasChanges = false;
      if (pendingNavigationUrl) {
        window.location.href = pendingNavigationUrl;
      } else {
        window.location.href = '{{ route("student.dashboard") }}';
      }
    }

    // Multi-stage form navigation
    let currentStage = 1;
    const totalStages = 5;

    function showStage(stage) {
      // Hide all stages
      document.querySelectorAll('.form-stage').forEach(s => {
        s.classList.add('hidden');
      });
      
      // Show current stage
      const currentStageElement = document.querySelector(`.form-stage[data-stage="${stage}"]`);
      if (currentStageElement) {
        currentStageElement.classList.remove('hidden');
      }
      
      // Update progress bar
      const progress = (stage / totalStages) * 100;
      document.getElementById('progressBar').style.width = progress + '%';
      document.getElementById('progressText').textContent = `Stage ${stage} of ${totalStages}`;
      
      // Update stage indicators
      document.querySelectorAll('.stage-indicator').forEach((indicator, index) => {
        if (index + 1 <= stage) {
          indicator.classList.remove('bg-gray-300', 'dark:bg-gray-600');
          indicator.classList.add('bg-bsu-red');
        } else {
          indicator.classList.remove('bg-bsu-red');
          indicator.classList.add('bg-gray-300', 'dark:bg-gray-600');
        }
      });
      
      // Update navigation buttons
      const backBtn = document.getElementById('backBtn');
      const nextBtn = document.getElementById('nextBtn');
      const completeBtn = document.getElementById('completeBtn');
      
      if (stage === 1) {
        backBtn.classList.add('hidden');
      } else {
        backBtn.classList.remove('hidden');
      }
      
      if (stage === totalStages) {
        nextBtn.classList.add('hidden');
        completeBtn.classList.remove('hidden');
      } else {
        nextBtn.classList.remove('hidden');
        completeBtn.classList.add('hidden');
      }
      
      // Scroll to top
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function nextStage() {
      // Validate current stage before proceeding
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

    function showValidationErrorModal(message) {
      document.getElementById('validationErrorMessage').textContent = message;
      document.getElementById('validationErrorModal').classList.remove('hidden');
    }

    function closeValidationErrorModal() {
      document.getElementById('validationErrorModal').classList.add('hidden');
    }

    function showMissingInfoModal(message) {
      if (message) {
        document.getElementById('missingInfoMessage').textContent = message;
      }
      document.getElementById('missingInfoModal').classList.remove('hidden');
    }

    function closeMissingInfoModal() {
      document.getElementById('missingInfoModal').classList.add('hidden');
    }

    // Make functions globally accessible
    window.closeValidationErrorModal = closeValidationErrorModal;
    window.closeMissingInfoModal = closeMissingInfoModal;

    function validateCurrentStage() {
      const currentStageElement = document.querySelector(`.form-stage[data-stage="${currentStage}"]`);
      if (!currentStageElement) return true;
      
      // Get all required fields in current stage
      const requiredFields = currentStageElement.querySelectorAll('[required]');
      let isValid = true;
      let firstInvalidField = null;
      let missingFieldsCount = 0;
      
      requiredFields.forEach(field => {
        // Handle radio buttons specially
        if (field.type === 'radio') {
          const name = field.name;
          const checked = currentStageElement.querySelector(`input[name="${name}"]:checked`);
          if (!checked) {
            isValid = false;
            missingFieldsCount++;
            // Highlight parent container if possible, or key label
             const container = field.closest('.space-y-2') || field.parentElement.parentElement;
             if(container) {
                 container.classList.add('p-2', 'border', 'border-red-300', 'rounded', 'bg-red-50', 'dark:bg-red-900/10');
                 // Create a cleanup listener
                 const cleanup = () => {
                     container.classList.remove('p-2', 'border', 'border-red-300', 'rounded', 'bg-red-50', 'dark:bg-red-900/10');
                 };
                 const radios = currentStageElement.querySelectorAll(`input[name="${name}"]`);
                 radios.forEach(r => r.addEventListener('change', cleanup, {once: true}));
             }
             if (!firstInvalidField) firstInvalidField = field;
          }
        } else {
             if (!field.value || field.value.trim() === '') {
                isValid = false;
                missingFieldsCount++;
                 field.classList.add('border-red-500', 'ring-2', 'ring-red-300');
                 field.addEventListener('input', function() {
                 this.classList.remove('border-red-500', 'ring-2', 'ring-red-300');
                 }, { once: true });
                 if (!firstInvalidField) firstInvalidField = field;
            }
        }
      });

      // Special Validation for Zip Code (Stage 1)
      if (isValid && currentStage === 1) {
        const zipCodeInput = currentStageElement.querySelector('input[name="zip_code"]');
        if (zipCodeInput && zipCodeInput.value.length > 4) {
          isValid = false;
          showValidationErrorModal('Zip Code must be exactly 4 digits.');
          zipCodeInput.classList.add('border-red-500', 'ring-2', 'ring-red-300');
          zipCodeInput.addEventListener('input', function() {
            this.classList.remove('border-red-500', 'ring-2', 'ring-red-300');
          }, { once: true });
          if (!firstInvalidField) firstInvalidField = zipCodeInput;
        }
      }

      // Special Validation for Signature (Stage 5)
      // Since signature is now auto-populated and read-only, strict validation against input is implicitly handled.
      // We just check if it's not empty (which is covered by required attribute check above, roughly)
      // But actually, since it's read-only, we should ensure the source fields (Stage 1) are valid.
      // The general 'required' check on Stage 1 covers the name fields.
      // So no special manual validation block is needed here anymore.
      
      if (!isValid) {
        // Use custom modal for zip code error if it's the specific issue
        const zipInvalid = currentStage === 1 && firstInvalidField && firstInvalidField.name === 'zip_code' && firstInvalidField.value.length > 4;
        
        if (!zipInvalid) {
             // General missing required fields
             showMissingInfoModal('Please fill in all required fields before proceeding.');
        }
        
        // Scroll to first invalid field
        if (firstInvalidField) {
          firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstInvalidField.focus();
        }
      }
      
      return isValid;
    }

    // Remove print flag when Complete button is clicked
    function removePrintFlag() {
      const printInput = document.getElementById('printAfterSave');
      if (printInput) {
        printInput.remove();
      }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      // Track form changes
      trackFormChanges();

      // Verify modal exists
      const modal = document.getElementById('unsavedChangesModal');
      console.log('Modal element on load:', modal);
      if (!modal) {
        console.error('CRITICAL: Unsaved changes modal not found in DOM!');
      }

      // Intercept back to dashboard link - try multiple ways
      let backToDashboardLink = document.getElementById('backToDashboardLink');
      
      // If not found by ID, try to find by href
      if (!backToDashboardLink) {
        const links = document.querySelectorAll('a[href*="dashboard"]');
        console.log('Found dashboard links:', links.length);
        if (links.length > 0) {
          backToDashboardLink = links[0];
          console.log('Using first dashboard link found');
        }
      }
      
      if (backToDashboardLink) {
        console.log('Back to dashboard link found:', backToDashboardLink);
        backToDashboardLink.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const hasChanges = hasFormChanges();
          console.log('Back clicked, has changes:', hasChanges, 'formHasChanges flag:', formHasChanges);
          
          if (hasChanges) {
            console.log('Showing modal');
            showUnsavedChangesModal(this.href);
          } else {
            console.log('No changes, navigating directly');
            window.location.href = this.href;
          }
        });
      } else {
        console.error('Back to dashboard link not found!');
      }

      // Handle modal buttons
      const cancelBtn = document.getElementById('cancelLeaveBtn');
      const discardBtn = document.getElementById('discardChangesBtn');
      const saveBtn = document.getElementById('saveChangesBtn');

      if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
          hideUnsavedChangesModal();
        });
      }

      if (discardBtn) {
        discardBtn.addEventListener('click', function() {
          navigateWithoutSaving();
        });
      }

      if (saveBtn) {
        saveBtn.addEventListener('click', function() {
          saveAndNavigate();
        });
      }

      // Reset form change tracking on successful form submission
      const form = document.getElementById('mainForm');
      if (form) {
        form.addEventListener('submit', function() {
          formHasChanges = false;
        });
      }

      // Also handle browser back/forward and page unload
      window.addEventListener('beforeunload', function(e) {
        if (hasFormChanges()) {
          const message = 'Changes you made may not be saved.';
          e.preventDefault();
          e.returnValue = message;
          return message;
        }
      });

      // Check if stage parameter is in URL
      const urlParams = new URLSearchParams(window.location.search);
      const stageParam = urlParams.get('stage');
      
      if (stageParam && parseInt(stageParam) >= 1 && parseInt(stageParam) <= totalStages) {
        // Navigate to the specified stage
        currentStage = parseInt(stageParam);
        showStage(currentStage);
      } else {
        // Default to stage 1
        showStage(1);
      }
    });

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
        const monthNum = parseInt(month);
        const dayNum = parseInt(day);
        const yearNum = parseInt(year);
        
        // Basic validation
        if (monthNum >= 1 && monthNum <= 12 && dayNum >= 1 && dayNum <= 31 && yearNum >= 1900 && yearNum <= 2010) {
          const monthStr = month.padStart(2, '0');
          const dayStr = day.padStart(2, '0');
          const dateString = `${year}-${monthStr}-${dayStr}`;
          const birthDate = new Date(dateString);
          
          // Validate date (check if date is valid)
          if (birthDate.getFullYear() == yearNum && 
              (birthDate.getMonth() + 1) == monthNum && 
              birthDate.getDate() == dayNum) {
            birthdateHiddenInput.value = dateString;
            calculateAge(birthDate);
          } else {
            birthdateHiddenInput.value = '';
            ageInput.value = '';
          }
        } else {
          birthdateHiddenInput.value = '';
          ageInput.value = '';
        }
      } else {
        birthdateHiddenInput.value = '';
        ageInput.value = '';
      }
    }

    function calculateAge(birthDate) {
      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const m = today.getMonth() - birthDate.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
      ageInput.value = age > 0 ? age : '';
    }

    // Add event listeners to birthdate inputs
    birthMonthInput.addEventListener('input', updateBirthdate);
    birthDayInput.addEventListener('input', updateBirthdate);
    birthYearInput.addEventListener('input', updateBirthdate);

    // Auto-advance to next field when max length is reached
    birthMonthInput.addEventListener('input', function() {
      if (this.value.length === 2 && parseInt(this.value) <= 12) {
        birthDayInput.focus();
      }
    });

    birthDayInput.addEventListener('input', function() {
      if (this.value.length === 2 && parseInt(this.value) <= 31) {
        birthYearInput.focus();
      }
    });

    // Pre-calculate age on page load if birthdate exists
    if (birthdateHiddenInput.value) {
      const birthDate = new Date(birthdateHiddenInput.value);
      calculateAge(birthDate);
    }

    // Handle existing scholarship checkbox
    function toggleScholarshipDetails() {
      const yesRadio = document.querySelector('input[name="has_existing_scholarship"][value="1"]');
      const detailsContainer = document.getElementById('scholarship_details_container');
      const detailsInput = document.getElementById('existing_scholarship_details');
      
      if (yesRadio.checked) {
        detailsContainer.style.display = 'block';
        detailsInput.disabled = false;
        detailsInput.required = true;
      } else {
        detailsContainer.style.display = 'none';
        detailsInput.disabled = true;
        detailsInput.required = false;
        detailsInput.value = '';
      }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      toggleScholarshipDetails();
      
      // Auto-populate signature
      const firstNameInput = document.querySelector('input[name="first_name"]');
      // ... (existing code for signature)
      const middleNameInput = document.querySelector('input[name="middle_name"]');
      const lastNameInput = document.querySelector('input[name="last_name"]');
      const signatureInput = document.querySelector('input[name="student_signature"]');
      
      if (signatureInput) {
          // Make read-only
          signatureInput.readOnly = true;
          signatureInput.classList.add('bg-gray-100', 'cursor-not-allowed');

          function updateSignature() {
              const first = firstNameInput ? firstNameInput.value.trim() : '';
              const middle = middleNameInput ? middleNameInput.value.trim() : '';
              const last = lastNameInput ? lastNameInput.value.trim() : '';
              
              let middleInitial = '';
              if (middle.length > 0) {
                  middleInitial = middle.charAt(0).toUpperCase() + '.';
              }
              
              const signature = `${first} ${middleInitial} ${last}`.replace(/\s+/g, ' ').trim();
              signatureInput.value = signature;
          }

          if (firstNameInput) firstNameInput.addEventListener('input', updateSignature);
          if (middleNameInput) middleNameInput.addEventListener('input', updateSignature);
          if (lastNameInput) lastNameInput.addEventListener('input', updateSignature);
          
          // Initial update
          updateSignature();
      }
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('academicDropdowns', () => ({
            campusData: @json($campusData),
            selectedCampus: @json(old('campus_id', $user->campus_id ?? '')), 
            selectedCollege: @json(old('college', $user->college ?? '')),
            selectedProgram: @json(old('program', $user->program ?? '')),
            selectedTrack: @json(old('track', $user->track ?? '')),
            
            get colleges() {
                if (!this.selectedCampus || !this.campusData[this.selectedCampus]) return [];
                return Object.keys(this.campusData[this.selectedCampus]);
            },
            get programs() {
                if (!this.selectedCampus || !this.selectedCollege || !this.campusData[this.selectedCampus] || !this.campusData[this.selectedCampus][this.selectedCollege]) return [];
                return this.campusData[this.selectedCampus][this.selectedCollege];
            },
            get tracks() {
                const programObj = this.programs.find(p => p.name === this.selectedProgram);
                return programObj ? programObj.tracks : [];
            },
            
            updateCampus() {
                this.selectedCollege = '';
                this.selectedProgram = '';
                this.selectedTrack = '';
            },
            updateCollege() {
                this.selectedProgram = '';
                this.selectedTrack = '';
            },
            updateProgram() {
                this.selectedTrack = '';
            }
        }));
    });
  </script>
</body>
</html>
