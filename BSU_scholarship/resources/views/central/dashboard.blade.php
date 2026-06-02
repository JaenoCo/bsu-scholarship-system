@php
  use Illuminate\Support\Facades\Session;
  use Illuminate\Support\Facades\Redirect;
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


<!DOCTYPE html>
<html lang="en"
  :class="{ 'dark': darkMode }"
  x-data="{
    sidebarOpen: JSON.parse(localStorage.getItem('sidebarOpen')) || false,
    rightSidebarOpen: JSON.parse(localStorage.getItem('rightSidebarOpen')) || false,
    isDesktop: window.innerWidth >= 768,
    tab: {{ json_encode(request()->query("tab")) }} || localStorage.getItem('activeTab') || 'all_scholarships',
    currentStatsCampus: 'all',
    darkMode: localStorage.getItem('darkMode_{{ $user->id }}') === 'true',
    showLogoutModal: false,
    initialParams: new URLSearchParams(window.location.search),
    
    getTabGroup(tab) {
        if (tab === 'all_scholarships' || tab === 'private_scholarships' || tab === 'government_scholarships') return 'scholarships';
        if (tab === 'sfao_reports') return 'reports';
        if (tab === 'all_statistics' || (tab.endsWith('_statistics') && tab !== 'all_statistics')) return 'statistics';
        if (tab === 'all_scholars' || tab === 'new_scholars' || tab === 'old_scholars') return 'scholars';
        if (tab === 'endorsed_applicants' || tab === 'rejected_applicants') return 'applications';
        if (tab === 'staff') return 'staff';
        return 'default';
    },

    normalizeTab(t) {
        const map = {
            'scholarships': 'all_scholarships',
            'scholarships-private': 'private_scholarships',
            'scholarships-government': 'government_scholarships',
            'scholars': 'all_scholars',
            'scholars-new': 'new_scholars',
            'scholars-old': 'old_scholars',
            'endorsed-applicants': 'endorsed_applicants',
            'rejected-applicants': 'rejected_applicants',
            'reports': 'sfao_reports',
            'statistics': 'all_statistics',
            'settings': 'account_settings'
        };
        return map[t] || t;
    },

    getGroupKeys(group) {
        const keys = {
            'scholarships': ['sort_by', 'sort_order', 'page_all', 'page_private', 'page_gov'],
            'reports': ['type', 'campus', 'sort', 'order', 'page_submitted', 'page_reviewed', 'page_approved', 'page_rejected', 'status'],
            'statistics': ['timePeriod', 'campus'],
            'scholars': ['page_scholars_all', 'page_scholars_new', 'page_scholars_old', 'sort_by', 'status', 'type'],
            'applications': ['sort_by', 'status_filter', 'campus_filter', 'scholarship_filter']
        };
        return keys[group] || [];
    },

    switchTab(newTab) {
        // 1. Identify Groups
        const currentGroup = this.getTabGroup(this.tab);
        const newGroup = this.getTabGroup(newTab);

        // 2. Save Current Group State
        if (currentGroup !== 'default') {
            const currentParams = new URLSearchParams(window.location.search);
            const groupKeys = this.getGroupKeys(currentGroup);
            const stateToSave = new URLSearchParams();
            
            groupKeys.forEach(key => {
                if (currentParams.has(key)) {
                    stateToSave.set(key, currentParams.get(key));
                }
            });
            localStorage.setItem('groupState_' + currentGroup, stateToSave.toString());
        }

        // 3. Restore New Group State
        let newParams = new URLSearchParams();
        if (newGroup !== 'default') {
            const savedState = localStorage.getItem('groupState_' + newGroup);
            if (savedState) {
                newParams = new URLSearchParams(savedState);
            }
        }
        
        // Always set the new tab
        newParams.set('tab', newTab);

        // Special Case: Remove 'campus' param for campus-specific statistics tabs
        // The controller resolves the campus from the tab name, so the param is redundant
        if (newTab.endsWith('_statistics') && newTab !== 'all_statistics') {
            newParams.delete('campus');
        }

        // CLEANUP: Remove default values to keep URL clean
        const defaults = {
            'campus': 'all',
            'campus_filter': 'all',
            'timePeriod': 'all',
            'status_filter': 'all',
            'scholarship_filter': 'all',
            'sort_by': 'created_at',
            'sort_order': 'desc',
            'type': 'all',
            'status': 'all'
        };

        // Remove pagination parameters if they are page 1
        // We iterate on a copy of keys to avoid modification issues during iteration
        Array.from(newParams.keys()).forEach(key => {
             if (key.startsWith('page_') && newParams.get(key) === '1') {
                 newParams.delete(key);
             }
        });

        // Remove standard defaults
        for (const [key, def] of Object.entries(defaults)) {
            if (newParams.get(key) === def) {
                newParams.delete(key);
            }
        }

        // 4. Construct New URL
        const newUrl = new URL(window.location);
        newUrl.search = newParams.toString();

        // 5. Smart Switch Logic
        // Compare the target params with what the page was initially loaded with.
        
        const initialParamsCopy = new URLSearchParams(this.initialParams);
        initialParamsCopy.delete('tab');
        
        const checkParams = new URLSearchParams(newParams);
        checkParams.delete('tab');

        // Logic: activeTabParams == initialPageParams ? switch : reload
        // Exception: Statistics tab is self-healing via Ajax, so we can always pushState for it (unless users prefers full reload consistency).
        // Actually, if we use pushState for Statistics, our previous fix in statistics.blade.php handles the data fetching.
        // For other tabs (Blade rendered), we MUST reload if params differ.
        
        if (checkParams.toString() === initialParamsCopy.toString() || newGroup === 'statistics') {
            this.tab = newTab;
            window.history.pushState({}, '', newUrl);
        } else {
            window.location.href = newUrl.toString();
        }
    }
  }" 
  @change-stats-campus.window="currentStatsCampus = $event.detail"
  @resize.window="isDesktop = window.innerWidth >= 768"
  x-init="
    $watch('darkMode', val => localStorage.setItem('darkMode_{{ $user->id }}', val));
    $watch('tab', val => localStorage.setItem('activeTab', val));
    $watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
    $watch('rightSidebarOpen', val => localStorage.setItem('rightSidebarOpen', val));
    
    // Ensure initialParams is set correctly
    initialParams = new URLSearchParams(window.location.search);
    
    // Normalize tab for backward compatibility
    this.tab = this.normalizeTab(this.tab);
  ">

  <script>
    // Immediately apply dark mode preference to prevent FOUC
    if (localStorage.getItem('darkMode_{{ $user->id }}') === 'true' || (!('darkMode_{{ $user->id }}' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="Cache-Control" content="no-store" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Central Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  @vite(['resources/css/app.css', 'resources/js/app.js'])



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
  <aside class="fixed inset-y-0 left-0 w-64 bg-bsu-red text-white dark:bg-gray-800 transform transition-transform duration-300 z-50 flex flex-col"
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
         x-cloak>
    
    <!-- Close Button -->
    <div class="absolute top-0 right-0 pt-4 pr-4">
      <button @click="sidebarOpen = false" class="text-white hover:text-gray-200 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Profile Info - Removed from here -->

    <!-- Navigation - Scrollable -->
    <nav class="mt-6 px-4 pb-4 overflow-y-auto flex-1 space-y-4 custom-scrollbar">
      <!-- Scholarships Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Scholarships
        </div>
        <button @click="switchTab('all_scholarships')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'all_scholarships' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          All Scholarships
        </button>
        <button @click="switchTab('private_scholarships')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'private_scholarships' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          Private Scholarships
        </button>
        <button @click="switchTab('government_scholarships')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'government_scholarships' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
          </svg>
          Government Scholarships
        </button>
      </div>

      <!-- Scholars Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Scholars
        </div>
        <button @click="switchTab('all_scholars')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'all_scholars' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          All Scholars
        </button>
        <button @click="switchTab('new_scholars')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'new_scholars' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
          New Scholars
        </button>
        <button @click="switchTab('old_scholars')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'old_scholars' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Old Scholars
        </button>
        <button @click="switchTab('endorsed_applicants')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'endorsed_applicants' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Endorsed Applicants
        </button>
        <button @click="switchTab('rejected_applicants')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'rejected_applicants' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Rejected Applicants
        </button>
      </div>

      <!-- Reports Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Reports
        </div>
        <button @click="switchTab('sfao_reports')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'sfao_reports' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          SFAO Reports
        </button>
      </div>

      <!-- Statistics Header -->
       <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Statistics
        </div>
        <button @click="switchTab('all_statistics'); $dispatch('change-stats-campus', 'all')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'all_statistics' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
          </svg>
          All Campus Statistics
        </button>

        <!-- Dynamic Campus Statistics Tabs -->
        @foreach($campuses as $campus)
        <button @click="switchTab('{{ strtolower(str_replace(' ', '_', $campus->name)) }}_statistics'); $dispatch('change-stats-campus', '{{ $campus->id }}')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2 pl-8"
                :class="tab === '{{ strtolower(str_replace(' ', '_', $campus->name)) }}_statistics' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            {{ $campus->name }}
        </button>
        @endforeach
      </div>

      <!-- Manage Users Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Manage Users
        </div>
        <button @click="switchTab('staff')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'staff' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          SFAO
        </button>
      </div>
    </nav>



      </div>
    </nav>
  </aside>

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
  <div x-show="showLogoutModal" 
       x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
       @click.self="showLogoutModal = false">
    <div x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Confirm Logout</h3>
      <p class="text-gray-600 dark:text-gray-300 mb-6">Are you sure you want to logout?</p>
      <div class="flex justify-end gap-3">
        <button @click="showLogoutModal = false"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
          Cancel
        </button>
        <a href="{{ url('/logout') }}"
           onclick="localStorage.removeItem('activeTab'); localStorage.removeItem('groupState_scholarships'); localStorage.removeItem('groupState_reports'); localStorage.removeItem('groupState_statistics'); localStorage.removeItem('groupState_scholars'); localStorage.removeItem('groupState_applications');"
           class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
          Logout
        </a>
      </div>
    </div>
  </div>

  <!-- Mobile Top Bar -->
  <header class="md:hidden flex justify-between items-center bg-bsu-red text-white dark:bg-gray-800 px-4 py-3">
    <button @click="sidebarOpen = true">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
    <span class="font-semibold text-lg">Central Dashboard</span>
  </header>

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
            <div class="text-xs italic text-bsu-red hidden md:block">The National Engineering University</div>
        </div>
    </div>

    <!-- User Profile Icon & Dark Mode -->
    <div class="flex items-center gap-2">
        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode"
                class="p-2 rounded-full text-gray-300 hover:text-white hover:bg-white/10 transition-colors focus:outline-none"
                :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <!-- Sun Icon (for Dark Mode) -->
            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <!-- Moon Icon (for Light Mode) -->
            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        <div class="relative">
        <button @click="rightSidebarOpen = true" class="flex items-center focus:outline-none">
            <img src="{{ $user && $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) . '?' . now()->timestamp : asset('images/default-avatar.png') }}"
                 alt="Profile"
                 class="h-10 w-10 rounded-full border-2 border-white object-cover hover:border-bsu-red transition-colors">
        </button>
    </div>
    </div>
  </header>

  <!-- Right Sidebar -->
  <aside class="fixed inset-y-0 right-0 w-64 bg-white dark:bg-gray-800 shadow-xl transform transition-transform duration-300 z-50 flex flex-col"
         :class="rightSidebarOpen ? 'translate-x-0' : 'translate-x-full'"
         @keydown.escape.window="rightSidebarOpen = false"
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
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user?->name ?? 'Central User' }}</h3>
              <p class="text-sm text-bsu-red font-medium">Central Admin Staff</p>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $user?->email ?? 'email@example.com' }}</p>
          </div>

          <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
              <button @click="switchTab('account_settings')" class="w-full flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
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

    <!-- Toasts -->
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
    @include('central.partials.tabs.scholarships')
    @include('central.partials.tabs.scholars', ['scholars' => $scholars])
    @include('central.partials.tabs.endorsed-applicants', ['endorsedApplicants' => $endorsedApplicants])
    @include('central.partials.tabs.rejected-applicants', ['rejectedApplicants' => $rejectedApplicants])
    @include('central.partials.tabs.reports')
    @include('central.partials.tabs.statistics')
    @include('central.partials.tabs.staff')
    @include('central.partials.tabs.settings')

  </main>


  <script>
    window.addEventListener("pageshow", function (event) {
      if (event.persisted) {
        window.location.reload();
      }
    });
  </script>

</body>
</html>
