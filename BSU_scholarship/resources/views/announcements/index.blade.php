@extends('layouts.app')

@section('title', 'Scholarship Announcements | Batangas State University')

@section('content')
    @include('partials.landing-navbar')

    <main class="min-h-screen bg-gray-50 px-4 pb-12 pt-24 dark:bg-gray-900 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            @include('student.announcements.index', ['announcements' => $announcements])
        </div>
    </main>
@endsection
