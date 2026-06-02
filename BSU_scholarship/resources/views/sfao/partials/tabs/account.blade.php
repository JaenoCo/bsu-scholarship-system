<!-- Account Tab for SFAO -->
<div x-show="tab === 'account'" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak class="px-4 py-8">

  <div class="max-w-4xl mx-auto space-y-8"> <!-- Reduced max-width for vertical stack -->

    <!-- Profile Header Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">
        <!-- Banner -->
        <div class="h-32 bg-gradient-to-r from-bsu-red to-red-700 relative">
            <div class="absolute inset-0 bg-black opacity-10 pattern-dots"></div>
        </div>
        
        <!-- Profile Info Wrapper -->
        <div class="px-8 pb-8">
            <div class="relative flex flex-col sm:flex-row items-center sm:items-end -mt-12 mb-6 gap-6">
                <!-- Avatar -->
                <div class="relative group">
                    <div class="h-32 w-32 rounded-full p-1 bg-white dark:bg-gray-800 shadow-xl">
                        <img src="{{ $user && $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) . '?' . now()->timestamp : asset('images/default-avatar.png') }}" 
                                alt="Profile" 
                                class="h-full w-full rounded-full object-cover">
                    </div>
                    
                    <!-- Upload Button Overlay -->
                    <label for="profile_upload" class="absolute bottom-0 right-0 bg-gray-900 text-white p-2 rounded-full shadow-lg cursor-pointer hover:bg-bsu-red transition-all transform hover:scale-110 border-4 border-white dark:border-gray-800" title="Change Profile Picture">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </label>
                    <form id="profile_upload_form" method="POST" action="{{ url('/upload-profile-picture/sfao') }}" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="profile_upload" name="profile_picture" accept="image/*" onchange="document.getElementById('profile_upload_form').submit()">
                    </form>
                </div>

                <!-- Text Info -->
                <div class="text-center sm:text-left flex-1 pb-2">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $user->name ?? 'SFAO User' }}</h1>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 mt-2 text-sm text-gray-600 dark:text-gray-300">
                        <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-bsu-red" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                            {{ $user->email ?? 'email@example.com' }}
                        </span>
                        <span class="hidden sm:inline text-gray-300">|</span>
                        <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-bsu-red" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            SFAO Head
                        </span>
                    </div>
                </div>

                <!-- Logout Button (Top Right in Desktop) -->
                <div class="hidden sm:block pb-2">
                    <button @click="showLogoutModal = true" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium rounded-full transition shadow-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign Out
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Vertical Stack (Single Column) -->
    <div class="space-y-8">
        
        <!-- Office Info (Glassy Card Look) -->
        <div class="space-y-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2 px-1">
                <span class="p-1 rounded-lg bg-red-100 dark:bg-red-900/30 text-bsu-red">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
                Office Details
            </h3>
            
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                <!-- Decorative background blob -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-red-50 dark:bg-red-900/10 rounded-full blur-3xl opacity-50 group-hover:opacity-100 transition duration-500"></div>

                <div class="relative z-10 space-y-6">
                   <div class="bg-gray-50 dark:bg-gray-700/50 p-5 rounded-2xl">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Primary Campus</label>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $sfaoCampus->name ?? 'Not Assigned' }}</div>
                   </div>

                   @if($sfaoCampus->extensionCampuses->count() > 0)
                   <div class="bg-gray-50 dark:bg-gray-700/50 p-5 rounded-2xl">
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Extension Campuses</label>
                        <ul class="space-y-2">
                        @foreach($sfaoCampus->extensionCampuses as $ext)
                            <li class="flex items-center gap-2 text-gray-800 dark:text-gray-200">
                                <svg class="w-4 h-4 text-bsu-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                {{ $ext->name }}
                            </li>
                        @endforeach
                        </ul>
                   </div>
                   @endif
                </div>
            </div>
        </div>

        <!-- Security (Clean Form) -->
        <div class="space-y-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2 px-1">
                <span class="p-1 rounded-lg bg-red-100 dark:bg-red-900/30 text-bsu-red">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                Security
            </h3>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                <!-- Decorative background blob -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-red-50 dark:bg-red-900/10 rounded-full blur-3xl opacity-50 group-hover:opacity-100 transition duration-500"></div>

                <div class="relative z-10">
                    <form method="POST" action="{{ route('sfao.change-password') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                            <input type="password" name="current_password" required
                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 dark:text-white focus:ring-2 focus:ring-bsu-red focus:border-transparent transition outline-none"
                                   placeholder="••••••••">
                        </div>
                        <!-- Changed grid to 2-columns internal form for better use of horizontal space in vertical mode -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                                <input type="password" name="password" required
                                       class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 dark:text-white focus:ring-2 focus:ring-bsu-red focus:border-transparent transition outline-none"
                                       placeholder="••••••••">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm New</label>
                                <input type="password" name="password_confirmation" required
                                       class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 dark:text-white focus:ring-2 focus:ring-bsu-red focus:border-transparent transition outline-none"
                                       placeholder="••••••••">
                            </div>
                        </div>
                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                    class="w-full sm:w-auto bg-bsu-red hover:bg-bsu-redDark text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition duration-200 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Logout (Only visible on small screens) -->
    <div class="sm:hidden text-center pt-4">
        <button @click="showLogoutModal = true" 
                class="w-full inline-flex items-center justify-center gap-2 text-red-600 hover:text-red-800 font-semibold transition bg-red-50 dark:bg-red-900/10 py-3 rounded-xl border border-red-100 dark:border-red-900/20">
            Sign Out
        </button>
    </div>

  </div>
</div>
