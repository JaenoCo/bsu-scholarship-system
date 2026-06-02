@props(['user', 'title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="en" 
    :class="{ 'dark': darkMode }" 
    x-data="{ 
        sidebarOpen: localStorage.getItem('sidebarOpen') === 'true', 
        darkMode: localStorage.getItem('darkMode_{{ $user?->id ?? 'guest' }}') === 'true',
        isDesktop: window.innerWidth >= 768,
        showLogoutModal: false
    }"
    x-init="
        $watch('darkMode', val => localStorage.setItem('darkMode_{{ $user?->id ?? 'guest' }}', val));
        $watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val));
    "
    @resize.window="isDesktop = window.innerWidth >= 768"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/lugo.png') }}">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/sfao-style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <!-- Styles Stack -->
    @stack('styles')

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/sfao-script.js') }}?v={{ time() }}_revert"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    
    <script>
        // Immediately apply dark mode preference to prevent FOUC
        if (localStorage.getItem('darkMode_{{ $user?->id ?? 'guest' }}') === 'true' || (!('darkMode_{{ $user?->id ?? 'guest' }}' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
        /* Custom scrollbar styling - minimized and subtle */
        nav::-webkit-scrollbar { width: 6px; }
        nav::-webkit-scrollbar-track { background: transparent; }
        nav::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 3px; }
        nav::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.5); }
        .dark nav::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.2); }
        .dark nav::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.4); }
    </style>
    
    <!-- Scripts Stack -->
    @stack('scripts')

    <!-- Session Inactivity Monitor -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration
            const sessionLifetime = {{ config('session.lifetime') }} * 60 * 1000; // Minutes to Milliseconds
            const warningTime = 60 * 1000; // Warn 1 minute before logic? (Not strictly requested but good) -> Actually user just wants redirect.
            const checkInterval = 10000; // Check every 10 seconds
            const pingInterval = 15 * 60 * 1000; // Ping server every 15 minutes if active
            
            let lastActivity = Date.now();
            let lastPing = Date.now();
            
            // Events to track activity
            const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
            
            const updateActivity = () => {
                lastActivity = Date.now();
                
                // If active and enough time passed, ping server to keep alive
                if (Date.now() - lastPing > pingInterval) {
                   pingServer();
                }
            };
            
            const pingServer = () => {
                lastPing = Date.now();
                // Simple request to keep session alive
                fetch('/ping', { method: 'GET' })
                    .catch(err => console.log('Keep-alive ping failed (offline?)'));
            };
            
            // Attach listeners
            events.forEach(event => {
                window.addEventListener(event, updateActivity, { passive: true });
            });
            
            // Monitor Loop
            setInterval(() => {
                const now = Date.now();
                const timeSinceLastActivity = now - lastActivity;
                
                // Check if idle time exceeds session lifetime
                if (timeSinceLastActivity >= sessionLifetime) {
                    // Force Redirect
                    window.location.href = "{{ route('login') }}?expired=1";
                }
            }, checkInterval);
        });
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 dark:text-white min-h-screen font-sans transition-colors duration-300">

    <!-- Global Sidebar Wrapper -->
    <x-layout.sidebar-wrapper :user="$user">
        <!-- Sidebar Navigation Content -->
        @yield('sidebar-menu')
    </x-layout.sidebar-wrapper>

    <!-- Global Navbar -->
    <!-- Note: We use the layout navbar but allow yielding customized settings if needed, 
         or just use standard. The page usually defines the navbar title. 
         Actually, the navbar is usually INSIDE the main content area in your design (floating or top),
         BUT in the Focused layout it was at the top. 
         In Dashboard views (SFAO/Student), the navbar is part of the "Main Content" column 
         to be pushed by the sidebar. -->
         
    <!-- Global Navbar -->
    @yield('navbar')
         
    <!-- Main Content Wrapper -->
    <main class="min-h-screen transition-all duration-300"
          :class="{ 'md:ml-64': sidebarOpen }">
          
        <!-- Scrollable Content Area -->
        <div class="p-4 md:p-8">
            @yield('content')
        </div>
    </main>
    
    <!-- Global Modals -->
    <x-modals.logout />
    
    <!-- Page Specific Modals -->
    @yield('modals')

</body>
</html>
