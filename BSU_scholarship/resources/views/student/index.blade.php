@php
  use Illuminate\Support\Facades\Session;
  use App\Models\User;

  if (!Session::has('user_id')) {
    return redirect()->route('login');
  }

  $user = User::find(session('user_id'));

  if (!$user) {
    Session::flush();
    return redirect()->route('login');
  }
@endphp

@extends('layouts.dashboard', ['user' => $user, 'title' => 'Student Dashboard'])

@section('sidebar-menu')
    @include('student.components.sidebar-menu', ['user' => $user])
@endsection

@section('navbar')
  <x-layout.navbar 
      title="Batangas State University" 
      subtitle="The National Engineering University"
      :user="$user" 
      :profile="false"
      :settings="true" 
      settings-click="$dispatch('switch-tab', 'account_settings')"
      :logout="true" 
  />
@endsection

@section('content')
<div x-data="{
    tab: 'announcements',
    subTab: 'all',
    unreadCount: {{ $unreadCount ?? 0 }},
    unreadCountScholarships: {{ $unreadCountScholarships ?? 0 }},
    unreadCountStatus: {{ $unreadCountStatus ?? 0 }},
    unreadCountComments: {{ $unreadCountComments ?? 0 }},
    
    tabMapping: {
        'all_scholarships': { tab: 'scholarships', subTab: 'all' },
        'private_scholarships': { tab: 'scholarships', subTab: 'private' },
        'government_scholarships': { tab: 'scholarships', subTab: 'government' },
        'my_scholarships': { tab: 'scholarships', subTab: 'my_scholarships' },
        'sfao_form': { tab: 'scholarships', subTab: 'form' },
        'tdp_form': { tab: 'scholarships', subTab: 'gvsreap_form' },
        'announcements': { tab: 'announcements', subTab: 'all' },
        'applied_scholarships': { tab: 'applied-scholarships', subTab: 'all' },
        'application_tracking': { tab: 'applied-scholarships', subTab: 'tracking' },
        'all_notifications': { tab: 'notifications', subTab: 'all' },
        'scholarship_notifications': { tab: 'notifications', subTab: 'scholarship_created' },
        'status_updates': { tab: 'notifications', subTab: 'application_status' },
        'comments': { tab: 'notifications', subTab: 'sfao_comment' },
        'all-app-forms': { tab: 'all-app-forms', subTab: 'all' },
        'account_settings': { tab: 'account', subTab: 'all' }
    },

    init() {
        // Restore state from URL
        const urlParams = new URLSearchParams(window.location.search);
        const urlTab = urlParams.get('tab');
        
        if (urlTab && this.tabMapping[urlTab]) {
            this.tab = this.tabMapping[urlTab].tab;
            this.subTab = this.tabMapping[urlTab].subTab;
        } else if (urlTab) {
            this.tab = urlTab;
        } else {
            this.tab = localStorage.getItem('studentActiveTab') || 'scholarships';
        }

        // Sync URL on state change
        this.$watch('tab', () => this.updateUrl());
        this.$watch('subTab', () => {
             this.updateUrl();
             this.$dispatch('subtab-changed', this.subTab);
        });
    },

    updateUrl() {
        localStorage.setItem('studentActiveTab', this.tab);
        let match = Object.keys(this.tabMapping).find(key => 
            this.tabMapping[key].tab === this.tab && 
            this.tabMapping[key].subTab === this.subTab
        );

        if (match) {
            const url = new URL(window.location);
            url.searchParams.set('tab', match);
            url.searchParams.delete('type');
            window.history.pushState({}, '', url);
        }
    }
  }"
  x-init="init()"
  @notification-changed.window="
    const status = $event.detail.status;
    const type = $event.detail.type;
    if (status === 'read') {
        if (unreadCount > 0) unreadCount--;
    } else if (status === 'unread') {
        unreadCount++;
    }
  "
  @notifications-read-all.window="unreadCount = 0;"

  x-on:switch-tab.window="
      const key = $event.detail;
      if (tabMapping[key]) {
          tab = tabMapping[key].tab;
          subTab = tabMapping[key].subTab;
      } else {
          // Direct tab switch fallback
          tab = key;
      }
  "
>

    <!-- Scholarships Tab -->
    <div x-show="tab === 'scholarships'" x-transition>
      <div x-show="subTab !== 'form' && subTab !== 'gvsreap_form'" x-transition>
        @include('student.scholarships.index')
      </div>
      <!-- SFAO Form -->
      <div x-show="subTab === 'form'" x-transition>
          @include('student.application_forms.sfao')
      </div>
      <!-- TDP Form -->
      <div x-show="subTab === 'gvsreap_form'" x-transition>
          @include('student.application_forms.tdp')
      </div>
    </div>



    <!-- Stats Overview (Only visible on home/scholarships tab) -->
    <div x-show="tab === 'scholarships' && subTab === 'all'" class="mb-8">
        <!-- Stats content here -->
    </div>
    
    <!-- Applied Scholarships Tab -->
    <div x-show="tab === 'applied-scholarships' || tab === 'applied_scholarships' || tab === 'application_tracking'" x-transition>
      @if(isset($applications))
          @include('student.applications.index')
      @else
          <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
              <strong>Critical Error:</strong> The system cannot find your applications data. 
              <br>This confirms that <code>DashboardController.php</code> was not successfully updated on the server.
              <br>Please re-upload <code>app/Http/Controllers/DashboardController.php</code>.
          </div>
      @endif
    </div>
    
    <!-- Notifications Tab -->
    <div x-show="tab === 'notifications'" x-transition>
      @include('student.notifications.index')
    </div>

    <!-- Announcements Tab -->
    <div x-show="tab === 'announcements'" x-transition>
      @include('student.announcements.index')
    </div>

    <!-- Application Forms Tab -->
    <div x-show="tab === 'all-app-forms'" x-transition>
        @include('student.application-forms.index')
    </div>

    <!-- Account Settings Tab -->
    <div x-show="tab === 'account'" x-transition>
        @include('student.settings.index')
    </div>

    <!-- Application Limit Warning Modal -->
    <div x-data="{ showWarning: false }"
         x-on:show-warning.window="showWarning = true"
         x-show="showWarning" 
         x-cloak
         class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showWarning = false"></div>

        <!-- Modal Card -->
        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden transform transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 scale-95">
             
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Application Limit Reached</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    You already have an active application. This scholarship does not allow multiple simultaneous applications. Please wait for your current application to be processed.
                </p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex justify-center border-t border-gray-100 dark:border-gray-700">
                <button @click="showWarning = false" 
                        class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg shadow-lg transition-colors">
                    Understood
                </button>
            </div>
        </div>
    </div>

</div>

{{-- Success Message Modal --}}
@if(session('success'))
<div x-data="{ showModal: true }" 
     x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    {{-- Background Overlay --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
         @click="showModal = false"></div>

    {{-- Modal Content --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all">
        
        {{-- Success Icon --}}
        <div class="flex justify-center mb-6">
            <div class="flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/30">
                <svg class="h-12 w-12 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        
        {{-- Title and Message --}}
        <div class="text-center mb-8">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3" id="modal-title">
                Success!
            </h3>
            <p class="text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ session('success') }}
            </p>
        </div>
        
        {{-- Action Button --}}
        <button type="button"
                @click="showModal = false"
                class="w-full inline-flex justify-center items-center px-6 py-3 bg-green-600 text-white text-base font-semibold rounded-xl shadow-lg hover:bg-green-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
            Continue
            <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </button>
    </div>
</div>
@endif

{{-- Error Message Modal --}}
@if(session('error'))
<div x-data="{ showModal: true }" 
     x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    {{-- Background Overlay --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
         @click="showModal = false"></div>

    {{-- Modal Content --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all">
        
        {{-- Error Icon --}}
        <div class="flex justify-center mb-6">
            <div class="flex items-center justify-center h-20 w-20 rounded-full bg-red-100 dark:bg-red-900/30">
                <svg class="h-12 w-12 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
        </div>
        
        {{-- Title and Message --}}
        <div class="text-center mb-8">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3" id="modal-title">
                Error
            </h3>
            <p class="text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ session('error') }}
            </p>
        </div>
        
        {{-- Action Button --}}
        <button type="button"
                @click="showModal = false"
                class="w-full inline-flex justify-center items-center px-6 py-3 bg-red-600 text-white text-base font-semibold rounded-xl shadow-lg hover:bg-red-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
            Close
        </button>
    </div>
</div>
@endif

{{-- Withdraw Confirmation Modal --}}
<div x-data="{ showModal: false, scholarshipId: null }"
     x-init="
        $watch('showModal', value => {
            if (!value) setTimeout(() => scholarshipId = null, 300);
        })
     "
     x-on:open-withdraw-modal.window="
        scholarshipId = $event.detail;
        showModal = true;
     "
     x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">

    {{-- Background Overlay --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
         @click="showModal = false"></div>

    {{-- Modal Content --}}
    <div x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all">

        {{-- Icon --}}
        <div class="flex justify-center mb-6">
            <div class="flex items-center justify-center h-20 w-20 rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                <svg class="h-10 w-10 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        {{-- Title and Message --}}
        <div class="text-center mb-8">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3" id="modal-title">
                Withdraw Application?
            </h3>
            <p class="text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                Are you sure you want to withdraw your application? This action cannot be undone.
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-4">
            <button type="button"
                    @click="showModal = false"
                    class="flex-1 px-4 py-3 bg-white text-gray-700 font-semibold rounded-xl border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
                Cancel
            </button>
            <form method="POST" action="{{ route('student.withdraw') }}" class="flex-1">
                @csrf
                <input type="hidden" name="scholarship_id" :value="scholarshipId">
                <button type="submit"
                        class="w-full px-4 py-3 bg-red-600 text-white font-semibold rounded-xl shadow-lg hover:bg-red-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                    Withdraw
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openWithdrawModal(scholarshipId) {
        window.dispatchEvent(new CustomEvent('open-withdraw-modal', { detail: scholarshipId }));
    }
</script>

@endsection
