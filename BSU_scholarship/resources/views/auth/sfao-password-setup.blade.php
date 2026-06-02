<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50"
    :class="{ 'dark': darkMode }"
    x-data="{ darkMode: localStorage.getItem('darkMode_{{ $user->id }}') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode_{{ $user->id }}', val))">
<head>
    <script>
        if (localStorage.getItem('darkMode_{{ $user->id }}') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Set Password | Spartan Scholarship</title>
  <link rel="icon" type="image/png" href="{{ asset('images/lugo.png') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 flex items-center justify-center min-h-screen">
  <div class="w-full max-w-md bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md">
    <div class="text-center mb-6">
      <img src="{{ asset('images/lugo.png') }}" alt="Logo" class="h-16 mx-auto mb-2">
            </div>
            <p class="mt-2 text-center text-sm text-gray-600">
                Complete your SFAO admin account setup
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <!-- User Details -->
                <div class="mb-6 p-4 bg-green-50 rounded-lg">
                    <h3 class="text-lg font-medium text-green-900 mb-2">Account Details</h3>
                    <div class="space-y-1 text-sm text-green-800">
                        <p><strong>Name:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Campus:</strong> {{ $user->campus->name }}</p>
                        <p><strong>Role:</strong> SFAO Administrator</p>
                    </div>
                </div>

                <!-- Success Messages -->
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Password Setup Form -->
                <form class="space-y-6" method="POST" action="{{ route('sfao.password.setup') }}">
                    @csrf
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" required
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                                          placeholder-gray-400 focus:outline-none focus:ring-bsu-red focus:border-bsu-red 
                                          sm:text-sm">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            Confirm Password
                        </label>
                        <div class="mt-1">
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md 
                                          placeholder-gray-400 focus:outline-none focus:ring-bsu-red focus:border-bsu-red 
                                          sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md 
                                       shadow-sm text-sm font-medium text-white bg-bsu-red hover:bg-bsu-redDark 
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red">
                            Complete Account Setup
                        </button>
                    </div>
                </form>

                <!-- Security Notice -->
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Security Notice
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Choose a strong password that you haven't used elsewhere. This password will be used to access your SFAO admin account.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500">
                        By completing your account setup, you agree to the terms and conditions of the BSU Scholarship System.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
