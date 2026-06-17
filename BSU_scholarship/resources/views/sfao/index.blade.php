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
           
           <!-- Account Settings (Conditional rendering managed by x-show in parent or inner logic?) 
                Wait, SFAO dashboard uses `activeTab` variable in x-data.
                The includes are just dumped in the div. 
                Wait, checking sfao/index.blade.php (Step 103), the includes are just stacked in a div.
                Are they NOT wrapped in x-show there? 
                Let's check `sfao/scholarships/index.blade.php` etc. They likely have x-show inside them.
                If so, I should wrap settings in x-show here or inside settings partial?
                The settings partial I made has NO x-show. 
                So I MUST wrap it here.
           -->
           <div x-show="tab === 'account' || tab.startsWith('account-')" x-transition>
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
