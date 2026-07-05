@props([])
<div x-data="{
    sidebarOpen: false,
    openSection: null,
    query: ''
}"
     class="flex">

    <!-- Mobile: hamburger -->
    <button @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar"
            class="p-2 m-2 rounded-md text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 shadow md:hidden">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'block' : 'hidden'" class="w-72 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 h-screen p-4 md:block" aria-label="Sidebar">
        <div class="flex flex-col h-full">
            <!-- Search -->
            <div class="mb-4">
                <label for="sidebar-search" class="sr-only">Search</label>
                <div class="relative text-gray-400 focus-within:text-gray-600">
                    <svg class="w-5 h-5 absolute left-3 top-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <input id="sidebar-search" x-model="query" type="search" placeholder="Search links, scholarships..."
                           class="w-full pl-10 pr-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-bsu-red" />
                </div>
            </div>

            <!-- Top links -->
            <nav class="flex-1" aria-label="Main Navigation">
                <ul class="space-y-1">
                    <li>
                        <a href="/dashboard" class="flex items-center px-3 py-2 text-sm font-semibold rounded-md focus:outline-none {{ Request::is('dashboard') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}" aria-current="page">
                            <svg class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9v8a2 2 0 01-2 2h-4a2 2 0 01-2-2v-6H9v6a2 2 0 01-2 2H3a2 2 0 01-2-2v-8z"/></svg>
                            Dashboard / Home
                        </a>
                    </li>

                    <li>
                        <a href="/scholarships" class="flex items-center px-3 py-2 text-sm font-semibold rounded-md focus:outline-none {{ Request::is('scholarships*') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}">
                            <svg class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2v4.5A2.5 2.5 0 0011.5 17H14c1.657 0 3-.895 3-2V10c0-1.105-1.343-2-3-2h-2z"/></svg>
                            Available Scholarships
                        </a>
                    </li>

                    <li class="mt-3 border-t border-gray-100 dark:border-gray-800 pt-3">
                        <button @click="openSection = openSection === 'applications' ? null : 'applications'" class="w-full flex items-center justify-between px-3 py-2 text-sm font-semibold rounded-md {{ Request::is('applications*') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}" :aria-expanded="openSection === 'applications'">
                            <span class="flex items-center">
                                <svg class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                My Scholarships / Applications
                            </span>
                            <svg :class="openSection === 'applications' ? 'transform rotate-90' : ''" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 6L14 10L6 14V6z" clip-rule="evenodd"/></svg>
                        </button>

                        <ul x-show="openSection === 'applications'" x-collapse class="mt-2 pl-8 space-y-1">
                            <li>
                                <a href="/applications" class="flex items-center px-3 py-2 text-sm rounded-md {{ Request::is('applications') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}">
                                    View My Applications
                                </a>
                            </li>
                            <li>
                                <a href="/applications/new" class="flex items-center px-3 py-2 text-sm rounded-md {{ Request::is('applications/new') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}">
                                    New Scholarship Application
                                </a>
                            </li>
                            <li>
                                <a href="/forms/additional" class="flex items-center px-3 py-2 text-sm rounded-md {{ Request::is('forms/additional') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}">
                                    Additional Application Forms
                                </a>
                            </li>
                            <li>
                                <a href="/grades/submit" class="flex items-center px-3 py-2 text-sm rounded-md {{ Request::is('grades*') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}">
                                    Submit Grades / Academic Records
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="mt-4">
                        <a href="/profile" class="flex items-center px-3 py-2 text-sm font-semibold rounded-md {{ Request::is('profile*') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}">
                            <svg class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zM6 20v-1a4 4 0 014-4h4a4 4 0 014 4v1"/></svg>
                            Profile Settings
                        </a>
                    </li>

                    <li class="mt-3 border-t border-gray-100 dark:border-gray-800 pt-3">
                        <button @click="openSection = openSection === 'notifications' ? null : 'notifications'" class="w-full flex items-center justify-between px-3 py-2 text-sm font-semibold rounded-md text-gray-700 hover:bg-gray-100 dark:text-gray-200" :aria-expanded="openSection === 'notifications'">
                            <span class="flex items-center">
                                <svg class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                Notifications & Updates
                            </span>
                            <svg :class="openSection === 'notifications' ? 'transform rotate-90' : ''" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 6L14 10L6 14V6z" clip-rule="evenodd"/></svg>
                        </button>

                        <ul x-show="openSection === 'notifications'" x-collapse class="mt-2 pl-8 space-y-1">
                            <li>
                                <a href="/notifications" class="flex items-center px-3 py-2 text-sm rounded-md {{ Request::is('notifications*') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}">
                                    All Notifications
                                </a>
                            </li>
                            <li>
                                <a href="/updates" class="flex items-center px-3 py-2 text-sm rounded-md {{ Request::is('updates*') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}">
                                    Announcements & Updates
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="mt-4 border-t border-gray-100 dark:border-gray-800 pt-3">
                        <a href="/help" class="flex items-center px-3 py-2 text-sm font-semibold rounded-md {{ Request::is('help*') ? 'bg-bsu-red text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200' }}">
                            <svg class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 18h.01M15 10a3 3 0 00-6 0c0 1.657 1.343 3 3 3s3 1.343 3 3"/></svg>
                            Help / FAQ
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Help & Support block at bottom -->
            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Need help?</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Contact our support or view the FAQ</p>
                    </div>
                    <a href="/support" class="ml-3 inline-flex items-center px-3 py-1.5 bg-bsu-red text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none">Get Support</a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main content placeholder to keep layout intact -->
    <div class="flex-1"></div>
</div>
