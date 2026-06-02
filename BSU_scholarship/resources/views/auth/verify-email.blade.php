@php
    $user = \App\Models\User::find(session('user_id'));
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth"
    :class="{ 'dark': darkMode }"
    x-data="{ darkMode: localStorage.getItem('darkMode_{{ $user ? $user->id : '' }}') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode_{{ $user ? $user->id : '' }}', val))">
<head>
    <script>
        if (localStorage.getItem('darkMode_{{ $user ? $user->id : '' }}') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Email | Spartan Scholarship</title>
  <link rel="icon" type="image/png" href="{{ asset('images/lugo.png') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script src="https://unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gradient-to-br from-red-50 to-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-8 rounded-xl shadow-xl w-full max-w-md border border-gray-200">
    <div class="flex flex-col items-center mb-6">
      <div class="bg-red-100 p-3 rounded-full mb-4">
        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Verify Your Email</h1>
      <p class="text-gray-600 text-center text-sm">
        We've sent a verification link to your email address. Please check your inbox and click the link to verify your account.
      </p>
    </div>

    @if(session('verified'))
      <div class="text-green-600 mb-4 bg-green-100 border border-green-400 p-3 rounded-lg text-sm" role="alert">
        <div class="flex items-center">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
          Email verified successfully! You can now log in.
        </div>
      </div>
    @endif

    @if(session('message'))
      <div class="text-blue-600 mb-4 bg-blue-100 border border-blue-400 p-3 rounded-lg text-sm" role="alert">
        <div class="flex items-center">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
          </svg>
          {{ session('message') }}
        </div>
      </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
      @csrf
      
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input type="email" id="email" name="email" required
               placeholder="Enter your email address"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
      </div>

      <button type="submit" 
              class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg font-semibold transition-colors duration-200">
        Resend Verification Email
      </button>
    </form>

    <div class="mt-6 text-center space-y-2">
      <a href="{{ route('login') }}" class="text-red-600 hover:underline text-sm block">
        <span class="flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Login
        </span>
      </a>
      <p class="text-xs text-gray-500">
        Didn't receive the email? Check your spam folder or contact support.
      </p>
    </div>
  </div>
</body>
</html>
