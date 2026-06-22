@php
  use Illuminate\Support\Facades\Session;
  use App\Models\User;

  if (!Session::has('user_id')) {
    Redirect::to('login')->send();
  }

  $user = User::with('campus')->find(session('user_id'));
  
  // Ensure we have $user before passing to view
  if (!$user) {
    Session::flush();
    Redirect::to('login')->send();
  }

  $centralCampusList = collect($campuses ?? [])->map(function ($campus) {
    return [
      'id' => $campus->id,
      'name' => $campus->name,
      'slug' => strtolower(str_replace(' ', '_', $campus->name)),
    ];
  })->values();
@endphp

@extends('layouts.dashboard', ['user' => $user, 'title' => 'Central Dashboard'])

@section('styles')
  <style>
    /* Custom scrollbar styling - minimized and subtle */
    [x-cloak] { display: none !important; }
  </style>
@endsection

@section('sidebar-menu')
    @include('central.components.sidebar-menu', ['user' => $user, 'campuses' => $campuses])
@endsection

@section('navbar')
  <x-layout.navbar 
      title="Central Dashboard" 
      :user="$user" 
      :settings="true"
      settings-click="$dispatch('switch-tab', 'settings')"
      :logout="true"
  />
@endsection

@section('content')
<div x-data="centralDashboard({
    initialTab: @js(request()->query('tab')),
    campusList: @js($centralCampusList)
})">

  <!-- Right Sidebar Component -->


  <!-- Toast Notifications Component -->
  @include('central.components.toast-notification')

  <!-- Tabs -->
  @include('central.partials.tabs.scholarships')
  @include('central.partials.tabs.scholars', ['scholars' => $scholars])
  @include('central.partials.tabs.endorsed-applicants', ['endorsedApplicants' => $endorsedApplicants])
  @include('central.partials.tabs.rejected-applicants', ['rejectedApplicants' => $rejectedApplicants])
  @include('central.partials.tabs.reports')
  @include('central.partials.tabs.statistics')
  @include('central.partials.tabs.staff')
  
  <div x-show="tab === 'account_settings'" x-cloak x-transition>
      @include('central.settings.index')
  </div>

  <!-- Logout Modal -->
  <div x-show="showLogoutModal" 
       x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
       @click.self="showLogoutModal = false">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Confirm Logout</h3>
      <p class="text-gray-600 dark:text-gray-300 mb-6">Are you sure you want to logout?</p>
      <div class="flex justify-end gap-3">
        <button @click="showLogoutModal = false"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
          Cancel
        </button>
        <a href="{{ url('/logout') }}"
           onclick="localStorage.removeItem('activeTab'); localStorage.removeItem('groupState_scholarships'); localStorage.removeItem('groupState_reports'); localStorage.removeItem('groupState_statistics'); localStorage.removeItem('groupState_scholars'); localStorage.removeItem('groupState_applications');"
           class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
          Logout
        </a>
      </div>
    </div>
  </div>

</div>

<!-- Load Central Dashboard Script -->
<script src="{{ asset('js/central-script.js') }}"></script>
@endsection
