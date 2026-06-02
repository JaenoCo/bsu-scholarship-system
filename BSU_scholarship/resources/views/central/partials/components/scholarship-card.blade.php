@props([
    'scholarship',
    'role' => 'central', // 'central', 'student', 'sfao'
    'hasActiveApplication' => false,
    'fillPercentage' => 0,
    'disableModal' => false
])

@php
    $isStudent = ($role === 'student');
    $isScholar = $isStudent && ($scholarship->is_scholar ?? false);
    $isApplied = $isStudent && $scholarship->applied && !$isScholar;

    $cardBaseClass = 'rounded-xl shadow-lg border-2 p-6 hover:shadow-xl transition-all duration-300 group relative overflow-hidden mb-8 cursor-pointer';
    
    if ($isScholar) {
        $cardClass = $cardBaseClass . ' bg-green-50 dark:bg-green-900/20 border-green-500 hover:border-green-600 hover:shadow-green-500/20';
        $gradientColors = 'rgba(240, 253, 244, 0.95), rgba(240, 253, 244, 0.95)'; // Green tint
    } elseif ($isApplied) {
        $cardClass = $cardBaseClass . ' bg-blue-50 dark:bg-blue-900/20 border-blue-500 hover:border-blue-600 hover:shadow-blue-500/20';
        $gradientColors = 'rgba(239, 246, 255, 0.95), rgba(239, 246, 255, 0.95)'; // Blue tint
    } else {
        $cardClass = $cardBaseClass . ' bg-white dark:bg-gray-800 border-bsu-red hover:border-bsu-redDark hover:shadow-bsu-red/20';
        $gradientColors = 'rgba(255,255,255,0.7), rgba(255,255,255,0.7)';
    }
@endphp

<div x-data="{ open: false, showReleaseModal: false, disableModal: @json($disableModal) }" 
     class="{{ $cardClass }}"
     @click="if(!disableModal) open = true"
     @if($scholarship->background_image)
     style="background-image: linear-gradient({{ $gradientColors }}), url('{{ $scholarship->getBackgroundImageUrl() }}'); background-size: cover; background-position: center;"
     @endif>

    @if($isScholar)
        <!-- Green Overlay -->
        <div class="absolute inset-0 bg-green-500/10 pointer-events-none z-0"></div>
        <!-- Scholar Text -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-10">
            <span class="text-5xl font-extrabold text-green-600/30 uppercase tracking-widest transform -rotate-12 border-4 border-green-600/30 px-10 py-4 rounded-2xl backdrop-blur-sm">Scholar</span>
        </div>
    @elseif($isApplied)
        <!-- Blue Overlay -->
        <div class="absolute inset-0 bg-blue-500/10 pointer-events-none z-0"></div>
        <!-- Applied Text -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-10">
            <span class="text-5xl font-extrabold text-blue-600/30 uppercase tracking-widest transform -rotate-12 border-4 border-blue-600/30 px-10 py-4 rounded-2xl backdrop-blur-sm">Applied</span>
        </div>
    @endif

        <!-- Scholarship Content -->
        <!-- Header (Top Left) -->
        <!-- Scholarship Content -->
        <div class="flex flex-col lg:flex-row lg:items-start gap-4">
            <!-- Left Column: Title, Type, Description -->
            <div class="flex-1 min-w-0">
                <!-- Title & Type -->
                <div class="mb-4">
                    <h3 class="text-xl font-bold text-bsu-red dark:text-white group-hover:text-bsu-redDark dark:group-hover:text-bsu-red transition-colors duration-200">
                        {{ $scholarship->scholarship_name }}
                    </h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $scholarship->getScholarshipTypeBadgeColor() }} shadow-sm">
                        {{ ucfirst($scholarship->scholarship_type) }}
                        </span>
                    </div>
                </div>
                
                <!-- Description Preview -->
                <!-- Description Preview -->
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 line-clamp-2">
                    {{ \Illuminate\Support\Str::limit($scholarship->description, 150) }}
                </p>
            </div>

            <!-- Right Column: Deadline, Quick Info & Actions -->
            <div class="flex flex-col gap-4 lg:min-w-[200px]">
                 <!-- Submission Deadline -->
                 <div>
                    <span class="block text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold mb-1">Submission Deadline</span>
                    <div class="font-bold text-lg {{ $scholarship->getDaysUntilDeadline() <= 7 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                        {{ $scholarship->submission_deadline?->format('M d, Y') }}
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-2 lg:grid-cols-1 gap-3 text-sm">

                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Amount:</span>
                        <div class="font-semibold text-green-600">
                            @if($scholarship->grant_amount)
                                ₱{{ number_format((float) $scholarship->grant_amount, 0) }}
                            @else
                                TBD
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Status:</span>
                        <div class="font-semibold {{ $scholarship->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $scholarship->is_active ? 'Active' : 'Inactive' }}
                        </div>
                    </div>
                </div>

                <!-- Slots Information -->
                @if($scholarship->slots_available)
                @php
                  // Use passed fillPercentage if available, otherwise calculate
                  if (!isset($fillPercentage) || $fillPercentage === 0) {
                      $applicationsCount = $scholarship->applications()->count();
                      $slotsAvailable = $scholarship->slots_available;
                      $fillPercentage = min(100, ($applicationsCount / $slotsAvailable) * 100);
                  } else {
                      // If fillPercentage is passed (e.g. from SFAO controller), use it.
                      // We might need applications count too if we want to show "X / Y"
                      $applicationsCount = $scholarship->applications_count ?? $scholarship->applications()->count();
                      $slotsAvailable = $scholarship->slots_available;
                  }
                @endphp
                <div class="mt-2">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Available Slots:</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $applicationsCount }} / {{ $slotsAvailable }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-bsu-red h-2 rounded-full transition-all duration-300" 
                             data-width="{{ $fillPercentage }}"
                             x-bind:style="'width: ' + $el.dataset.width + '%'"></div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ number_format($fillPercentage, 1) }}% filled
                    </div>
                </div>
                @endif

                @if($role === 'student')
                    @if($scholarship->is_scholar ?? false)
                        <!-- Scholar status is handled by overlay -->
                    @elseif($scholarship->applied)
                      <!-- Applied status is handled by overlay -->
                    @endif
                @endif
            </div>
        </div>

        <!-- Fabulous Modal Content (Generic Student/Central View) -->
        <div x-show="open && !disableModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click.stop="open = false"></div>

            <!-- Modal Card -->
            <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95">

                <!-- Hero Header -->
                <div class="relative h-48 sm:h-64 bg-gray-200 dark:bg-gray-700 shrink-0">
                    @if($scholarship->background_image)
                        <div class="absolute inset-0 bg-cover bg-center" 
                             style="background-image: url('{{ $scholarship->getBackgroundImageUrl() }}');"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-bsu-red to-red-900"></div>
                        <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                    @endif

                    <!-- Close Button -->
                    <button @click.stop="open = false" 
                            class="absolute top-4 right-4 p-2 bg-black/20 hover:bg-black/40 text-white rounded-full backdrop-blur-md transition-all duration-200 z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <!-- Title & Badge -->
                    <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                            <div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-white bg-white/20 backdrop-blur-md border border-white/30 mb-3">
                                    {{ ucfirst($scholarship->scholarship_type) }} Scholarship
                                </span>
                                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight shadow-black drop-shadow-lg">
                                    {{ $scholarship->scholarship_name }}
                                </h2>
                            </div>
                            <!-- Status Badge -->
                            <div class="flex items-center gap-2 bg-black/30 backdrop-blur-md px-4 py-2 rounded-lg border border-white/10">
                                <div class="w-2 h-2 rounded-full {{ $scholarship->is_active ? 'bg-green-400 animate-pulse' : 'bg-red-400' }}"></div>
                                <span class="text-sm font-medium text-white">
                                    {{ $scholarship->is_active ? 'Active & Accepting' : 'Currently Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <div class="p-6 sm:p-8 space-y-8">
                        
                        <!-- Description Section -->
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
                                {{ $scholarship->description }}
                            </p>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                            
                            <!-- Left Column: Key Details -->
                            <div class="space-y-6">
                                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                                    <h4 class="flex items-center text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                                        <svg class="w-5 h-5 mr-2 text-bsu-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Key Information
                                    </h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Grant Type</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $scholarship->grant_type ?? 'N/A')) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Renewable</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                @if($scholarship->renewal_allowed)
                                                    <span class="text-green-600 flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        Yes
                                                    </span>
                                                @else
                                                    <span class="text-gray-500">No</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Stackable</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                @if($scholarship->allow_existing_scholarship)
                                                    <span class="text-green-600 flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        Yes
                                                    </span>
                                                @else
                                                    <span class="text-gray-500">No</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Application Opens</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $scholarship->application_start_date ? $scholarship->application_start_date->format('M d, Y') : 'Immediately' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Submission Deadline</span>
                                            <span class="text-sm font-bold text-bsu-red">
                                                {{ $scholarship->submission_deadline ? $scholarship->submission_deadline->format('M d, Y') : 'No Deadline' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                                    <h4 class="flex items-center text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                                        <svg class="w-5 h-5 mr-2 text-bsu-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Eligibility Criteria
                                    </h4>
                                    <div class="space-y-3">
                                        @if($scholarship->min_gwa)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Minimum GWA</span>
                                            <span class="text-sm font-bold text-gray-900 dark:text-white bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded">
                                                {{ $scholarship->min_gwa }}
                                            </span>
                                        </div>
                                        @endif
                                        
                                        @if($scholarship->max_annual_income)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Max Annual Income</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">₱{{ number_format($scholarship->max_annual_income, 2) }}</span>
                                        </div>
                                        @endif

                                        @if($scholarship->eligibility_notes)
                                        <div class="mt-3 pt-3">
                                            <span class="text-xs font-bold text-gray-400 uppercase mb-1 block">Additional Notes</span>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 italic bg-white dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-600">
                                                "{{ $scholarship->eligibility_notes }}"
                                            </p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Requirements & Stats -->
                            <div class="space-y-6">
                                <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5 border border-gray-100 dark:border-gray-700 h-full">
                                    <h4 class="flex items-center text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                                        <svg class="w-5 h-5 mr-2 text-bsu-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Required Documents
                                    </h4>
                                    
                                    @if($scholarship->requiredDocuments->count() > 0)
                                        <ul class="space-y-3">
                                            @foreach($scholarship->requiredDocuments as $document)
                                                <li class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-300 group">
                                                    <div class="mt-0.5 w-5 h-5 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0 group-hover:bg-bsu-red group-hover:text-white transition-colors">
                                                        <svg class="w-3 h-3 text-bsu-red dark:text-red-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                    <span>{{ $document->document_name }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                                            <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-sm italic">No specific documents listed.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center shrink-0">
                    
                    @if($role === 'central')
                        <form action="{{ route('central.scholarships.destroy', $scholarship->id) }}" 
                              method="POST" 
                              onsubmit="return confirmDelete('{{ $scholarship->scholarship_name }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600 text-sm font-medium transition-colors flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Scholarship
                            </button>
                        </form>

                        <div class="flex gap-3">
                            <button @click.stop="open = false" class="px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition-colors">
                                Close
                            </button>
                            <a href="{{ route('central.scholarships.edit', $scholarship->id) }}" 
                               class="inline-flex items-center px-6 py-2.5 bg-bsu-red hover:bg-bsu-redDark text-white text-sm font-semibold rounded-lg shadow-lg shadow-bsu-red/30 hover:shadow-bsu-red/50 transform hover:-translate-y-0.5 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Scholarship
                            </a>
                        </div>

                    @elseif($role === 'student')
                        <div class="flex-1"></div> <!-- Spacer -->
                        <div class="flex gap-3 w-full sm:w-auto">
                            <button @click.stop="open = false" class="px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition-colors">
                                Close
                            </button>

                            @if($scholarship->is_scholar ?? false)
                                <!-- Scholar badge removed from here as per overlay design -->
                            @elseif($scholarship->applied)
                                <div class="flex gap-2 relative z-20">
                                    <button type="button" 
                                            onclick="openWithdrawModal('{{ $scholarship->id }}')"
                                            class="px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold rounded-lg shadow-lg transition-colors flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Withdraw
                                    </button>


                                </div>
                            @else
                                @if($hasActiveApplication && !$scholarship->allow_existing_scholarship)
                                    <button type="button"
                                            @click="$dispatch('show-warning')"
                                            class="px-6 py-2.5 bg-bsu-red hover:bg-bsu-redDark text-white text-sm font-semibold rounded-lg shadow-lg shadow-bsu-red/30 hover:shadow-bsu-red/50 transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                        </svg>
                                        Apply Now
                                    </button>
                                @else
                                    <form method="GET" action="{{ route('student.apply', ['scholarship_id' => $scholarship->id]) }}">
                                        <button type="submit" 
                                                class="px-6 py-2.5 bg-bsu-red hover:bg-bsu-redDark text-white text-sm font-semibold rounded-lg shadow-lg shadow-bsu-red/30 hover:shadow-bsu-red/50 transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                            </svg>
                                            Apply Now
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>

                    @elseif($role === 'sfao')
                        <div class="flex-1"></div> <!-- Spacer -->
                        <div class="flex gap-3">
                            <button @click.stop="open = false" class="px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition-colors">
                                Close
                            </button>
                            <button @click.stop="showReleaseModal = true" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow-lg transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Release Grant
                            </button>

                            <!-- Release Grant Confirmation Modal -->
                            <div x-show="showReleaseModal" 
                                 x-cloak
                                 class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0">
                                
                                <!-- Backdrop -->
                                <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm" @click.stop="showReleaseModal = false"></div>

                                <!-- Modal Card -->
                                <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col transform transition-all"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                                     @click.stop> <!-- Stop clicks inside modal from closing it -->

                                    <!-- Modal Content -->
                                    <div class="p-6 text-center">
                                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Confirm Release Grants</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Are you sure you want to release grants? This will generate grant slips and send emails to all active scholars under this scholarship.
                                        </p>
                                    </div>

                                    <!-- Modal Actions -->
                                    <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex gap-3 justify-end border-t border-gray-100 dark:border-gray-700">
                                        <button @click.stop="showReleaseModal = false" 
                                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            Cancel
                                        </button>
                                        <form action="{{ route('sfao.scholarships.release-grant', $scholarship->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-4 py-2 text-sm font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-lg shadow-green-600/30 transition-all transform hover:-translate-y-0.5">
                                                Confirm Release
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
</div>
