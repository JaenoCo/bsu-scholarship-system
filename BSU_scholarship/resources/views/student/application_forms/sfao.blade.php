
    <!-- Unified Header -->
    <div class="bg-bsu-red dark:bg-red-900 rounded-xl shadow-lg p-6 mb-6 relative overflow-hidden">

        
        <div class="relative z-10 text-white">
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <span x-show="subTab === 'form'" class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    SFAO Application Form
                </span>
                <span x-show="subTab === 'gvsreap_form'" class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    TDP Application Form
                </span>
            </h1>
            <p class="text-red-100 mt-1 font-medium">Please complete all required information below</p>
        </div>
    </div>

    <!-- SFAO Form Content -->
    <div x-show="subTab === 'form'">
          <!-- Application Status & Progress -->
          @include('student.components.dashboard.application-status', ['form' => $form])

          <!-- Application Data Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
              <!-- Personal Data Card -->
              @include('student.components.dashboard.personal-data-card', ['form' => $form])

              <!-- Academic Data Card -->
              @include('student.components.dashboard.academic-data-card', ['form' => $form])

              <!-- Family Data Card -->
              @include('student.components.dashboard.family-data-card', ['form' => $form])

              <!-- Essay/Question Card -->
              @include('student.components.dashboard.essay-card', ['form' => $form])

              <!-- Certification Card -->
              @include('student.components.dashboard.certification-card', ['form' => $form])
          </div>

          <!-- Action Buttons -->
          <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('student.forms.application_form') }}" 
               class="inline-flex items-center px-6 py-3 bg-bsu-red hover:bg-bsu-redDark text-white font-semibold rounded-lg shadow hover:shadow-lg transition">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              <span>Proceed to Application Form</span>
            </a>
            
            <a href="{{ route('student.print-application', ['type' => 'sfao']) }}" 
               class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold rounded-lg shadow hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              <span>Print Application Form</span>
            </a>
          </div>
    </div>

    <!-- TDP Form Content -->
    <div x-show="subTab === 'gvsreap_form'">
        <!-- Application Status & Progress -->
          @include('student.components.dashboard.application-status', ['form' => $form])
        
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
              
              <!-- Personal Information Card -->
              <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">Personal Info</h3>
                      <p class="text-sm text-blue-600 dark:text-blue-300">Basic details & contact</p>
                    </div>
                  </div>
                  
                  <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">Name:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">
                        {{ $form?->first_name ?? '' }} {{ $form?->last_name ?? 'Not provided' }}
                      </span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">Birthdate:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">
                        {{ $form?->birthdate ? $form->birthdate->format('m/d/Y') : 'Not provided' }}
                      </span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">Mobile:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">
                        {{ $form?->contact_number ?? 'Not provided' }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between">
                     <!-- Status Indicator Logic (Simplified) -->
                     <div class="flex items-center">
                       @if($form && $form->first_name && $form->birthdate && $form->contact_number)
                         <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                         <span class="text-sm text-green-600 dark:text-green-400 font-medium">Complete</span>
                       @else
                         <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                         <span class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Incomplete</span>
                       @endif
                     </div>
                     <a href="{{ route('student.forms.application_form') }}?stage=1" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">Edit →</a>
                  </div>
              </div>
        
              <!-- School Information Card -->
              <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mr-4">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">School Info</h3>
                      <p class="text-sm text-green-600 dark:text-green-300">Education details</p>
                    </div>
                  </div>
                  
                  <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">School:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">Batangas State U</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">SR Code:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">{{ $form?->sr_code ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">Program:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">{{ $form?->program ?? 'Not provided' }}</span>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between">
                     <div class="flex items-center">
                       @if($form && $form->sr_code && $form->program)
                         <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                         <span class="text-sm text-green-600 dark:text-green-400 font-medium">Complete</span>
                       @else
                         <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                         <span class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Incomplete</span>
                       @endif
                     </div>
                     <a href="{{ route('student.forms.application_form') }}?stage=2" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 text-sm font-medium">Edit →</a>
                  </div>
              </div>
        
              <!-- Family Background Card -->
              <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-purple-800 dark:text-purple-200">Family Info</h3>
                      <p class="text-sm text-purple-600 dark:text-purple-300">Parents & Income</p>
                    </div>
                  </div>
                  
                  <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">Father:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">{{ $form?->father_name ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">Mother:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">{{ $form?->mother_name ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">Income:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">
                        {{ $form?->estimated_gross_annual_income ? 'Provided' : 'Not provided' }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between">
                     <div class="flex items-center">
                       @if($form && $form->father_name && $form->mother_name && $form->estimated_gross_annual_income)
                         <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                         <span class="text-sm text-green-600 dark:text-green-400 font-medium">Complete</span>
                       @else
                         <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                         <span class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Incomplete</span>
                       @endif
                     </div>
                     <a href="{{ route('student.forms.application_form') }}?stage=3" class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 text-sm font-medium">Edit →</a>
                  </div>
              </div>
        
              <!-- Other Financial Assistance Card -->
              <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border border-orange-200 dark:border-orange-700 rounded-lg p-6 hover:shadow-lg transition-shadow">
                  <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center mr-4">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-orange-800 dark:text-orange-200">Financial Aid</h3>
                      <p class="text-sm text-orange-600 dark:text-orange-300">Other assistance details</p>
                    </div>
                  </div>
                  
                  <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">Other Assistance?</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200">
                        {{ $form?->has_existing_scholarship ? 'Yes' : 'No' }}
                      </span>
                    </div>
                    @if($form?->has_existing_scholarship)
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600 dark:text-gray-300">Details:</span>
                      <span class="font-medium text-gray-800 dark:text-gray-200 truncate max-w-[150px]">
                        {{ $form?->existing_scholarship_details ?? 'Not specified' }}
                      </span>
                    </div>
                    @endif
                  </div>
                  
                  <div class="flex items-center justify-between">
                     <div class="flex items-center">
                         <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                         <span class="text-sm text-green-600 dark:text-green-400 font-medium">Complete</span>
                     </div>
                     <a href="{{ route('student.forms.application_form') }}?stage=2" class="text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 text-sm font-medium">Edit →</a>
                  </div>
              </div>
        
              <!-- Certification Card -->
              @include('student.components.dashboard.certification-card', ['form' => $form])
        
          </div>
        
          <!-- Action Buttons -->
          <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('student.forms.tdp_application_form') }}" 
               class="inline-flex items-center px-6 py-3 bg-bsu-red hover:bg-bsu-redDark text-white font-semibold rounded-lg shadow hover:shadow-lg transition">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              <span>Proceed to Application Form</span>
            </a>
        
            <a href="{{ route('student.print-application', ['type' => 'tdp']) }}" 
               class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold rounded-lg shadow hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              <span>Print Application Form</span>
            </a>
          </div>
    </div>
