@extends('layouts.app')

@section('title', 'Waiting for Verification | Batangas State University')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
     x-data="verificationHandler()">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg border border-gray-200">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6 animate-pulse">
                <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-2">
                Verify your email
            </h2>
            <p class="text-sm text-gray-600 mb-8">
                We've sent a verification link to <span class="font-bold text-gray-800" x-text="email"></span>. 
                Please check your inbox and click the link to verify your account.
            </p>
            
            <div class="flex flex-col space-y-4">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded text-left" role="alert">
                    <p class="font-bold text-blue-700 text-sm">Waiting for verification...</p>
                    <p class="text-xs text-blue-600">This page will automatically close once you verify your email.</p>
                </div>

                <!-- Resend Button -->
                <div class="pt-4">
                    <button @click="resendEmail" 
                            :disabled="resendCount >= 3 || loading"
                            :class="{ 'opacity-50 cursor-not-allowed': resendCount >= 3 || loading }"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <span x-show="!loading">Resend Verification Email</span>
                        <span x-show="loading">Sending...</span>
                    </button>
                    <p class="mt-2 text-xs text-gray-500">
                        Resend attempts remaining: <span x-text="3 - resendCount"></span>
                    </p>
                    <p x-show="resendCount >= 3" class="text-xs text-red-500 mt-1 font-semibold">
                        Maximum resend attempts reached. Please contact support if you haven't received the email.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-center">
             <a href="{{ route('logout') }}" class="text-sm font-medium text-red-600 hover:text-red-500">
                Cancel and Logout
            </a>
        </div>
    </div>
</div>

<script>
    function verificationHandler() {
        return {
            email: '{{ session('email') }}',
            resendCount: {{ session('resend_count', 0) }},
            loading: false,
            checkInterval: null,

            init() {
                // Poll for verification status
                this.checkInterval = setInterval(() => {
                    this.checkStatus();
                }, 3000);
            },

            checkStatus() {
                fetch('{{ route('verification.check') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.verified) {
                            window.location.href = "{{ route('login') }}";
                        }
                    })
                    .catch(error => console.error('Error checking status:', error));
            },

            resendEmail() {
                if (this.resendCount >= 3) return;

                this.loading = true;
                
                fetch('{{ route('verification.resend.post') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: this.email })
                })
                .then(response => response.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.resendCount = data.resend_count;
                        alert('Verification email resent!');
                    } else {
                        alert(data.message || 'Failed to resend email.');
                        if (data.resend_count !== undefined) {
                            this.resendCount = data.resend_count;
                        }
                    }
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }
    }
</script>
@endsection
