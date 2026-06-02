@extends('auth.layout')

@section('title', 'Reset Password')
@section('heading', 'Set New Password')
@section('subheading', 'Enter your new password')

@section('content')
<form method="POST" action="{{ route('password.update') }}" class="space-y-4">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    {{-- Email --}}
    <x-auth.input 
        type="email"
        label="Email Address"
        name="email"
        :value="$email ?? old('email')"
        required
        autofocus
        readonly
    />

    {{-- Password --}}
    <x-auth.password-input 
        label="New Password"
        name="password"
        required
        autocomplete="new-password"
    />

    {{-- Confirm Password --}}
    <x-auth.password-input 
        label="Confirm Password"
        name="password_confirmation"
        required
        autocomplete="new-password"
    />

    {{-- Submit Button --}}
    <button type="submit"
            class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm">
        Reset Password
    </button>
</form>
@endsection
