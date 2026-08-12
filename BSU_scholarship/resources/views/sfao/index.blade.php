@php
  use Illuminate\Support\Facades\Session;
  use Illuminate\Support\Str;
  use App\Models\User;

  // Redirect to login if session has ended or role mismatch (Handled by middleware usually, but keeping safe)
  if (!Session::has('user_id') || session('role') !== 'sfao') {
    return redirect()->route('login');
  }

  $user = User::find(session('user_id'));

  if (!$user) {
    Session::flush();
    return redirect()->route('login');
  }

  // Calculate default stats campus
  $allCampuses = $sfaoCampus->getAllCampusesUnder();
  $defaultStatsCampus = $allCampuses->count() > 1 ? 'all' : $allCampuses->first()->id;
@endphp

@extends('layouts.dashboard', ['user' => $user, 'title' => 'SFAO Analytics & Dashboard'])

{{-- 
    SFAO Specific State Management 
    We define a separate x-data for the internal content to handle tabs and dropdowns.
    Note: The layout handles sidebarOpen/rightSidebarOpen/darkMode. 
    This inner scope handles tabs and dropdowns.
--}}
@section('content')
    <div x-data='sfaoDashboardState({ 
        defaultStatsCampus: @json($defaultStatsCampus), 
        userId: @json($user->id), 
        userRole: @json(session("role")),
        campusList: @json($allCampuses->map(fn($c) => ["id" => $c->id, "name" => $c->name, "slug" => Str::slug($c->name)])),
        activeTab: @json($activeTab ?? "analytics") 
    })'>

        <!-- Toasts -->
        @include('sfao.components.modals.toast')

        <!-- Tabs -->
        <div>
           @include('sfao.scholarships.index') <!-- Scholarship Lists -->
           @include('sfao.applicants.index')   <!-- Applicants Lists -->
           @include('sfao.scholars.index')     <!-- Scholars Lists -->
           @include('sfao.reports.index')      <!-- Reports -->
           @include('sfao.analytics.index')    <!-- Analytics -->
           @include('sfao.application-forms.index')  <!-- Application Forms -->
           @include('sfao.import.scholarships') <!-- Scholarship Import -->
           
           <div>
                @include('sfao.settings.index')
           </div>
        </div>

    </div>

    <!-- Load SFAO Dashboard Script -->
    <script src="{{ asset('js/sfao-script.js') }}?v={{ time() }}"></script>
@endsection

@section('navbar')
  <!-- Global Navbar -->
  <x-layout.navbar 
      title="SFAO Dashboard" 
      :user="$user" 
      :settings="false" 
      :logout="true" 
      action-text="Import"
      action-click="$dispatch('switch-tab', 'import-scholarships')"
      action-title="Import scholarships from CSV or Excel"
  />
@endsection

@section('sidebar-menu')
    <!-- Navigation - Scrollable -->
    @include('sfao.components.sidebar-menu', ['user' => $user, 'sfaoCampus' => $sfaoCampus])
@endsection
