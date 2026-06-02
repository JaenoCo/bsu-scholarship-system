@extends('auth.layout')

@section('title', 'Central Admin Login')
@section('heading', 'Central Admin Access')
@section('subheading', 'Sign in to your central admin account')

@push('styles')
<style>
    body {
        background-image: url("{{ asset('images/central_login.jpg') }}") !important;
        background-size: cover !important;
        background-position: center center !important;
        background-repeat: no-repeat !important;
        background-attachment: fixed !important;
    }
    /* Add a semi-transparent overlay to the body to ensure content readability if needed, 
       but here we are targeting the body. If the card has a background, it handles the text contrast. */
</style>
@endpush

@section('content')
<form method="POST" action="{{ url('/central/login') }}" id="centralLoginForm" autocomplete="off" class="space-y-4">
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

  {{-- Remember Me & Forgot Password --}}
  <div class="flex items-center justify-between mb-6">
    <label class="inline-flex items-center text-sm text-gray-700 dark:text-gray-300">
      <input type="checkbox" name="remember" class="rounded text-red-600 focus:ring-red-500 dark:bg-gray-700">
      <span class="ml-2">Remember me</span>
    </label>
    <a href="{{ route('password.request') }}" class="text-sm text-red-600 hover:underline dark:text-red-400">Forgot password?</a>
  </div>

  {{-- Submit Button --}}
  <button type="submit" id="centralLoginButton"
          class="w-full bg-red-600 hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm">
    <span id="centralLoginText">Sign In</span>
    <span id="centralLoginSpinner" class="hidden">
      <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      Signing in...
    </span>
  </button>
</form>
@endsection

@push('scripts')
<script>
  // Handle form submission with loading state
  document.getElementById('centralLoginForm').addEventListener('submit', function() {
    const button = document.getElementById('centralLoginButton');
    const text = document.getElementById('centralLoginText');
    const spinner = document.getElementById('centralLoginSpinner');
    
    button.disabled = true;
    text.classList.add('hidden');
    spinner.classList.remove('hidden');
  });

  // Clear form fields on back navigation
  window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
      document.getElementById("centralLoginForm").reset();
      window.location.reload();
    } else if (performance.getEntriesByType("navigation")[0].type === "back_forward") {
      document.getElementById("centralLoginForm").reset();
    }
  });
</script>
@endpush

