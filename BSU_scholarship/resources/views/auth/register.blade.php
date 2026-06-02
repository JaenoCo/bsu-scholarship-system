@extends('auth.layout')

@section('title', 'Create Account')
@section('heading', 'Join Spartan Scholarship')
@section('subheading', 'Create your account to start your scholarship journey')
@section('container_width', 'max-w-4xl')

@section('content')
<div x-data="signupForm()">
  <!-- Progress Bar -->
  <div class="mb-8">
    <div class="flex justify-between mb-2">
      <template x-for="(step, index) in steps" :key="index">
        <div class="flex flex-col items-center w-1/4">
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors duration-300"
               :class="currentStep > index + 1 ? 'bg-green-500 text-white' : (currentStep === index + 1 ? 'bg-bsu-red text-white' : 'bg-gray-200 text-gray-500')">
            <span x-show="currentStep <= index + 1" x-text="index + 1"></span>
            <svg x-show="currentStep > index + 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <span class="text-xs mt-1 text-center hidden sm:block" 
                :class="currentStep === index + 1 ? 'text-bsu-red font-semibold' : 'text-gray-500'"
                x-text="step"></span>
        </div>
      </template>
    </div>
    <div class="relative pt-1">
      <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
        <div :style="`width: ${(currentStep / steps.length) * 100}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-bsu-red transition-all duration-500"></div>
      </div>
    </div>
  </div>

  <form method="POST" action="{{ url('/register') }}" id="registerForm" autocomplete="off" class="space-y-4" @submit.prevent="submitForm">
    @csrf
    <input type="hidden" name="role" value="student">

    <!-- Step 1: Personal Info -->
    <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Personal Information</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-auth.input type="text" label="First Name" name="first_name" placeholder="First Name" required x-model="formData.first_name" />
        <x-auth.input type="text" label="Middle Name (Optional)" name="middle_name" placeholder="Middle Name" x-model="formData.middle_name" />
        <x-auth.input type="text" label="Last Name" name="last_name" placeholder="Last Name" required x-model="formData.last_name" />
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-auth.input type="date" label="Birthdate" name="birthdate" required x-model="formData.birthdate" />
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sex <span class="text-red-500">*</span></label>
          <select name="sex" required x-model="formData.sex" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
            <option value="" disabled selected>Select Sex</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Step 2: Contact Info -->
    <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Contact Information</h3>
      
      <x-auth.input type="email" label="BSU Email" name="email" placeholder="example@g.batstate-u.edu.ph" pattern="^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$" required x-model="formData.email">
        <p class="mt-1 text-xs text-gray-500">Must end with @g.batstate-u.edu.ph</p>
      </x-auth.input>
      
      <x-auth.input type="text" label="Contact Number" name="contact_number" placeholder="09123456789" required x-model="formData.contact_number" />
    </div>

    <!-- Step 3: Academic Info -->
    <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Academic Information</h3>
      
      <x-auth.input type="text" label="SR Code" name="sr_code" placeholder="XX-XXXXX" required x-model="formData.sr_code" />
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Education Level <span class="text-red-500">*</span></label>
        <select name="education_level" required x-model="formData.education_level" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
          <option value="" disabled selected>Select Level</option>
          <option value="Undergraduate">Undergraduate</option>
          <option value="Graduate School">Graduate School</option>
          <option value="Integrated School">Integrated School</option>
        </select>
      </div>

      <div class="mb-4">
        <label for="campus_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Campus <span class="text-red-500">*</span></label>
        <select id="campus_id" name="campus_id" required x-model="formData.campus_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
          <option value="" disabled selected>Select a campus</option>
          @foreach($campuses as $campus)
            <option value="{{ $campus->id }}">{{ $campus->name }}</option>
          @endforeach
        </select>
      </div>



      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">College <span class="text-red-500">*</span></label>
        <select name="college" required x-model="formData.college" @change="formData.program = formData.college" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" :disabled="!formData.campus_id">
          <option value="" disabled selected>Select College</option>
          <template x-for="college in availableColleges" :key="college.id">
            <option :value="college.short_name" x-text="college.short_name"></option>
          </template>
        </select>
        <input type="hidden" name="program" x-model="formData.program">
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year Level <span class="text-red-500">*</span></label>
        <select name="year_level" required x-model="formData.year_level" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
          <option value="" disabled selected>Select Year Level</option>
          <option value="1st Year">1st Year</option>
          <option value="2nd Year">2nd Year</option>
          <option value="3rd Year">3rd Year</option>
          <option value="4th Year">4th Year</option>
        </select>
      </div>
    </div>

    <!-- Step 4: Scholarship Verification -->
    <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Scholarship Verification</h3>
      
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Do you currently have an existing scholarship?</label>
        <div class="flex gap-4">
          <label class="inline-flex items-center">
            <input type="radio" name="has_scholarship" value="yes" x-model="formData.has_scholarship" class="form-radio text-red-600 focus:ring-red-500">
            <span class="ml-2 text-gray-700 dark:text-gray-300">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input type="radio" name="has_scholarship" value="no" x-model="formData.has_scholarship" class="form-radio text-red-600 focus:ring-red-500">
            <span class="ml-2 text-gray-700 dark:text-gray-300">No</span>
          </label>
        </div>
      </div>

      <div x-show="formData.has_scholarship === 'yes'" class="mt-4">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Select Scholarships:</h4>
        <div class="max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-2 bg-white dark:bg-gray-800">
          @foreach($scholarships as $scholarship)
            <label class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer transition-colors">
              <input type="checkbox" value="{{ $scholarship->id }}" x-model="formData.selected_scholarships" class="form-checkbox text-red-600 focus:ring-red-500 rounded border-gray-300 dark:border-gray-500 dark:bg-gray-700">
              <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">{{ $scholarship->scholarship_name }}</span>
            </label>
          @endforeach
        </div>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Please select all scholarships you are currently enrolled in.
        </p>
      </div>
    </div>

    <!-- Step 5: Credentials -->
    <div x-show="currentStep === 5" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 border-b pb-2">Credentials</h3>
      
      <x-auth.password-input label="Password" name="password" placeholder="Create a strong password" autocomplete="new-password" showStrength="true" required x-model="formData.password" />
      <x-auth.password-input label="Confirm Password" name="password_confirmation" placeholder="Confirm your password" autocomplete="new-password" required x-model="formData.password_confirmation" />
      
      <div class="mb-4 mt-4">
        <label class="inline-flex items-start text-sm text-gray-700 dark:text-gray-300">
          <input type="checkbox" name="terms" required x-model="formData.terms" class="mt-1 rounded text-red-600 focus:ring-red-500 dark:bg-gray-700">
          <span class="ml-2">
            I agree to the <a href="#" @click.prevent="showToSModal = true" class="text-red-600 hover:underline dark:text-red-400">Terms of Service</a>
          </span>
        </label>
      </div>
      <div class="mb-6">
        <label class="inline-flex items-start text-sm text-gray-700 dark:text-gray-300">
          <input type="checkbox" name="privacy" required x-model="formData.privacy" class="mt-1 rounded text-red-600 focus:ring-red-500 dark:bg-gray-700">
          <span class="ml-2">
            I agree to the <a href="#" @click.prevent="showPrivacyModal = true" class="text-red-600 hover:underline dark:text-red-400">Privacy Policy</a>
          </span>
        </label>
      </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-6">
      <button type="button" x-show="currentStep > 1" @click="prevStep" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">Back</button>
      <div class="flex-1"></div> <!-- Spacer -->
      <button type="button" x-show="currentStep < steps.length" @click="nextStep" class="px-4 py-2 bg-bsu-red text-white rounded-lg hover:bg-red-700 transition">Next</button>
      <button type="submit" x-show="currentStep === steps.length" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center" :disabled="isSubmitting">
        <span x-show="!isSubmitting">Create Account</span>
        <span x-show="isSubmitting" class="flex items-center">
          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Processing...
        </span>
      </button>
    </div>
  </form>

  <div class="mt-4 text-center">
    <a href="{{ url('/login') }}" class="text-sm text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors duration-200">Already have an account? Sign In</a>
  </div>

  <!-- Age Validation Modal -->
  <div x-show="showAgeErrorModal" 
       class="fixed inset-0 z-50 overflow-y-auto" 
       aria-labelledby="modal-title" 
       role="dialog" 
       aria-modal="true"
       style="display: none;">
       
    <!-- Backdrop -->
    <div x-show="showAgeErrorModal" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" 
         aria-hidden="true"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-screen items-center justify-center p-4 text-center">
      <div x-show="showAgeErrorModal" 
           x-transition:enter="ease-out duration-300" 
           x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave="ease-in duration-200" 
           x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-xs p-6">
        
        <div class="flex flex-col items-center justify-center">
            <!-- Icon -->
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
              <svg class="h-8 w-8 text-red-600 dark:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>

            <!-- Content -->
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2" id="modal-title">
              Age Restriction
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
              We apologize, but you must be at least <span class="font-bold text-gray-700 dark:text-gray-300">18 years old</span> to register for an account on this platform.
            </p>

            <!-- Button -->
            <button type="button" 
                    @click="showAgeErrorModal = false" 
                    class="w-full inline-flex justify-center rounded-xl border border-transparent bg-bsu-red px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
              I Understand
            </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Required Fields Modal -->
  <div x-show="showRequiredFieldsModal" 
       class="fixed inset-0 z-50 overflow-y-auto" 
       aria-labelledby="modal-title" 
       role="dialog" 
       aria-modal="true"
       style="display: none;">
       
    <!-- Backdrop -->
    <div x-show="showRequiredFieldsModal" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" 
         aria-hidden="true"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-screen items-center justify-center p-4 text-center">
      <div x-show="showRequiredFieldsModal" 
           x-transition:enter="ease-out duration-300" 
           x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave="ease-in duration-200" 
           x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-xs p-6">
        
        <div class="flex flex-col items-center justify-center">
            <!-- Icon -->
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
              <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>

            <!-- Content -->
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2" id="modal-title">
              Missing Information
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
              Please fill in all required fields to proceed to the next step.
            </p>

            <!-- Button -->
            <button type="button" 
                    @click="showRequiredFieldsModal = false" 
                    class="w-full inline-flex justify-center rounded-xl border border-transparent bg-bsu-red px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
              Okay, I'll check
            </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Invalid Email Modal -->
  <div x-show="showEmailErrorModal" 
       class="fixed inset-0 z-50 overflow-y-auto" 
       aria-labelledby="modal-title" 
       role="dialog" 
       aria-modal="true"
       style="display: none;">
       
    <!-- Backdrop -->
    <div x-show="showEmailErrorModal" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" 
         aria-hidden="true"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-screen items-center justify-center p-4 text-center">
      <div x-show="showEmailErrorModal" 
           x-transition:enter="ease-out duration-300" 
           x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave="ease-in duration-200" 
           x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-xs p-6">
        
        <div class="flex flex-col items-center justify-center">
            <!-- Icon -->
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
              <svg class="h-8 w-8 text-red-600 dark:text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>

            <!-- Content -->
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2" id="modal-title">
              Invalid Email Format
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
              Please use your G-Suite email address ending with <span class="font-bold text-gray-700 dark:text-gray-300">@g.batstate-u.edu.ph</span>.
            </p>

            <!-- Button -->
            <button type="button" 
                    @click="showEmailErrorModal = false" 
                    class="w-full inline-flex justify-center rounded-xl border border-transparent bg-bsu-red px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
              Okay, I'll fix it
            </button>
        </div>
      </div>
    </div>
  </div>
  <!-- TOS Modal -->
  <div x-show="showToSModal" 
       class="fixed inset-0 z-50 overflow-y-auto" 
       aria-labelledby="modal-title" 
       role="dialog" 
       aria-modal="true"
       style="display: none;">
       
    <!-- Backdrop -->
    <div x-show="showToSModal" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" 
         aria-hidden="true"
         @click="showToSModal = false"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-screen items-center justify-center p-4">
      <div x-show="showToSModal" 
           x-transition:enter="ease-out duration-300" 
           x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave="ease-in duration-200" 
           x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-2xl max-h-[80vh] flex flex-col">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-bsu-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Terms of Service (TOS)
            </h3>
            <button @click="showToSModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="px-6 py-4 overflow-y-auto custom-scrollbar prose dark:prose-invert max-w-none text-sm">
            <p class="font-bold">Effective Date: [Insert Date]</p>
            <p class="font-bold mb-4">Last Updated: [Insert Date]</p>

            <h4 class="font-bold mt-4">1. Acceptance of Terms</h4>
            <p>By creating an account or accessing the system ("Service"), you agree to comply with and be bound by these Terms of Service. If you do not agree, you must not use the Service.</p>

            <h4 class="font-bold mt-4">2. Account Registration</h4>
            <ul class="list-disc pl-5">
                <li>You must provide accurate, complete, and up-to-date information.</li>
                <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                <li>You agree to notify the administrator immediately of any unauthorized access or security breach.</li>
                <li>The system may suspend or terminate accounts found violating these Terms.</li>
            </ul>

            <h4 class="font-bold mt-4">3. Acceptable Use</h4>
            <p>You agree NOT to:</p>
            <ul class="list-disc pl-5">
                <li>Attempt to access, modify, or disrupt system operations.</li>
                <li>Upload malicious content (viruses, malware, scripts).</li>
                <li>Use the system for fraudulent or illegal activity.</li>
                <li>Share login credentials with others.</li>
            </ul>

            <h4 class="font-bold mt-4">4. System Availability</h4>
            <p>We strive to keep the Service available at all times, but we do not guarantee uninterrupted, error-free operation. Scheduled or emergency maintenance may affect system availability.</p>

            <h4 class="font-bold mt-4">5. User Responsibilities</h4>
            <p>Users must:</p>
            <ul class="list-disc pl-5">
                <li>Provide true information when submitting forms.</li>
                <li>Follow system policies, guidelines, and eligibility rules.</li>
                <li>Use the system only for legitimate academic or administrative purposes.</li>
            </ul>

            <h4 class="font-bold mt-4">6. Termination</h4>
            <p>We may suspend or remove your account if:</p>
            <ul class="list-disc pl-5">
                <li>You violate these Terms.</li>
                <li>You submit false or fraudulent information.</li>
                <li>The system detects suspicious or harmful activity.</li>
            </ul>

            <h4 class="font-bold mt-4">7. Limitation of Liability</h4>
            <p>The Service is provided “as is.” We are not liable for:</p>
            <ul class="list-disc pl-5">
                <li>Data loss</li>
                <li>Unauthorized access caused by user negligence</li>
                <li>Downtime or technical failures</li>
                <li>Damages arising from misuse of the system</li>
            </ul>

            <h4 class="font-bold mt-4">8. Changes to the Terms</h4>
            <p>We may update these Terms at any time. Continued use of the Service after changes means you accept the updated Terms.</p>

            <h4 class="font-bold mt-4">9. Contact Information</h4>
            <p>For questions, concerns, or reporting violations, contact:</p>
            <p>[Insert Contact Email or Office Name]</p>
            <p><a href="mailto:test.bsuscholarship@gmail.com" class="text-blue-600 hover:underline">test.bsuscholarship@gmail.com</a></p>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-end">
            <button type="button" 
                    @click="showToSModal = false; formData.terms = true" 
                    class="px-6 py-2 bg-bsu-red hover:bg-bsu-redDark text-white rounded-lg transition-colors font-medium text-sm shadow-sm">
              I Accept
            </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Privacy Policy Modal -->
  <div x-show="showPrivacyModal" 
       class="fixed inset-0 z-50 overflow-y-auto" 
       aria-labelledby="modal-title" 
       role="dialog" 
       aria-modal="true"
       style="display: none;">
       
    <!-- Backdrop -->
    <div x-show="showPrivacyModal" 
         x-transition:enter="ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" 
         aria-hidden="true"
         @click="showPrivacyModal = false"></div>

    <!-- Modal Panel -->
    <div class="flex min-h-screen items-center justify-center p-4">
      <div x-show="showPrivacyModal" 
           x-transition:enter="ease-out duration-300" 
           x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave="ease-in duration-200" 
           x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
           x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
           class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-2xl max-h-[80vh] flex flex-col">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-bsu-red" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Privacy Policy
            </h3>
            <button @click="showPrivacyModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="px-6 py-4 overflow-y-auto custom-scrollbar prose dark:prose-invert max-w-none text-sm">
            <p class="font-bold">Effective Date: [Insert Date]</p>
            <p class="font-bold mb-4">Last Updated: [Insert Date]</p>

            <h4 class="font-bold mt-4">1. Information We Collect</h4>
            <p>We may collect the following types of information during account registration and system usage:</p>
            <p class="font-semibold mt-2">Personal Information</p>
            <ul class="list-disc pl-5">
                <li>Full name</li>
                <li>Email address</li>
                <li>Contact number</li>
                <li>Birthdate</li>
                <li>Address</li>
                <li>School/department/role</li>
                <li>Other details required for scholarship, registration, or system services</li>
            </ul>
            <p class="font-semibold mt-2">Technical Information</p>
            <ul class="list-disc pl-5">
                <li>Login timestamps</li>
                <li>IP address</li>
                <li>Device and browser type</li>
                <li>Usage logs for system security</li>
            </ul>

            <h4 class="font-bold mt-4">2. How We Use Your Information</h4>
            <p>Your data may be used for:</p>
            <ul class="list-disc pl-5">
                <li>Account creation and identity verification</li>
                <li>Processing scholarship applications or forms</li>
                <li>Monitoring eligibility and academic requirements</li>
                <li>Improving system performance and security</li>
                <li>Sending notifications, updates, or official communications</li>
            </ul>

            <h4 class="font-bold mt-4">3. Data Sharing and Disclosure</h4>
            <p>We do not sell or rent your information. We may share information only with:</p>
            <ul class="list-disc pl-5">
                <li>Authorized school administrators</li>
                <li>Scholarship offices or related departments</li>
                <li>Government agencies if legally required</li>
                <li>IT personnel maintaining the system</li>
            </ul>

            <h4 class="font-bold mt-4">4. Data Protection and Security</h4>
            <p>We use reasonable security measures to protect your data, including:</p>
            <ul class="list-disc pl-5">
                <li>Encrypted connections (HTTPS)</li>
                <li>Access controls and authentication</li>
                <li>System monitoring and logging</li>
                <li>Regular security updates</li>
            </ul>
            <p class="mt-2">However, no system is 100% secure. Users must protect their own passwords and accounts.</p>

            <h4 class="font-bold mt-4">5. Data Retention</h4>
            <p>We retain your information for as long as required for academic or administrative purposes, or as required by law. You may request deletion of your account when eligible.</p>

            <h4 class="font-bold mt-4">6. User Rights</h4>
            <p>You may request to:</p>
            <ul class="list-disc pl-5">
                <li>Access your personal information</li>
                <li>Correct inaccurate information</li>
                <li>Request deletion of your account (subject to policies)</li>
                <li>Withdraw consent for certain data uses</li>
            </ul>

            <h4 class="font-bold mt-4">7. Cookies and Tracking</h4>
            <p>The system may use cookies or similar technologies to enhance user experience and track basic site usage.</p>

            <h4 class="font-bold mt-4">8. Updates to the Privacy Policy</h4>
            <p>We may revise this Privacy Policy. Changes will be posted within the system, and continued use means you accept the updated policy.</p>

            <h4 class="font-bold mt-4">9. Contact Information</h4>
            <p>If you have privacy-related questions or concerns, contact:</p>
            <p>[Insert Contact Email or Office]</p>
            <p><a href="mailto:test.bsuscholarship@gmail.com" class="text-blue-600 hover:underline">test.bsuscholarship@gmail.com</a></p>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-end">
            <button type="button" 
                    @click="showPrivacyModal = false; formData.privacy = true" 
                    class="px-6 py-2 bg-bsu-red hover:bg-bsu-redDark text-white rounded-lg transition-colors font-medium text-sm shadow-sm">
              I Accept
            </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('footer')
<a href="{{ url('/') }}" class="flex items-center gap-1 text-red-600 hover:underline text-sm dark:text-red-400">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    Back to Homepage
</a>
@endsection

@push('scripts')
<script>
  function signupForm() {
    return {
      currentStep: 1,
      campuses: @json($campuses),
      availableColleges: [],
      steps: ['Personal Info', 'Contact Info', 'Academic Info', 'Scholarship Verification', 'Credentials'],
      formData: {
        first_name: '',
        middle_name: '',
        last_name: '',
        birthdate: '',
        sex: '',
        email: '',
        contact_number: '',
        sr_code: '',
        education_level: '',
        college: '',
        program: '',
        year_level: '',
        campus_id: '',
        has_scholarship: '',
        selected_scholarships: [],
        password: '',
        password_confirmation: '',
        terms: false,
        privacy: false
      },
      showAgeErrorModal: false,
      showRequiredFieldsModal: false,
      showEmailErrorModal: false,
      showToSModal: false,
      showPrivacyModal: false,
      isSubmitting: false,
      
      nextStep() {
        if (this.validateStep(this.currentStep)) {
          this.currentStep++;
        }
      },
      
      prevStep() {
        if (this.currentStep > 1) {
          this.currentStep--;
        }
      },
      
      validateStep(step) {
        // Basic validation logic
        if (step === 1) {
          if (!this.formData.first_name || !this.formData.last_name || !this.formData.birthdate || !this.formData.sex) {
            this.showRequiredFieldsModal = true;
            return false;
          }
          
          // Age Validation
          const birthdate = new Date(this.formData.birthdate);
          const today = new Date();
          let age = today.getFullYear() - birthdate.getFullYear();
          const m = today.getMonth() - birthdate.getMonth();
          if (m < 0 || (m === 0 && today.getDate() < birthdate.getDate())) {
              age--;
          }
          
          if (age < 18) {
              this.showAgeErrorModal = true;
              return false;
          }
        } else if (step === 2) {
          if (!this.formData.email || !this.formData.contact_number) {
            this.showRequiredFieldsModal = true;
            return false;
          }
          if (!this.formData.email.endsWith('@g.batstate-u.edu.ph')) {
            this.showEmailErrorModal = true;
            return false;
          }
        } else if (step === 3) {
          if (!this.formData.sr_code || !this.formData.education_level || !this.formData.college || !this.formData.program || !this.formData.year_level || !this.formData.campus_id) {
            this.showRequiredFieldsModal = true;
            return false;
          }
        } else if (step === 4) {
          if (!this.formData.has_scholarship) {
            alert('Please answer the scholarship verification question.');
            return false;
          }
          if (this.formData.has_scholarship === 'yes' && this.formData.selected_scholarships.length === 0) {
            alert('Please select at least one scholarship.');
            return false;
          }
        } else if (step === 5) {
          if (!this.formData.password || !this.formData.password_confirmation) {
            alert('Please enter a password.');
            return false;
          }
          if (this.formData.password !== this.formData.password_confirmation) {
            alert('Passwords do not match.');
            return false;
          }
          if (!this.formData.terms) {
            alert('You must agree to the Terms of Service.');
            return false;
          }
          if (!this.formData.privacy) {
            alert('You must agree to the Privacy Policy.');
            return false;
          }
        }
        return true;
      },
      
      init() {
        // Check if page was reloaded
        const navEntry = performance.getEntriesByType("navigation")[0];
        const isReload = navEntry ? navEntry.type === 'reload' : (performance.navigation.type === 1);

        if (isReload) {
          const savedData = sessionStorage.getItem('signupFormData');
          if (savedData) {
            this.formData = JSON.parse(savedData);
            // Ensure selected_scholarships is an array
            if (!Array.isArray(this.formData.selected_scholarships)) {
                this.formData.selected_scholarships = [];
            }
            // Restore current step if saved (optional, but good for UX)
            const savedStep = sessionStorage.getItem('signupCurrentStep');
            if (savedStep) {
              this.currentStep = parseInt(savedStep);
            }
          }
        } else {
          // If not a reload (e.g. first visit or navigated back), clear storage
          sessionStorage.removeItem('signupFormData');
          sessionStorage.removeItem('signupCurrentStep');
        }

        // Watch for changes and save to sessionStorage
        this.$watch('formData', (value) => {
          sessionStorage.setItem('signupFormData', JSON.stringify(value));
        });
        
        this.$watch('currentStep', (value) => {
          sessionStorage.setItem('signupCurrentStep', value);
        });

        // Watch for campus changes to update colleges
        this.$watch('formData.campus_id', (value) => {
            if (value) {
                const selectedCampus = this.campuses.find(c => c.id == value);
                this.availableColleges = selectedCampus ? selectedCampus.colleges : [];
                // Reset college if it's not in the new list (unless it's a reload)
                // We can just reset it to ensure validity
                if (!this.availableColleges.some(d => d.name === this.formData.college)) {
                    this.formData.college = '';
                }
            } else {
                this.availableColleges = [];
                this.formData.college = '';
            }
        });

        // Initialize colleges if campus is already selected (e.g. from session storage)
        if (this.formData.campus_id) {
             const selectedCampus = this.campuses.find(c => c.id == this.formData.campus_id);
             this.availableColleges = selectedCampus ? selectedCampus.colleges : [];
        }
      },

      submitForm() {
        if (this.validateStep(5)) {
          this.isSubmitting = true;
          
          // Create hidden inputs for selected scholarships before submitting
          const form = document.getElementById('registerForm');
          
          // Remove any existing hidden inputs for selected_scholarships to avoid duplicates
          const existingInputs = form.querySelectorAll('input[name="selected_scholarships[]"]');
          existingInputs.forEach(input => input.remove());

          if (this.formData.has_scholarship === 'yes') {
              this.formData.selected_scholarships.forEach(id => {
                  const input = document.createElement('input');
                  input.type = 'hidden';
                  input.name = 'selected_scholarships[]';
                  input.value = id;
                  form.appendChild(input);
              });
          }

          // Clear storage on submit
          sessionStorage.removeItem('signupFormData');
          sessionStorage.removeItem('signupCurrentStep');
          form.submit();
        }
      }
    }
  }
</script>
@endpush
