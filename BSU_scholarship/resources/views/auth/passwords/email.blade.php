@extends('auth.layout')

@section('title', 'Reset Password')
@section('heading', 'Reset Password')
@section('subheading', 'Enter your email to receive a reset link')

@section('content')
@if (session('status'))
    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf

    {{-- Email --}}
    <x-auth.input 
        type="email"
        label="Email Address"
        name="email"
        :value="old('email')"
        required
        autofocus
        placeholder="example@g.batstate-u.edu.ph"
    />

    {{-- Submit Button --}}
    <button type="submit"
            class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm">
        Send Password Reset Link
    </button>

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" 
           class="text-sm text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors duration-200">
            Back to Login
        </a>
    </div>
</form>
@endsection
