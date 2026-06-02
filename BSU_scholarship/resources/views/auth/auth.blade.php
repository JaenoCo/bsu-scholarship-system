@extends('auth.layout')

@section('title', 'Login')
@section('heading', 'Welcome Back')
@section('subheading', 'Sign in to your account to continue')

@section('content')
<form method="POST" action="{{ url('/login') }}" id="loginForm" autocomplete="off" class="space-y-4">
  @csrf

  {{-- Email --}}
  <x-auth.input 
    type="email"
    label="Gsuite"
    name="email"
    placeholder="example@g.batstate-u.edu.ph"
    pattern="^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$"
    autocomplete="off"
    ariaDescribedby="email-help"
    required
  >
    <p id="email-help" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
      Must be a valid BatState-U email address
    </p>
  </x-auth.input>

  {{-- Password --}}
  <x-auth.password-input 
    label="Password"
    name="password"
    autocomplete="new-password"
    required
  />

  {{-- Campus Selection --}}
  <div class="mb-4">
    <label for="campus_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      Select Campus <span class="text-red-500">*</span>
    </label>
    <select id="campus_id" name="campus_id" required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-colors duration-200">
      <option value="" disabled selected>-- Choose your campus --</option>
      @foreach(\App\Models\Campus::all() as $campus)
        <option value="{{ $campus->id }}">{{ $campus->name }}</option>
      @endforeach
    </select>
  </div>

  {{-- Remember Me & Forgot Password --}}
  <div class="flex items-center justify-between mb-6">
    <label class="inline-flex items-center text-sm text-gray-700 dark:text-gray-300">
      <input type="checkbox" name="remember" class="rounded text-red-600 focus:ring-red-500 dark:bg-gray-700">
      <span class="ml-2">Remember me</span>
    </label>
    <a href="{{ route('password.request') }}" class="text-sm text-red-600 hover:underline dark:text-red-400">Forgot password?</a>
  </div>

  {{-- Submit Button --}}
  <button type="submit" id="loginButton"
          class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm">
    <span id="loginText">Sign In</span>
    <span id="loginSpinner" class="hidden">
      <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Signing in...
    </span>
  </button>

  {{-- New Applicant Link --}}
  <div class="mt-4 text-center">
    <a href="{{ route('register') }}" 
       class="text-sm text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors duration-200">
      New Applicant? Create Account
    </a>
  </div>
</form>
@endsection

@section('footer')
<a href="{{ url('/') }}" class="flex items-center gap-1 text-red-600 hover:underline text-sm dark:text-red-400">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
  </svg>
  Back to Homepage
</a>
@endsection

@push('scripts')
<script>
  // Handle form submission with loading state
  document.getElementById('loginForm').addEventListener('submit', function() {
    const button = document.getElementById('loginButton');
    const text = document.getElementById('loginText');
    const spinner = document.getElementById('loginSpinner');
    
    button.disabled = true;
    text.classList.add('hidden');
    spinner.classList.remove('hidden');
  });

  // Clear form fields on back navigation
  window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
      document.getElementById("loginForm").reset();
      window.location.reload();
    } else if (performance.getEntriesByType("navigation")[0].type === "back_forward") {
      document.getElementById("loginForm").reset();
    }
  });
</script>
@endpush
