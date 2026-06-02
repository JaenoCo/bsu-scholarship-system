@php
  use Illuminate\Support\Facades\Session;
  use Illuminate\Support\Facades\Redirect;
  use App\Models\User;

  // Redirect to login if session has ended or role mismatch
  if (!Session::has('user_id') || session('role') !== 'sfao') {
    return redirect()->route('login');
  }

  $user = User::find(session('user_id'));

  if (!$user) {
    Session::flush();
    return redirect()->route('login');
  }

  // Calculate default stats campus
  $allCampuses = $sfaoCampus->getAllCampusesUnder();
  $defaultStatsCampus = $allCampuses->count() > 1 ? 'all' : $allCampuses->first()->id;
@endphp

<!DOCTYPE html>
<html lang="en"
  :class="{ 'dark': darkMode }"
  x-data="{
    sidebarOpen: false,
    rightSidebarOpen: false,
    isDesktop: window.innerWidth >= 768,
    tab: localStorage.getItem('sfaoTab') || '{{ $activeTab ?? "scholarships" }}',
    statsCampus: localStorage.getItem('sfaoStatsCampus') || '{{ $defaultStatsCampus }}',
    darkMode: localStorage.getItem('darkMode_{{ $user->id }}') === 'true',
    showLogoutModal: false
  }"
  @resize.window="isDesktop = window.innerWidth >= 768"
  x-init="
    $watch('darkMode', val => localStorage.setItem('darkMode_{{ $user->id }}', val));
    $watch('tab', val => localStorage.setItem('sfaoTab', val));
    $watch('statsCampus', val => localStorage.setItem('sfaoStatsCampus', val));
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
  <title>SFAO Dashboard</title>
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

    <!-- Navigation - Scrollable -->
    <nav class="mt-6 px-4 pb-4 overflow-y-auto flex-1 space-y-4 custom-scrollbar">
      <!-- Scholarships Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Scholarships
        </div>
        <button @click="tab = 'scholarships'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'scholarships' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M12 14l9-5-9-5-9 5 9 5z" />
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
          </svg>
          All Scholarships
        </button>
        <button @click="tab = 'scholarships-private'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'scholarships-private' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
          </svg>
          Private Scholarships
        </button>
        <button @click="tab = 'scholarships-government'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'scholarships-government' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
          </svg>
          Government Scholarships
        </button>
      </div>

      <!-- Applicants Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Applicants
        </div>
        <button @click="tab = 'applicants'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'applicants' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          All Applicants
        </button>
        <button @click="tab = 'applicants-not_applied'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'applicants-not_applied' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
          </svg>
          Not Applied
        </button>
        <button @click="tab = 'applicants-in_progress'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'applicants-in_progress' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
          </svg>
          In Progress
        </button>
        <button @click="tab = 'applicants-pending'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'applicants-pending' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Pending
        </button>
        <button @click="tab = 'applicants-approved'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'applicants-approved' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Approved
        </button>
        <button @click="tab = 'applicants-rejected'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'applicants-rejected' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Rejected
        </button>
      </div>

      <!-- Scholars Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Scholars
        </div>
        <button @click="tab = 'scholars'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'scholars' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
          </svg>
          All Scholars
        </button>
        <button @click="tab = 'scholars-new'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'scholars-new' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
          </svg>
          New Scholars
        </button>
        <button @click="tab = 'scholars-old'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'scholars-old' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
          </svg>
          Old Scholars
        </button>
      </div>

      <!-- Reports Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Reports
        </div>
        <button @click="tab = 'reports-student_summary'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'reports-student_summary' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Student Summary Report
        </button>
        <button @click="tab = 'reports-scholar_summary'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'reports-scholar_summary' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          Scholar Summary Report
        </button>
        <button @click="tab = 'reports-grant_summary'"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'reports-grant_summary' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Grant Summary Report
        </button>

      </div>

      <!-- Statistics Header -->
      <div class="space-y-1">
        <div class="px-4 py-2 text-sm font-semibold text-gray-200 uppercase tracking-wider">
          Statistics
        </div>
        @if($sfaoCampus->getAllCampusesUnder()->count() > 1)
        <button @click="tab = 'statistics'; statsCampus = 'all'; $dispatch('set-stats-filter', 'all')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'statistics' && statsCampus === 'all' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          All Statistics
        </button>
        @endif
        @foreach($sfaoCampus->getAllCampusesUnder() as $campus)
        <button @click="tab = 'statistics'; statsCampus = '{{ $campus->id }}'; $dispatch('set-stats-filter', '{{ $campus->id }}')"
                class="w-full text-left px-4 py-2 rounded hover:bg-bsu-redDark dark:hover:bg-gray-700 transition text-sm flex items-center gap-2"
                :class="tab === 'statistics' && statsCampus == '{{ $campus->id }}' ? 'bg-white text-bsu-red dark:bg-gray-200' : 'text-white dark:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          {{ $campus->name }} Statistics
        </button>
        @endforeach
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
           onclick="
             localStorage.removeItem('sfaoTab');
             localStorage.removeItem('sfaoStatsCampus');
             localStorage.removeItem('sfaoApplicantsSortBy');
             localStorage.removeItem('sfaoApplicantsSortOrder');
             localStorage.removeItem('sfaoApplicantsCampus');
             localStorage.removeItem('sfaoApplicantsStatus');
             localStorage.removeItem('sfaoScholarsSortBy');
             localStorage.removeItem('sfaoScholarsSortOrder');
             localStorage.removeItem('sfaoScholarsCampus');
             localStorage.removeItem('sfaoScholarsStatus');
             localStorage.removeItem('sfaoScholarsType');
           "
           class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
          Logout
        </a>
      </div>
    </div>
  </div>
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
    <div class="flex items-center gap-2">
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
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user?->name ?? 'SFAO User' }}</h3>
              <p class="text-sm text-bsu-red font-medium">SFAO Staff</p>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $user?->email ?? 'email@example.com' }}</p>
          </div>

          <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
              <button @click="tab = 'account'" class="w-full flex items-center px-4 py-3 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Account Settings
              </button>
              <button @click="showLogoutModal = true; rightSidebarOpen = false" class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
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
    @include('sfao.partials.tabs.scholarships') <!-- Scholarship Lists -->
    @include('sfao.partials.tabs.applicants')   <!-- Applicants Lists -->
    @include('sfao.partials.tabs.scholars')     <!-- Scholars Lists -->
    @include('sfao.partials.tabs.reports')      <!-- Reports -->
    @include('sfao.partials.tabs.statistics')   <!-- Statistics -->
    @include('sfao.partials.tabs.account')      <!-- Account -->
  </main>


  <!-- Fix Safari Back Cache Bug -->
  <script>
    window.addEventListener("pageshow", function (event) {
      if (event.persisted) {
        window.location.reload();
      }
    });
    
  </script>
</body>
</html>
