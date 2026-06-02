@php
  use Illuminate\Support\Facades\Session;
  use App\Models\User;
  $loggedInUser = User::find(session('user_id'));
@endphp

<!DOCTYPE html>
<html lang="en"
    :class="{ 'dark': darkMode }"
    x-data="{ darkMode: localStorage.getItem('darkMode_{{ session('user_id') }}') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode_{{ session('user_id') }}', val))">
<head>
    <script>
        if (localStorage.getItem('darkMode_{{ session('user_id') }}') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Validate Endorsed Applicant</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-50 min-h-screen">

  <x-layout.navbar 
    :user="$loggedInUser" 
    title="Validate Endorsed Applicant" 
    :sidebar="false" 
    :logout="true"
    :settings="true"
    :back-url="route('central.dashboard', ['tab' => 'endorsed-applicants'])"
    back-text="Back to Dashboard"
  />

  <div class="max-w-6xl mx-auto p-4 md:p-8 mt-6">


    <!-- Applicant Profile Card -->
    <div class="bg-white rounded-xl shadow-lg border-2 border-bsu-red p-6 mb-6">
      <div class="flex flex-col md:flex-row gap-6">
        <!-- Avatar -->
        <div class="flex-shrink-0">
          <img src="{{ $user && $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) : asset('images/default-avatar.png') }}"
               alt="Profile Picture"
               class="h-24 w-24 md:h-28 md:w-28 rounded-full object-cover border-4 border-bsu-red">
        </div>
        <!-- Identity & Quick Info -->
        <div class="flex-1">
          <div class="text-sm text-gray-500 uppercase tracking-wide font-medium">Student</div>
          <div class="text-xl font-bold text-bsu-red mt-1">{{ $user->name }}</div>
          <div class="text-sm text-gray-600">{{ $user->email }}</div>
          <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
              <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Campus</div>
              <div class="text-sm font-bold text-bsu-red mt-1">{{ $user->campus->name ?? 'N/A' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
              <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Program</div>
              <div class="text-sm font-bold text-bsu-red mt-1">{{ $user->form->program ?? 'N/A' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
              <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Year Level</div>
              <div class="text-sm font-bold text-bsu-red mt-1">{{ $user->form->year_level ?? 'N/A' }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
              <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">GWA</div>
              <div class="text-sm font-bold text-bsu-red mt-1">{{ $user->form->previous_gwa ?? 'N/A' }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Profile Details -->
      <div class="mt-6 pt-6 border-t-2 border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div>
          <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Sex</div>
          <div class="font-bold text-bsu-red mt-1">{{ $user->form->sex ?? 'N/A' }}</div>
        </div>
        <div>
          <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Age</div>
          <div class="font-bold text-bsu-red mt-1">{{ $user->form->age ?? 'N/A' }}</div>
        </div>
        <div>
          <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Contact</div>
          <div class="font-bold text-bsu-red mt-1">{{ $user->form->telephone ?? $user->form->email ?? 'N/A' }}</div>
        </div>
        <div class="md:col-span-3">
          <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Address</div>
          <div class="font-medium text-gray-800 mt-1">
            @php
              $addressParts = array_filter([
                $user->form->street_barangay ?? null,
                $user->form->town_city ?? null,
                $user->form->province ?? null,
                $user->form->zip_code ?? null,
              ]);
            @endphp
            {{ count($addressParts) ? implode(', ', $addressParts) : 'N/A' }}
          </div>
        </div>
      </div>
    </div>

    <!-- Scholarship & Application -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="md:col-span-3 bg-white rounded-xl shadow-lg border-2 border-bsu-red p-6">
        <div class="text-sm text-gray-600 uppercase tracking-wide font-semibold mb-2">Scholarship</div>
        <div class="text-xl font-bold text-bsu-red mb-4">{{ $scholarship->scholarship_name }}</div>
        <div class="grid grid-cols-3 gap-4 text-sm">
          <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
            <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Type</div>
            <div class="font-bold text-bsu-red mt-1">{{ ucfirst($scholarship->scholarship_type) }}</div>
          </div>

          <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
            <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Grant Amount</div>
            <div class="font-bold text-bsu-red mt-1">{{ $scholarship->grant_amount ? '₱' . number_format((float) $scholarship->grant_amount, 2) : 'TBD' }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
            <div class="text-xs uppercase text-gray-600 font-semibold tracking-wide">Status</div>
            <div class="font-bold text-bsu-red mt-1">{{ ucfirst($application->status) }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Documents -->
    <div class="bg-white rounded-xl shadow-lg border-2 border-bsu-red p-6 mb-6">
      <div class="flex items-center justify-between mb-4 pb-3 border-b-2 border-bsu-red">
        <h2 class="text-lg font-bold text-bsu-red uppercase tracking-wide">Submitted Documents</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-bsu-red">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Category</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Size</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Evaluation</th>
              <th class="px-6 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">View</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @forelse($submittedDocuments as $doc)
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">{{ $doc->getDocumentCategoryDisplayName() }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $doc->document_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $doc->getFileTypeDisplayName() }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $doc->getFileSizeFormatted() }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $doc->getEvaluationStatusBadgeColor() }}">
                    {{ $doc->getEvaluationStatusDisplayName() }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <a href="{{ $doc->getViewUrl() }}" target="_blank" class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-white hover:bg-bsu-red hover:text-white border-2 border-bsu-red text-bsu-red transition-colors" title="View document">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No submitted documents found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-4 border-t-2 border-gray-200" x-data="{ showAcceptModal: false, showRejectModal: false, rejectionReason: '', showRejectConfirm: false }">
      <!-- Cancel/Back button removed as per request, using Navbar back button -->
      
      <!-- Reject Button -->
      <button @click="showRejectModal = true" class="px-6 py-2 text-sm font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
        Reject
      </button>
      
      <!-- Accept Button -->
      <button @click="showAcceptModal = true" class="px-6 py-2 text-sm font-semibold bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
        Accept
      </button>

      <!-- Accept Confirmation Modal -->
      <div x-show="showAcceptModal" 
           x-cloak
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0"
           x-transition:enter-end="opacity-100"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="opacity-100"
           x-transition:leave-end="opacity-0"
           class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
           @click.self="showAcceptModal = false">
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
          <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-green-100 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Accept Application</h3>
          <p class="text-gray-600 mb-6 text-center">Are you sure you want to accept this application? The application will be validated and the student will be notified.</p>
          
          <form method="POST" action="{{ route('central.endorsed.accept', $application->id) }}" id="acceptForm">
            @csrf
            <div class="flex justify-end gap-3">
              <button 
                type="button" 
                @click="showAcceptModal = false"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                Cancel
              </button>
              <button 
                type="submit"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                Confirm Acceptance
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Rejection Modal -->
      <div x-show="showRejectModal && !showRejectConfirm" 
           x-cloak
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0"
           x-transition:enter-end="opacity-100"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="opacity-100"
           x-transition:leave-end="opacity-0"
           class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
           @click.self="showRejectModal = false; rejectionReason = ''">
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Application</h3>
          <p class="text-gray-600 mb-4">Please provide a reason for rejecting this application. The student will not be able to apply to this scholarship again.</p>
          
          <div class="mb-4">
            <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
            <textarea 
              id="rejection_reason" 
              name="rejection_reason" 
              x-model="rejectionReason"
              rows="4" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
              placeholder="Enter the reason for rejection..."
              required></textarea>
            <p class="mt-1 text-xs text-gray-500">This reason will be shown to the student.</p>
          </div>
          
          <div class="flex justify-end gap-3">
            <button 
              type="button" 
              @click="showRejectModal = false; rejectionReason = ''"
              class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
              Cancel
            </button>
            <button 
              type="button"
              @click="if(rejectionReason.trim()) { showRejectConfirm = true; }"
              :disabled="!rejectionReason.trim()"
              :class="rejectionReason.trim() ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-400 cursor-not-allowed'"
              class="px-4 py-2 text-white rounded-lg transition-colors">
              Continue
            </button>
          </div>
        </div>
      </div>

      <!-- Rejection Confirmation Modal -->
      <div x-show="showRejectModal && showRejectConfirm" 
           x-cloak
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0"
           x-transition:enter-end="opacity-100"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="opacity-100"
           x-transition:leave-end="opacity-0"
           class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
           @click.self="showRejectModal = false; showRejectConfirm = false; rejectionReason = ''">
        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
          <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Confirm Rejection</h3>
          <p class="text-gray-600 mb-4 text-center">Are you sure you want to reject this application? The student will not be able to apply to this scholarship again.</p>
          
          <div class="bg-gray-50 rounded-lg p-3 mb-4">
            <p class="text-sm font-medium text-gray-700 mb-1">Rejection Reason:</p>
            <p class="text-sm text-gray-600" x-text="rejectionReason"></p>
          </div>
          
          <form method="POST" action="{{ route('central.endorsed.reject', $application->id) }}" id="rejectForm">
            @csrf
            <input type="hidden" name="rejection_reason" :value="rejectionReason">
            <div class="flex justify-end gap-3">
              <button 
                type="button" 
                @click="showRejectConfirm = false"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                Back
              </button>
              <button 
                type="submit"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                Confirm Rejection
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>
</html>


