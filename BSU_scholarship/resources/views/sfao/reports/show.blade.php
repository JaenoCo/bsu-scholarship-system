@php
  use Illuminate\Support\Facades\Session;
  use Illuminate\Support\Facades\Redirect;
  use App\Models\User;

  // Redirect to login if session has ended or role mismatch
  if (!Session::has('user_id') || session('role') !== 'sfao') {
    return redirect()->route('login');
  }

  $user = User::find(session('user_id'));

  if (!$user) {
    Session::flush();
    return redirect()->route('login');
  }
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full"
    :class="{ 'dark': darkMode }"
    x-data="{ darkMode: localStorage.getItem('darkMode_{{ $user->id }}') === 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode_{{ $user->id }}', val))">
<head>
    <script>
        // Immediately apply dark mode preference to prevent FOUC
        if (localStorage.getItem('darkMode_{{ $user->id }}') === 'true' || (!('darkMode_{{ $user->id }}' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Report - SFAO Dashboard</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .bg-bsu-red { background-color: #dc2626; }
        .text-bsu-red { color: #dc2626; }
        .border-bsu-red { border-color: #dc2626; }
        .focus\:ring-bsu-red:focus { --tw-ring-color: #dc2626; }
        .focus\:border-bsu-red:focus { border-color: #dc2626; }
        .bg-bsu-redDark { background-color: #b91c1c; }
        .hover\:bg-bsu-redDark:hover { background-color: #b91c1c; }
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); }
        .glass-effect { backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.1); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .status-badge { position: relative; overflow: hidden; }
        .status-badge::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); transition: left 0.5s; }
        .status-badge:hover::before { left: 100%; }
        .fade-in { animation: fadeIn 0.6s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 min-h-screen transition-colors duration-200">

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            
            <!-- Page Header -->
            @include('sfao.components.page-header', [
                'title' => 'Report Details'
            ])
            
            <!-- Enhanced Header -->
            <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden mb-8 card-hover fade-in transition-colors duration-200">
                <div class="gradient-bg px-8 py-8 relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                            <defs>
                                <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#grid)" />
                        </svg>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-16 h-16 glass-effect rounded-2xl flex items-center justify-center mr-6 status-badge">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-3xl font-bold text-white mb-3">{{ $report->title }}</h1>
                                    <div class="flex items-center space-x-6 mb-3">
                                        <div class="flex items-center text-white/90">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            @if($report->original_campus_selection === 'constituent_with_extensions')
                                                {{ $report->campus->name }} + Extensions
                                            @else
                                                {{ $report->campus->name ?? 'Unknown Campus' }}
                                            @endif
                                        </div>
                                        <div class="flex items-center text-white/90">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $report->created_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-white/20 text-white backdrop-blur-sm">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                        <span class="text-white/80 text-sm">{{ $report->getReportTypeDisplayName() }}</span>
                                        <span class="text-white/80 text-sm">{{ $report->getPeriodDisplayName() }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($report->isDraft())
                                <div class="text-right">
                                    <div class="text-white/80 text-sm">Status</div>
                                    <div class="text-white font-semibold">Draft</div>
                                </div>
                            @else
                                <div class="text-right">
                                    <div class="text-white/80 text-sm">Status</div>
                                    <div class="text-white font-semibold capitalize">{{ $report->status }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($report->description)
                <div class="px-8 py-6 border-t border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-bsu-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Description
                    </h3>
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $report->description }}</p>
                    </div>
                </div>
                @endif

                @if($report->notes)
                <div class="px-8 py-6 border-t border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Notes
                    </h3>
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-4 shadow-sm border border-blue-200 dark:border-blue-800">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $report->notes }}</p>
                    </div>
                </div>
                @endif

                @if($report->central_feedback)
                <div class="px-8 py-6 border-t border-gray-200 dark:border-gray-700 bg-gradient-to-r from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Central Administration Feedback
                    </h3>
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-4 shadow-sm border border-yellow-200 dark:border-yellow-800">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3">{{ $report->central_feedback }}</p>
                        @if($report->reviewed_at)
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700 pt-3">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Reviewed on {{ \Carbon\Carbon::parse($report->reviewed_at)->format('M d, Y H:i A') }}
                                @if($report->reviewer)
                                    by {{ $report->reviewer->name }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Enhanced Summary Statistics -->
            <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden mb-8 card-hover fade-in transition-colors duration-200">
                <div class="gradient-bg px-8 py-6 relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                            <defs>
                                <pattern id="grid2" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#grid2)" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center">
                            <div class="w-12 h-12 glass-effect rounded-xl flex items-center justify-center mr-4 status-badge">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white">Summary Statistics</h3>
                                <p class="text-white/90">Overview of application data</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-8 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-600 shadow-lg card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-bsu-red dark:text-red-400">Total Applications</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $report->report_data['summary']['total_applications'] ?? 0 }}</p>
                                </div>
                                <div class="w-12 h-12 bg-bsu-red/10 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-bsu-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-600 shadow-lg card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-green-600 dark:text-green-400">Approved Applications</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $report->report_data['summary']['approved_applications'] ?? 0 }}</p>
                                </div>
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-600 shadow-lg card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-red-600 dark:text-red-400">Rejected Applications</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $report->report_data['summary']['rejected_applications'] ?? 0 }}</p>
                                </div>
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-600 shadow-lg card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Pending Applications</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $report->report_data['summary']['pending_applications'] ?? 0 }}</p>
                                </div>
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-600 shadow-lg card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Approval Rate</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($report->report_data['summary']['approval_rate'] ?? 0, 1) }}%</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-600 shadow-lg card-hover">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Total Scholarships</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $report->report_data['summary']['total_scholarships'] ?? 0 }}</p>
                                </div>
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($report->report_data['scholarship_performance']))
            <!-- Scholarship Performance -->
            <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden mb-8 card-hover fade-in transition-colors duration-200">
                <div class="gradient-bg px-8 py-6 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                            <defs>
                                <pattern id="grid3" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#grid3)" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center">
                            <div class="w-12 h-12 glass-effect rounded-xl flex items-center justify-center mr-4 status-badge">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Scholarship Performance</h3>
                        </div>
                    </div>
                </div>
                <div class="p-8 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Scholarship</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Applications</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Approved</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Approval Rate</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($report->report_data['scholarship_performance'] as $scholarship)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $scholarship['name'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ $scholarship['total_applications'] ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ $scholarship['approved_applications'] ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ ($scholarship['approval_rate'] ?? 0) >= 70 ? 'bg-green-100 text-green-800' : 
                                               (($scholarship['approval_rate'] ?? 0) >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $scholarship['approval_rate'] ?? 0 }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if(isset($report->report_data['performance_insights']))
            <!-- Enhanced Performance Insights -->
            <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden mb-8 card-hover fade-in transition-colors duration-200">
                <div class="gradient-bg px-8 py-6 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                            <defs>
                                <pattern id="grid4" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#grid4)" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center">
                            <div class="w-12 h-12 glass-effect rounded-xl flex items-center justify-center mr-4 status-badge">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Performance Insights</h3>
                        </div>
                    </div>
                </div>
                <div class="p-8 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-600 shadow-lg card-hover">
                            <h4 class="text-sm font-medium text-bsu-red dark:text-red-400 mb-2">Overall Performance Score</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report->report_data['performance_insights']['overall_score'] ?? 0 }}/100</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-600 shadow-lg card-hover">
                            <h4 class="text-sm font-medium text-bsu-red dark:text-red-400 mb-2">Campus Consistency</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report->report_data['performance_insights']['consistency_score'] ?? 0 }}%</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-600 shadow-lg card-hover">
                            <h4 class="text-sm font-medium text-bsu-red dark:text-red-400 mb-2">Scholarship Utilization</h4>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report->report_data['performance_insights']['utilization_score'] ?? 0 }}%</p>
                        </div>
                    </div>

                    @if(isset($report->report_data['performance_insights']['warnings']) && count($report->report_data['performance_insights']['warnings']) > 0)
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 mb-4">
                        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-400 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Areas of Concern
                        </h4>
                        <ul class="list-disc list-inside space-y-2">
                            @foreach($report->report_data['performance_insights']['warnings'] as $warning)
                            <li class="text-sm text-yellow-800 dark:text-yellow-300">{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if(isset($report->report_data['performance_insights']['recommendations']) && count($report->report_data['performance_insights']['recommendations']) > 0)
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6">
                        <h4 class="text-sm font-medium text-green-800 dark:text-green-400 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Recommendations
                        </h4>
                        <ul class="list-disc list-inside space-y-2">
                            @foreach($report->report_data['performance_insights']['recommendations'] as $recommendation)
                            <li class="text-sm text-green-800 dark:text-green-300">{{ $recommendation }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- ML-Powered Insights Section -->
            @if(isset($report->report_data['ml_insights']) && is_array($report->report_data['ml_insights']))
            <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden mb-8 card-hover fade-in transition-colors duration-200">
                <div class="gradient-bg px-8 py-8 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                            <defs>
                                <pattern id="gridml" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#gridml)" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center">
                            <div class="w-12 h-12 glass-effect rounded-xl flex items-center justify-center mr-4 status-badge">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white">AI-Powered Insights</h3>
                                <p class="text-white/90">Machine Learning Analysis & Predictions</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-8 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700">
                    
                    @if(!isset($report->report_data['ml_insights']['error']))
                    <!-- ML Predictions Section -->
                    @if(isset($report->report_data['ml_insights']['predictions']) && count($report->report_data['ml_insights']['predictions']) > 0)
                    <div class="mb-6">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-bsu-red" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" clip-rule="evenodd"></path>
                            </svg>
                            Predictive Analytics
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($report->report_data['ml_insights']['predictions'] as $prediction)
                            <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-bsu-red/20 dark:border-bsu-red/30">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $prediction['label'] ?? 'Prediction' }}</p>
                                <p class="text-2xl font-bold text-bsu-red">{{ number_format($prediction['value'] ?? 0, 1) }}{{ isset($prediction['unit']) ? $prediction['unit'] : '%' }}</p>
                                @if(isset($prediction['trend']))
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $prediction['trend'] }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Risk Factors Section -->
                    @if(isset($report->report_data['ml_insights']['risk_factors']) && count($report->report_data['ml_insights']['risk_factors']) > 0)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 mb-6">
                        <h4 class="text-sm font-bold text-red-800 dark:text-red-400 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 2.476.75.75 0 00.53 1.97a7.5 7.5 0 1010.5 10.5.75.75 0 00-.97.53l-.004.006z" clip-rule="evenodd"></path>
                            </svg>
                            Risk Factors Detected
                        </h4>
                        <ul class="list-disc list-inside space-y-2">
                            @foreach($report->report_data['ml_insights']['risk_factors'] as $risk)
                            <li class="text-sm text-red-800 dark:text-red-300">{{ $risk }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Opportunities Section -->
                    @if(isset($report->report_data['ml_insights']['opportunities']) && count($report->report_data['ml_insights']['opportunities']) > 0)
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-6">
                        <h4 class="text-sm font-bold text-blue-800 dark:text-blue-400 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm3.707 9.293a1 1 0 00-1.414-1.414L11 10.586V6a1 1 0 10-2 0v4.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3z" clip-rule="evenodd"></path>
                            </svg>
                            Growth Opportunities
                        </h4>
                        <ul class="list-disc list-inside space-y-2">
                            @foreach($report->report_data['ml_insights']['opportunities'] as $opportunity)
                            <li class="text-sm text-blue-800 dark:text-blue-300">{{ $opportunity }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- ML Recommendations Section -->
                    @if(isset($report->report_data['ml_insights']['recommendations']) && count($report->report_data['ml_insights']['recommendations']) > 0)
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                        <h4 class="text-sm font-bold text-green-800 dark:text-green-400 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            AI-Generated Recommendations
                        </h4>
                        <ul class="list-disc list-inside space-y-2">
                            @foreach($report->report_data['ml_insights']['recommendations'] as $recommendation)
                            <li class="text-sm text-green-800 dark:text-green-300">{{ $recommendation }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @else
                    <!-- ML Analysis Unavailable -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                        <p class="text-sm text-yellow-800 dark:text-yellow-300 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            ML Analysis: {{ $report->report_data['ml_insights']['error'] ?? 'Unable to generate AI insights at this time.' }}
                        </p>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            <!-- Enhanced Report Actions -->
            <div class="bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden card-hover fade-in transition-colors duration-200">
                <div class="gradient-bg px-8 py-6 relative overflow-hidden">
                    <div class="absolute inset-0 opacity-10">
                        <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                            <defs>
                                <pattern id="grid5" width="10" height="10" patternUnits="userSpaceOnUse">
                                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#grid5)" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center">
                            <div class="w-12 h-12 glass-effect rounded-xl flex items-center justify-center mr-4 status-badge">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Report Actions</h3>
                        </div>
                    </div>
                </div>
                <div class="p-8 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <!-- Report Info -->
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Report generated on {{ $report->created_at->format('M d, Y H:i A') }}
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            
                            @if($report->isDraft())
                                <!-- Draft Actions -->
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <!-- Submit Button -->
                                    <form method="POST" action="{{ route('sfao.reports.submit', $report->id) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-6 py-3 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-bsu-red hover:bg-bsu-redDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red transition duration-200 h-12">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                            Submit to Central
                                        </button>
                                    </form>
                                    
                                    <a href="{{ route('sfao.reports.edit', $report->id) }}"
                                       class="inline-flex items-center px-6 py-3 border border-bsu-red shadow-sm text-sm font-medium rounded-lg text-bsu-red bg-white dark:bg-gray-800 hover:bg-bsu-red hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red transition duration-200 h-12">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit Report
                                    </a>
                                    
                                    <form method="POST" action="{{ route('sfao.reports.delete', $report->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-6 py-3 border border-red-600 shadow-sm text-sm font-medium rounded-lg text-red-600 bg-white dark:bg-gray-800 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-200 h-12"
                                                onclick="return confirm('Are you sure you want to delete this report? This action cannot be undone.')">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete Report
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>