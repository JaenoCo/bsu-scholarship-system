@php
  use Illuminate\Support\Facades\Session;
  use Illuminate\Support\Facades\Redirect;
  use App\Models\User;

  // Redirect to login if session has ended
  if (!Session::has('user_id')) {
    return redirect()->route('login');
  }

  $user = User::find(session('user_id'));

  // If no user found, flush session and redirect
  if (!$user) {
    Session::flush();
    return redirect()->route('login');
  }
@endphp

<!DOCTYPE html>
<html lang="en"
  :class="{ 'dark': darkMode }"
  x-data="{
    sidebarOpen: false,
    rightSidebarOpen: false,
    isDesktop: window.innerWidth >= 768,
    tab: 'scholarships',
    subTab: 'all',
    darkMode: localStorage.getItem('darkMode_{{ $user->id }}') === 'true',
    showLogoutModal: false,
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
        'applied_scholarships': { tab: 'applied-scholarships', subTab: 'all' },
        'application_tracking': { tab: 'applied-scholarships', subTab: 'tracking' },
        'all_notifications': { tab: 'notifications', subTab: 'all' },
        'scholarship_notifications': { tab: 'notifications', subTab: 'scholarship_created' },
        'status_updates': { tab: 'notifications', subTab: 'application_status' },
        'comments': { tab: 'notifications', subTab: 'sfao_comment' },
        'account_settings': { tab: 'account', subTab: 'all' }
    },

    init() {
        // Dark Mode watcher
        this.$watch('darkMode', val => localStorage.setItem('darkMode_{{ $user->id }}', val));
        
        // Resize listener
        window.addEventListener('resize', () => {
            this.isDesktop = window.innerWidth >= 768;
        });

        // Restore state from URL
        const urlParams = new URLSearchParams(window.location.search);
        const urlTab = urlParams.get('tab');
        
        if (urlTab && this.tabMapping[urlTab]) {
            this.tab = this.tabMapping[urlTab].tab;
            this.subTab = this.tabMapping[urlTab].subTab;
        } else {
            // Fallback to localStorage or defaults
            this.tab = localStorage.getItem('activeTab') || 'scholarships';
            // Default subTabs for main tabs
            if (this.tab === 'scholarships') this.subTab = 'all';
            else if (this.tab === 'applied-scholarships') this.subTab = 'all';
            else if (this.tab === 'notifications') this.subTab = 'all';
        }

        // Sync URL on state change
        this.$watch('tab', () => this.updateUrl());
        this.$watch('subTab', () => {
             this.updateUrl();
             this.$dispatch('subtab-changed', this.subTab);
        });
    },

    updateUrl() {
        // Save actibe tab to local storage
        localStorage.setItem('activeTab', this.tab);

        // Find matching URL key
        let match = Object.keys(this.tabMapping).find(key => 
            this.tabMapping[key].tab === this.tab && 
            this.tabMapping[key].subTab === this.subTab
        );

        if (match) {
            const url = new URL(window.location);
            url.searchParams.set('tab', match);
            url.searchParams.delete('type'); // Clean up deprecated param
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
        if (type === 'scholarship_created' && unreadCountScholarships > 0) unreadCountScholarships--;
        if (type === 'application_status' && unreadCountStatus > 0) unreadCountStatus--;
        if (type === 'sfao_comment' && unreadCountComments > 0) unreadCountComments--;
    } else if (status === 'unread') {
        unreadCount++;
        if (type === 'scholarship_created') unreadCountScholarships++;
        if (type === 'application_status') unreadCountStatus++;
        if (type === 'sfao_comment') unreadCountComments++;
    }
    console.log('Notification changed:', { status, type, unreadCount, unreadCountScholarships, unreadCountStatus, unreadCountComments });
  "
  @notifications-read-all.window="
    unreadCount = 0;
    unreadCountScholarships = 0;
    unreadCountStatus = 0;
    unreadCountComments = 0;
  "
  x-on:switch-tab.window="
    tab = $event.detail;
  ">

<head>
  <script>
    // Immediately apply dark mode preference to prevent FOUC
    if (localStorage.getItem('darkMode_{{ $user->id }}') === 'true' || (!('darkMode_{{ $user->id }}' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>
  <meta charset="UTF-8">
  <meta http-equiv="Cache-Control" content="no-store" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Student Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  <!-- Tailwind & Alpine.js -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://unpkg.com/alpinejs" defer></script>


  <style>
    /* Custom scrollbar styling - minimized and subtle */
    [x-cloak] { display: none !important; }
    nav::-webkit-scrollbar {
      width: 6px;
    }
    nav::-webkit-scrollbar-track {
      background: transparent;
    }
    nav::-webkit-scrollbar-thumb {
      background: rgba(156, 163, 175, 0.3);
      border-radius: 3px;
    }
    nav::-webkit-scrollbar-thumb:hover {
      background: rgba(156, 163, 175, 0.5);
    }
    .dark nav::-webkit-scrollbar-thumb {
      background: rgba(156, 163, 175, 0.2);
    }
    .dark nav::-webkit-scrollbar-thumb:hover {
      background: rgba(156, 163, 175, 0.4);
    }
  </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 dark:text-white min-h-screen font-sans">


  <!-- Sidebar -->
  @include('student.components.navigation.sidebar', ['user' => $user, 'unreadCount' => $unreadCount])

  <!-- Mobile Overlay Backdrop -->
  <div x-show="sidebarOpen" 
       x-transition:enter="transition-opacity ease-linear duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity ease-linear duration-300"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 bg-gray-900/50 z-40 md:hidden"
       @click="sidebarOpen = false"
       x-cloak>
  </div>

  <!-- Logout Confirmation Modal -->
  @include('student.components.modals.logout')

  <!-- Main Header -->
  <header class="flex items-center justify-between px-8 py-4 bg-[#2f2f2f] dark:bg-gray-800 shadow-sm sticky top-0 z-30 border-b border-gray-700 transition-all duration-300"
          :class="{ 'md:ml-64': sidebarOpen, 'md:mr-64': rightSidebarOpen }"
          :style="isDesktop && rightSidebarOpen ? 'margin-right: 16rem;' : ''">
    <!-- Branding -->
    <div class="flex items-center space-x-2 md:space-x-3">
        <button @click="sidebarOpen = true" class="text-white hover:text-gray-300 focus:outline-none mr-1 md:mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <img src="{{ asset('images/Batangas_State_Logo.png') }}" alt="Logo" class="h-10 md:h-12 w-auto">
        <div class="text-white">
            <div class="text-sm md:text-base font-bold leading-tight">Batangas State University</div>
            <div class="text-xs font-light hidden md:block">The National Engineering University</div>
        </div>
    </div>

    <!-- Dark Mode Toggle & User Profile -->
    <div class="flex items-center gap-4">
        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode" 
                class="p-2 rounded-full hover:bg-gray-700 transition-colors focus:outline-none"
                :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <!-- Sun Icon (for Dark Mode) -->
            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <!-- Moon Icon (for Light Mode) -->
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        <!-- User Profile Icon -->
        <div class="relative">
        <button @click="rightSidebarOpen = true" class="flex items-center focus:outline-none">
            <img src="{{ $user && $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) . '?' . now()->timestamp : asset('images/default-avatar.png') }}"
                 alt="Profile"
                 class="h-10 w-10 rounded-full border-2 border-white object-cover hover:border-bsu-red transition-colors">
        </button>
    </div>
  </header>

  <!-- Right Sidebar -->
  <aside class="fixed inset-y-0 right-0 w-64 bg-white dark:bg-gray-800 shadow-xl transform transition-transform duration-300 z-50 flex flex-col"
         :class="rightSidebarOpen ? 'translate-x-0' : 'translate-x-full'"
         x-cloak>
      <div class="h-full flex flex-col py-6 overflow-y-scroll">
        <div class="px-4 sm:px-6">
          <div class="flex items-start justify-between">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Profile</h2>
            <div class="ml-3 h-7 flex items-center">
              <button @click="rightSidebarOpen = false" class="bg-white dark:bg-gray-800 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red">
                <span class="sr-only">Close panel</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>
        <div class="mt-6 relative flex-1 px-4 sm:px-6">
          <!-- Profile Content -->
          <div class="flex flex-col items-center">
              <img src="{{ $user && $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) . '?' . now()->timestamp : asset('images/default-avatar.png') }}"
                   alt="Profile Picture"
                   class="h-32 w-32 rounded-full border-4 border-gray-200 dark:border-gray-700 object-cover mb-4">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user?->name ?? 'Student User' }}</h3>
          </div>

          <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
              <button @click="tab = 'account'" class="w-full flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Account Settings
              </button>
              <button @click="showLogoutModal = true" class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Logout
              </button>
          </div>
        </div>
      </div>
  </aside>

  <!-- Main Content -->
  <main class="p-4 md:p-8 min-h-screen bg-white dark:bg-gray-900 transition-all duration-300"
          :class="{ 'md:ml-64': sidebarOpen, 'md:mr-64': rightSidebarOpen }"
          :style="isDesktop && rightSidebarOpen ? 'margin-right: 16rem;' : ''">

    <!-- Toasts for success and errors -->
    @if (session('success'))
      <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition.opacity class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40">
        <div class="bg-white dark:bg-gray-800 border border-green-400 text-green-700 dark:text-green-300 px-6 py-5 rounded-xl shadow-xl flex items-center space-x-4">
          <svg class="w-10 h-10 text-green-500 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          <div class="text-lg font-medium">{{ session('success') }}</div>
        </div>
      </div>
    @endif

    @if ($errors->any())
      <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition.opacity class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40">
        <div class="bg-white dark:bg-gray-800 border border-red-400 text-red-700 dark:text-red-300 px-6 py-5 rounded-xl shadow-xl flex items-center space-x-4">
          <svg class="w-10 h-10 text-red-500 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <div class="text-lg font-medium">{{ $errors->first() }}</div>
        </div>
      </div>
    @endif

    <!-- Tabs -->
    <!-- Scholarships Tab -->
    <!-- Scholarships Tab -->
    <div x-show="tab === 'scholarships'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100">
      <!-- Scholarships Content -->
      <!-- Scholarships Content -->
      <!-- Scholarships Content (Unified) -->
      <div x-show="subTab !== 'form' && subTab !== 'gvsreap_form'" 
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 transform scale-95"
           x-transition:enter-end="opacity-100 transform scale-100">
        @include('student.partials.tabs.scholarships')
      </div>
      
      <!-- Application Form Sub-tab -->
      <!-- Application Form Sub-tab (Unified) -->
      <div x-show="subTab === 'form' || subTab === 'gvsreap_form'" 
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 transform scale-95"
           x-transition:enter-end="opacity-100 transform scale-100">
          @include('student.partials.tabs.application-form')
      </div>
    </div>

    <!-- Applied Scholarships Tab -->
    <div x-show="tab === 'applied-scholarships'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100">
      @include('student.partials.tabs.applications')
    </div>
    

    
    <!-- Notifications Tab -->
    <!-- Notifications Tab -->
    <div x-show="tab === 'notifications'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100">
      @include('student.partials.tabs.notifications')
    </div>
    
    @include('student.partials.tabs.account')
  </main>

  <!-- Fix Safari Back Cache Bug -->
  <script>
    window.addEventListener("pageshow", function (event) {
      if (event.persisted) {
        window.location.reload();
      }
    });
    
    // Handle dropdown states
    document.addEventListener('alpine:init', () => {
      Alpine.data('dashboard', () => ({
        init() {
          // Open scholarships dropdown if scholarships tab is active
          if (this.tab === 'scholarships') {
            this.scholarshipsDropdownOpen = true;
          }
          // Open applications dropdown if applications tab is active
          if (this.tab === 'applied-scholarships' || this.tab === 'application-tracking') {
            this.applicationsDropdownOpen = true;
          }
        }
      }));
    });
  </script>
</body>
</html>
