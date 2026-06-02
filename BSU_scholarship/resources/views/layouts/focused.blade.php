@php
  use Illuminate\Support\Facades\Session;
  use App\Models\User;

  // Redirect to login if session has ended or role mismatch
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
<html lang="en" class="h-full" 
    :class="{ 'dark': darkMode }" 
    x-data="{ 
        darkMode: localStorage.getItem('darkMode_{{ $user->id }}') === 'true',
        userMenuOpen: false,
        sidebarOpen: false, 
        rightSidebarOpen: false,
        showLogoutModal: false,
        showRedirectModal: false,
        isDesktop: window.innerWidth >= 768
    }" 
    @resize.window="isDesktop = window.innerWidth >= 768" 
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode_{{ $user->id }}', val))">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page-title', 'BSU Scholarship System')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/lugo.png') }}">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Immediately apply dark mode preference to prevent FOUC
        if (localStorage.getItem('darkMode_{{ $user->id }}') === 'true' || (!('darkMode_{{ $user->id }}' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 flex flex-col h-full min-h-full transition-colors duration-300">

    <!-- Global Standard Navbar -->
    <div class="print:hidden">
        <x-layout.navbar 
            :user="$user" 
            :title="View::hasSection('navbar-title') ? View::getSection('navbar-title') : 'Scholarship Management System'"
            :sidebar="false"
            :back-url="View::hasSection('back-url') ? View::getSection('back-url') : null"
            :back-text="View::hasSection('back-text') ? View::getSection('back-text') : null"
            :logout="true"
            :profile="false"
            :settings="true"
            settings-click="showRedirectModal = true"
        />
    </div>

    <!-- Main Content -->
    <main class="flex-1 w-full @yield('content-width', 'max-w-7xl') mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-fade-in-up">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto print:hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">&copy; {{ date('Y') }} Batangas State University - The National Engineering University</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Scholarship & Financial Assistance Office</p>
            </div>
        </div>
    </footer>

    <!-- Logout Modal (Standard Global Modal) -->
    <x-modals.logout />
    
    <!-- Redirect Warning Modal -->
    <x-modals.redirect-warning url="{{ route('sfao.dashboard') }}" />

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
    </style>
</body>
</html>
