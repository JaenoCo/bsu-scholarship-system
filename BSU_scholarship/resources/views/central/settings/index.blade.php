<div class="max-w-4xl mx-auto space-y-6">

    <!-- Profile Overview & Edit Card -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6 border-b border-gray-200 dark:border-gray-700 pb-2">
            Profile Information
        </h2>

        <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- Left Side: Profile Picture -->
            <div class="w-full md:w-1/3 flex flex-col items-center space-y-4">
                <div class="relative group">
                    <img src="{{ $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) : asset('images/default-avatar.png') }}" 
                         alt="Profile" 
                         class="h-32 w-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700 shadow-md">
                    
                    <label for="profile_upload" class="absolute bottom-0 right-0 bg-red-600 text-white p-2 rounded-full cursor-pointer hover:bg-red-700 transition shadow-lg" title="Change Profile Picture">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </label>
                </div>
                
                <form action="{{ url('/upload-profile-picture/central') }}" method="POST" enctype="multipart/form-data" id="profile_form">
                    @csrf
                    <input type="file" id="profile_upload" name="profile_picture" class="hidden" onchange="document.getElementById('profile_form').submit()">
                </form>

                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Allowed: JPG, PNG, JPEG</p>
                    <p class="text-xs text-gray-400">Max size: 2MB</p>
                </div>
            </div>

            <!-- Right Side: User Details Form -->
            <div class="w-full md:w-2/3 space-y-4">
                @if(session('name_success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                        {{ session('name_success') }}
                    </div>
                @endif

                <form action="{{ route('central.update-name') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Full Name</label>
                            <div class="flex gap-2">
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                       class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                                <button type="submit" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                    Save
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Role</label>
                            <div class="text-lg font-medium text-gray-800 dark:text-white bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-600">
                                Central Admin
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Email Address</label>
                            <div class="flex items-center space-x-2 bg-gray-50 dark:bg-gray-700 px-4 py-2 rounded-md border border-gray-200 dark:border-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-gray-800 dark:text-white">{{ $user->email }}</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Settings Card -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
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

        <form action="{{ route('central.change-password') }}" method="POST" class="max-w-xl">
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
