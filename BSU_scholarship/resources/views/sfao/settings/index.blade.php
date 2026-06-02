<div class="w-full h-full space-y-6">

    <!-- Account Information Tab -->
    <div x-show="tab === 'account' || tab === 'account-info'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
    <div x-show="tab === 'account' || tab === 'account-info'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="flex flex-col gap-8">

         <!-- Top Row: Profile Widgets -->
         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
             <!-- Profile Picture Card -->
             <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center text-center">
                <!-- Avatar Wrapper -->
                <div class="relative inline-block mb-4 group">
                    <!-- Main Image -->
                    <div class="h-32 w-32 rounded-full p-1 bg-gradient-to-tr from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800">
                        <img src="{{ $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) : asset('images/default-avatar.png') }}" 
                             alt="Profile" 
                             class="h-full w-full rounded-full object-cover border-4 border-white dark:border-gray-800 shadow-sm">
                    </div>
                    
                    <!-- Camera Icon (Bottom Right) -->
                    <label for="profile_upload" class="absolute bottom-2 right-2 bg-gray-900 text-white p-2.5 rounded-full cursor-pointer hover:bg-bsu-red transition-all shadow-md border-4 border-white dark:border-gray-800 hover:scale-105" title="Change Photo">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </label>
                    
                    <!-- Hidden Form -->
                    <form action="{{ url('/upload-profile-picture/sfao') }}" method="POST" enctype="multipart/form-data" id="profile_form">
                        @csrf
                        <input type="file" id="profile_upload" name="profile_picture" class="hidden" onchange="document.getElementById('profile_form').submit()">
                    </form>
                </div>

                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $user->email }}</p>
                
                <div class="px-4 py-2 bg-red-50 dark:bg-red-900/20 text-bsu-red rounded-full text-xs font-bold uppercase tracking-wider">
                    SFAO Administrator
                </div>
             </div>

             <!-- Quick Status Card -->
             <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-center">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-6 border-b border-gray-100 dark:border-gray-700 pb-2">Account Status</h4>
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-sm text-gray-600 dark:text-gray-300">Joined Date</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-50 dark:bg-green-900/20 text-green-600 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-sm text-gray-600 dark:text-gray-300">Verification</span>
                        </div>
                        @if($user->email_verified_at)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Verified
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Pending
                            </span>
                        @endif
                    </div>
                </div>
             </div>
         </div>

         <!-- Bottom Row: Editable Form -->
         <div class="w-full">
             <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 h-full">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Profile Details</h2>
                </div>

                <form method="POST" action="{{ route('sfao.profile.update') }}" class="p-6 space-y-8">
                    @csrf
                    @method('PUT')
                    
                    @if(session('status') === 'profile-updated')
                        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-green-900/20 dark:text-green-400" role="alert">
                            <span class="font-medium">Success!</span> Profile information updated.
                        </div>
                    @endif

                    <!-- Personal Details Section -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Personal Information</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name Field -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Display Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700/50 focus:ring-2 focus:ring-bsu-red focus:border-bsu-red dark:text-white transition-all shadow-sm"
                                       placeholder="Enter your full name">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Email Field -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                        </svg>
                                    </span>
                                    <input type="email" value="{{ $user->email }}" readonly
                                           class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-500 cursor-not-allowed">
                                </div>
                                <p class="mt-1 text-xs text-gray-400">Linked to your institutional invite.</p>
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Contact Number
                                    @if(empty($user->contact_number))
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-yellow-100 text-yellow-800 animate-pulse">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            Important
                                        </span>
                                    @endif
                                </label>
                                <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" 
                                       class="w-full px-4 py-2.5 rounded-lg border {{ empty($user->contact_number) ? 'border-yellow-400 ring-1 ring-yellow-400' : 'border-gray-300' }} dark:border-gray-600 dark:bg-gray-700/50 focus:ring-2 focus:ring-bsu-red focus:border-bsu-red dark:text-white transition-all shadow-sm"
                                       placeholder="+63 9XX XXX XXXX">
                                @if(empty($user->contact_number))
                                    <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-500 font-medium">Please add a contact number for urgent communications.</p>
                                @endif
                                @error('contact_number')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-6"></div>

                    <!-- Office Details Section -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="p-2 bg-red-50 dark:bg-red-900/20 text-bsu-red rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </span>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Office Assignment</h3>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Assigned Campus</label>
                                    <div class="text-gray-900 dark:text-white font-semibold">{{ $sfaoCampus->name ?? 'Not Assigned' }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Department</label>
                                    <div class="text-gray-900 dark:text-white font-semibold">Scholarship & Financial Assistance Office</div>
                                </div>
                             </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-bsu-red hover:bg-red-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5"
                                title="Save Profile Changes">
                            Save Changes
                        </button>
                    </div>
                </form>
             </div>
         </div>
    </div>

    <!-- Security Settings Card -->
    <div x-show="tab === 'account-security'" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6 border-b border-gray-200 dark:border-gray-700 pb-2">
            Security Settings
        </h2>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('sfao.change-password') }}" method="POST" class="max-w-xl">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                    <input type="password" name="new_password" id="new_password" required 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                    <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long.</p>
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" required 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition font-medium shadow-md">
                        Update Password
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
