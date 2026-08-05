@php
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\Facades\Redirect;
    use App\Models\User;

    if (!Session::has('user_id')) {
        Redirect::to('login')->send();
    }

    $user = User::with('campus')->find(session('user_id'));

    if (!$user) {
        Session::flush();
        Redirect::to('login')->send();
    }

    $applicationStats = $analytics['applications'] ?? [];
    $userStats = $analytics['users'] ?? [];
    $scholarshipStats = $analytics['scholarships'] ?? [];
    $allApplicationsData = collect($analytics['all_applications_data'] ?? []);
    $applicationsCollection = collect($applications ?? []);

    $totalApplicants = (int) ($userStats['unique_applicants'] ?? $applicationStats['total'] ?? $applicationsCollection->count());
    $approvedApplications = (int) ($applicationStats['approved'] ?? $applicationsCollection->where('status', 'approved')->count());
    $pendingApplications = (int) ($applicationStats['pending'] ?? $applicationsCollection->where('status', 'pending')->count());
    $rejectedApplications = (int) ($applicationStats['rejected'] ?? $applicationsCollection->where('status', 'rejected')->count());
    $totalApplications = max(1, (int) ($applicationStats['total'] ?? $applicationsCollection->count()));
    $approvalRate = round(($approvedApplications / $totalApplications) * 100, 1);

    $campusLabels = collect($campuses ?? [])->map(fn ($campus) => $campus->name)->values();
    $campusApplicationCounts = collect($campuses ?? [])->map(function ($campus) use ($allApplicationsData, $applicationsCollection) {
        $fromAnalytics = $allApplicationsData->where('campus_id', $campus->id)->count();
        if ($fromAnalytics > 0) {
            return $fromAnalytics;
        }
        return $applicationsCollection->filter(fn ($application) => optional(optional($application)->user)->campus_id === $campus->id)->count();
    })->values();

    $distributionSource = $allApplicationsData->isNotEmpty()
        ? $allApplicationsData->groupBy(fn ($item) => $item->scholarship_type ?: 'Unspecified')->map->count()
        : $applicationsCollection->groupBy(fn ($application) => optional(optional($application)->scholarship)->scholarship_type ?: 'Unspecified')->map->count();

    $distributionLabels = $distributionSource->keys()->map(fn ($label) => ucfirst((string) $label))->values();
    $distributionValues = $distributionSource->values();

    $monthlyTrendLabels = data_get($applicationStats, 'monthly_trends.labels', []);
    $monthlyTrendValues = data_get($applicationStats, 'monthly_trends.data', []);

    $statusLabels = ['Approved', 'Pending', 'Rejected'];
    $statusValues = [$approvedApplications, $pendingApplications, $rejectedApplications];

    $recentApplications = $applicationsCollection
        ->sortByDesc(fn ($application) => $application->created_at)
        ->take(15)
        ->values();

    $lastUpdated = now()->format('F d, Y h:i A');

    $statusBadge = function ($status) {
        return match ($status) {
            'approved', 'claimed' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            'in_progress' => 'info',
            default => 'secondary',
        };
    };

    $activeTabRaw = request('tabs', request('tab', 'dashboard'));
    $activeTab = str_replace('-', '_', strtolower((string) $activeTabRaw));
    $activeTab = match ($activeTab) {
        'scholarships' => 'all_scholarships',
        'archived' => 'archived_scholarships',
        'scholars' => 'all_scholars',
        'applicants' => 'endorsed_applicants',
        'reports', 'sfao_reports' => 'sfao_reports',
        'analytics', 'statistics' => 'all_statistics',
        'overview' => 'dashboard',
        'users', 'user_management' => 'staff',
        'settings' => 'account_settings',
        default => $activeTab,
    };

    $dashboardTabs = ['dashboard', 'all_statistics'];
    $scholarshipTabs = ['all_scholarships', 'private_scholarships', 'government_scholarships', 'archived_scholarships'];
    $scholarTabs = ['all_scholars', 'new_scholars', 'old_scholars'];
    $applicantTabs = ['endorsed_applicants', 'rejected_applicants'];
    $currentPageTitle = match (true) {
        in_array($activeTab, $dashboardTabs, true) => $activeTab === 'all_statistics' ? 'Analytics' : 'Dashboard',
        in_array($activeTab, $scholarshipTabs, true) => 'Scholarships',
        in_array($activeTab, $scholarTabs, true) => 'Scholars',
        in_array($activeTab, $applicantTabs, true) => 'Applicants',
        $activeTab === 'sfao_reports' => 'Reports',
        $activeTab === 'staff' => 'User Management',
        $activeTab === 'account_settings' => 'Settings',
        default => 'Dashboard',
    };

    $navActive = fn (...$tabs) => in_array($activeTab, $tabs, true) ? ' active' : '';
    $headerNotifications = $headerNotifications ?? collect();
    $unreadNotificationCount = $unreadNotificationCount ?? 0;
    $centralScholarshipRows = $centralScholarshipRows ?? collect();
    $centralArchivedScholarshipRows = $centralArchivedScholarshipRows ?? collect();
    $centralScholarRows = $centralScholarRows ?? collect();
    $centralApplicationRows = $centralApplicationRows ?? collect();
    $centralStaffRows = $centralStaffRows ?? collect();
    $allReportsForReportsTab = $allReportsForReportsTab ?? collect();
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Scholarship Analytics Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('images/lugo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bsu-bg: #f8fafc;
            --bsu-primary: #7B1113;
            --bsu-secondary: #991B1B;
            --bsu-success: #22C55E;
            --bsu-warning: #F59E0B;
            --bsu-danger: #EF4444;
            --bsu-info: #3B82F6;
            --bsu-gray: #64748b;
            --bsu-border: #e5e7eb;
            --bsu-shadow: 0 2px 10px rgba(0,0,0,.05);
            --bsu-radius: 12px;
        }

        body {
            min-height: 100vh;
            background: var(--bsu-bg);
            color: #111827;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .bsu-topbar {
            height: 72px;
            background: #fff;
            border-bottom: 1px solid var(--bsu-border);
            box-shadow: var(--bsu-shadow);
            z-index: 1040;
        }

        .bsu-logo {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .bsu-brand-title {
            font-size: .95rem;
            font-weight: 800;
            color: var(--bsu-primary);
            line-height: 1.15;
        }

        .bsu-brand-subtitle {
            font-size: .72rem;
            color: var(--bsu-gray);
            line-height: 1.2;
        }

        .bsu-search {
            max-width: 480px;
        }

        .bsu-search .form-control {
            border-radius: 999px;
            border-color: var(--bsu-border);
            background: #f8fafc;
            padding-left: 2.75rem;
            min-height: 42px;
        }

        .bsu-search-icon {
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--bsu-gray);
            pointer-events: none;
        }

        .bsu-shell {
            padding-top: 72px;
        }

        .bsu-sidebar {
            position: fixed;
            inset: 72px auto 0 0;
            width: 280px;
            background: #fff;
            border-right: 1px solid var(--bsu-border);
            box-shadow: var(--bsu-shadow);
            overflow-y: auto;
            transition: transform .2s ease, width .2s ease;
            z-index: 1030;
        }

        .bsu-sidebar-collapsed .bsu-sidebar {
            width: 88px;
        }

        .bsu-main {
            margin-left: 280px;
            padding: 28px;
            transition: margin-left .2s ease;
        }

        .bsu-sidebar-collapsed .bsu-main {
            margin-left: 88px;
        }

        .bsu-nav-section {
            padding: 1rem;
        }

        .bsu-nav-label {
            margin: .75rem .75rem .35rem;
            color: #94a3b8;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .bsu-nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: #475569;
            text-decoration: none;
            border-radius: 10px;
            padding: .78rem .85rem;
            font-weight: 600;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }

        .bsu-nav-link:hover {
            background: #fff1f2;
            color: var(--bsu-primary);
            transform: translateX(2px);
        }

        .bsu-nav-link.active {
            background: var(--bsu-primary);
            color: #fff;
            box-shadow: 0 8px 18px rgba(123, 17, 19, .18);
        }

        .bsu-nav-icon {
            width: 22px;
            height: 22px;
            flex: 0 0 22px;
        }

        .bsu-sidebar-collapsed .bsu-nav-text,
        .bsu-sidebar-collapsed .bsu-nav-label,
        .bsu-sidebar-collapsed .bsu-sidebar-footer {
            display: none;
        }

        .bsu-page-title {
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0;
        }

        .breadcrumb-item a {
            color: var(--bsu-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .bsu-card,
        .bsu-kpi-card {
            background: #fff;
            border: 1px solid var(--bsu-border);
            border-radius: var(--bsu-radius);
            box-shadow: var(--bsu-shadow);
        }

        .bsu-kpi-card {
            padding: 1.25rem;
        }

        .bsu-kpi-label {
            color: var(--bsu-gray);
            font-size: .82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .bsu-kpi-value {
            font-size: 2rem;
            font-weight: 800;
            color: #0f172a;
        }

        .bsu-kpi-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
        }

        .bsu-kpi-icon-primary { background: rgba(123,17,19,.1); color: var(--bsu-primary); }
        .bsu-kpi-icon-success { background: rgba(34,197,94,.12); color: var(--bsu-success); }
        .bsu-kpi-icon-warning { background: rgba(245,158,11,.14); color: var(--bsu-warning); }
        .bsu-kpi-icon-danger { background: rgba(239,68,68,.12); color: var(--bsu-danger); }
        .bsu-kpi-icon-info { background: rgba(59,130,246,.12); color: var(--bsu-info); }

        .bsu-trend {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .25rem .55rem;
            font-size: .75rem;
            font-weight: 700;
        }

        .bsu-trend-up {
            background: rgba(34,197,94,.12);
            color: #15803d;
        }

        .bsu-trend-down {
            background: rgba(239,68,68,.12);
            color: #b91c1c;
        }

        .bsu-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.15rem 1.25rem;
            border-bottom: 1px solid var(--bsu-border);
        }

        .bsu-card-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #111827;
        }

        .bsu-card-subtitle {
            margin: .2rem 0 0;
            color: var(--bsu-gray);
            font-size: .84rem;
        }

        .bsu-chart-box {
            height: 310px;
            padding: 1.25rem;
        }

        .bsu-chart-box-lg {
            height: 360px;
        }

        .bsu-table {
            margin: 0;
            vertical-align: middle;
        }

        .bsu-table thead th {
            color: #475569;
            font-size: .74rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            background: #f8fafc;
            border-bottom: 1px solid var(--bsu-border);
            cursor: pointer;
            white-space: nowrap;
        }

        .bsu-table tbody td {
            color: #334155;
            border-color: #f1f5f9;
            font-size: .9rem;
        }

        .badge {
            font-weight: 700;
            padding: .45rem .65rem;
        }

        .btn-bsu {
            --bs-btn-bg: var(--bsu-primary);
            --bs-btn-border-color: var(--bsu-primary);
            --bs-btn-hover-bg: var(--bsu-secondary);
            --bs-btn-hover-border-color: var(--bsu-secondary);
            --bs-btn-color: #fff;
            --bs-btn-hover-color: #fff;
            border-radius: 10px;
            font-weight: 700;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .bsu-sidebar-backdrop {
            display: none;
        }

        @media (max-width: 991.98px) {
            .bsu-sidebar {
                transform: translateX(-100%);
                width: 280px;
            }

            .bsu-sidebar-open .bsu-sidebar {
                transform: translateX(0);
            }

            .bsu-main,
            .bsu-sidebar-collapsed .bsu-main {
                margin-left: 0;
                padding: 20px;
            }

            .bsu-sidebar-open .bsu-sidebar-backdrop {
                display: block;
                position: fixed;
                inset: 72px 0 0;
                background: rgba(15,23,42,.35);
                z-index: 1020;
            }

            .bsu-search {
                display: none;
            }
        }

        @media (max-width: 575.98px) {
            .bsu-topbar {
                height: 64px;
            }

            .bsu-shell {
                padding-top: 64px;
            }

            .bsu-sidebar {
                top: 64px;
            }

            .bsu-main {
                padding: 16px;
            }

            .bsu-kpi-value {
                font-size: 1.55rem;
            }
        }
    </style>
</head>
<body>
    <div id="bsuDashboardShell" class="bsu-shell">
        <nav class="navbar bsu-topbar fixed-top px-3 px-lg-4">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary btn-icon border-0" id="sidebarToggle" type="button" aria-label="Toggle navigation">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <img src="{{ asset('images/lugo.png') }}" alt="Batangas State University" class="bsu-logo">
                <div>
                    <div class="bsu-brand-title">BSU Scholarship System</div>
                    <div class="bsu-brand-subtitle">Central Administration Portal</div>
                </div>
            </div>

            <form class="bsu-search flex-grow-1 mx-4 position-relative" role="search" id="globalSearchForm" autocomplete="off">
                <span class="bsu-search-icon position-absolute">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                </span>
                <input class="form-control" id="globalSearch" type="search" placeholder="Search applicants, scholarships, campuses..." aria-label="Global search">
                <div class="dropdown-menu shadow border-0 w-100 mt-2 p-0 overflow-hidden" id="globalSearchResults"></div>
            </form>

            <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-light btn-icon position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        @if($unreadNotificationCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ min($unreadNotificationCount, 99) }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-0" style="width: 320px;">
                        <div class="px-3 py-2 border-bottom fw-bold">Notifications</div>
                        @forelse($headerNotifications as $notification)
                            <a class="dropdown-item py-3 text-wrap" href="{{ route('central.dashboard', ['tabs' => 'all_statistics']) }}">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-semibold">{{ $notification->title }}</span>
                                    @unless($notification->is_read)
                                        <span class="badge text-bg-danger">New</span>
                                    @endunless
                                </div>
                                <div class="small text-secondary">{{ \Illuminate\Support\Str::limit($notification->message, 80) }}</div>
                            </a>
                        @empty
                            <div class="px-3 py-4 text-center text-secondary small">No notifications yet.</div>
                        @endforelse
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 rounded-pill px-2 px-sm-3" data-bs-toggle="dropdown" type="button" aria-expanded="false">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-bold" style="width:32px;height:32px;background:var(--bsu-primary);">
                            {{ strtoupper(substr($user->name ?? 'CA', 0, 2)) }}
                        </span>
                        <span class="d-none d-sm-inline fw-semibold">{{ $user->name ?? 'Central Admin' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="{{ route('central.dashboard', ['tabs' => 'account_settings']) }}">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="{{ url('/logout') }}">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <aside class="bsu-sidebar">
            <div class="bsu-nav-section">
                <div class="bsu-nav-label">Administration</div>
                <a class="bsu-nav-link{{ $navActive('dashboard') }}" href="{{ route('central.dashboard', ['tabs' => 'dashboard']) }}">
                    <svg class="bsu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 13h8V3H3v10Zm10 8h8V3h-8v18ZM3 21h8v-6H3v6Z"/></svg>
                    <span class="bsu-nav-text">Dashboard</span>
                </a>
                <a class="bsu-nav-link{{ $navActive(...$scholarshipTabs) }}" href="{{ route('central.dashboard', ['tabs' => 'all_scholarships']) }}">
                    <svg class="bsu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15Z"/></svg>
                    <span class="bsu-nav-text">Scholarships</span>
                </a>
                <a class="bsu-nav-link{{ $navActive(...$scholarTabs) }}" href="{{ route('central.dashboard', ['tabs' => 'all_scholars']) }}">
                    <svg class="bsu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5-10-5Z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/></svg>
                    <span class="bsu-nav-text">Scholars</span>
                </a>
                <a class="bsu-nav-link{{ $navActive(...$applicantTabs) }}" href="{{ route('central.dashboard', ['tabs' => 'endorsed_applicants']) }}">
                    <svg class="bsu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>
                    <span class="bsu-nav-text">Applicants</span>
                </a>
                <a class="bsu-nav-link{{ $navActive('sfao_reports') }}" href="{{ route('central.dashboard', ['tabs' => 'sfao_reports']) }}">
                    <svg class="bsu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
                    <span class="bsu-nav-text">Reports</span>
                </a>

                <div class="bsu-nav-label">Insights</div>
                <a class="bsu-nav-link{{ $navActive('all_statistics') }}" href="{{ route('central.dashboard', ['tabs' => 'all_statistics']) }}">
                    <svg class="bsu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 16V9M12 16V5M17 16v-3"/></svg>
                    <span class="bsu-nav-text">Analytics</span>
                </a>
                <a class="bsu-nav-link{{ $navActive('staff') }}" href="{{ route('central.dashboard', ['tabs' => 'staff']) }}">
                    <svg class="bsu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span class="bsu-nav-text">User Management</span>
                </a>
                <a class="bsu-nav-link{{ $navActive('account_settings') }}" href="{{ route('central.dashboard', ['tabs' => 'account_settings']) }}">
                    <svg class="bsu-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 .6 1.65 1.65 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-.6-1 1.65 1.65 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-.6 1.65 1.65 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.2.36.3.76.3 1.17V10a2 2 0 1 1 0 4h-.09A1.65 1.65 0 0 0 19.4 15Z"/></svg>
                    <span class="bsu-nav-text">Settings</span>
                </a>
            </div>
            <div class="bsu-sidebar-footer p-3 small text-secondary">
                <div class="fw-bold text-dark">Central Office</div>
                <div>University-wide scholarship oversight</div>
            </div>
        </aside>
        <div class="bsu-sidebar-backdrop" id="sidebarBackdrop"></div>

        <main class="bsu-main">
            @if(in_array($activeTab, $dashboardTabs, true))
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Central</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $currentPageTitle }}</li>
                        </ol>
                    </nav>
                    <h1 class="bsu-page-title h2 mb-1">{{ $activeTab === 'all_statistics' ? 'Scholarship Analytics' : 'Central Dashboard' }}</h1>
                    <p class="text-secondary mb-0">Executive overview of scholarship applications, approvals, campus distribution, and program performance.</p>
                </div>
                <div class="text-xl-end">
                    <div class="small text-secondary">Last updated</div>
                    <div class="fw-bold">{{ $lastUpdated }}</div>
                    <a href="{{ url()->full() }}" class="btn btn-bsu btn-sm mt-2" id="refreshDashboard">Refresh Dashboard</a>
                </div>
            </div>

            <div class="row g-3 g-xl-4 mb-4">
                <div class="col-12 col-sm-6 col-xl">
                    @include('central.components.bootstrap-kpi-card', [
                        'title' => 'Total Applicants',
                        'value' => number_format($totalApplicants),
                        'trend' => '12.4%',
                        'trendDirection' => 'up',
                        'variant' => 'primary',
                        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>',
                    ])
                </div>
                <div class="col-12 col-sm-6 col-xl">
                    @include('central.components.bootstrap-kpi-card', [
                        'title' => 'Approved Applications',
                        'value' => number_format($approvedApplications),
                        'trend' => '8.1%',
                        'trendDirection' => 'up',
                        'variant' => 'success',
                        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>',
                    ])
                </div>
                <div class="col-12 col-sm-6 col-xl">
                    @include('central.components.bootstrap-kpi-card', [
                        'title' => 'Pending Applications',
                        'value' => number_format($pendingApplications),
                        'trend' => '3.7%',
                        'trendDirection' => 'up',
                        'variant' => 'warning',
                        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
                    ])
                </div>
                <div class="col-12 col-sm-6 col-xl">
                    @include('central.components.bootstrap-kpi-card', [
                        'title' => 'Rejected Applications',
                        'value' => number_format($rejectedApplications),
                        'trend' => '2.2%',
                        'trendDirection' => 'down',
                        'variant' => 'danger',
                        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>',
                    ])
                </div>
                <div class="col-12 col-sm-6 col-xl">
                    @include('central.components.bootstrap-kpi-card', [
                        'title' => 'Approval Rate',
                        'value' => $approvalRate . '%',
                        'trend' => '5.6%',
                        'trendDirection' => 'up',
                        'variant' => 'info',
                        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>',
                    ])
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-7">
                    <section class="bsu-card h-100">
                        <div class="bsu-card-header">
                            <div>
                                <h2 class="bsu-card-title">Applications by Campus</h2>
                                <p class="bsu-card-subtitle">Distribution of submitted scholarship applications across campuses.</p>
                            </div>
                            <span class="badge text-bg-light">{{ $campusLabels->count() }} campuses</span>
                        </div>
                        <div class="bsu-chart-box">
                            <canvas id="campusApplicationsChart"></canvas>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-xl-5">
                    <section class="bsu-card h-100">
                        <div class="bsu-card-header">
                            <div>
                                <h2 class="bsu-card-title">Scholarship Distribution</h2>
                                <p class="bsu-card-subtitle">Application share by scholarship category.</p>
                            </div>
                            <span class="badge text-bg-light">{{ number_format($scholarshipStats['total'] ?? 0) }} programs</span>
                        </div>
                        <div class="bsu-chart-box">
                            <canvas id="scholarshipDistributionChart"></canvas>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-8">
                    <section class="bsu-card h-100">
                        <div class="bsu-card-header">
                            <div>
                                <h2 class="bsu-card-title">Monthly Application Trends</h2>
                                <p class="bsu-card-subtitle">Volume of scholarship submissions by month.</p>
                            </div>
                        </div>
                        <div class="bsu-chart-box bsu-chart-box-lg">
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-xl-4">
                    <section class="bsu-card h-100">
                        <div class="bsu-card-header">
                            <div>
                                <h2 class="bsu-card-title">Approval Status Breakdown</h2>
                                <p class="bsu-card-subtitle">Current review status composition.</p>
                            </div>
                        </div>
                        <div class="bsu-chart-box bsu-chart-box-lg">
                            <canvas id="statusBreakdownChart"></canvas>
                        </div>
                    </section>
                </div>
            </div>

            <section class="bsu-card mb-4">
                <div class="bsu-card-header flex-column flex-lg-row align-items-lg-center">
                    <div>
                        <h2 class="bsu-card-title">Recent Applications</h2>
                        <p class="bsu-card-subtitle">Latest scholarship submissions requiring administrative visibility.</p>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
                        <input type="search" id="tableSearch" class="form-control" placeholder="Search table...">
                        <select id="pageSize" class="form-select" style="max-width:120px">
                            <option value="5">5 rows</option>
                            <option value="10">10 rows</option>
                            <option value="15">15 rows</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table bsu-table" id="applicationsTable">
                        <thead>
                            <tr>
                                <th data-sort="student">Student ID</th>
                                <th data-sort="name">Applicant Name</th>
                                <th data-sort="campus">Campus</th>
                                <th data-sort="scholarship">Scholarship</th>
                                <th data-sort="status">Status</th>
                                <th data-sort="date">Date Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentApplications as $application)
                                @php
                                    $student = $application->user;
                                    $scholarship = $application->scholarship;
                                    $submittedAt = $application->created_at;
                                @endphp
                                <tr>
                                    <td data-value="{{ $student->sr_code ?? 'APP-' . str_pad((string) $application->id, 5, '0', STR_PAD_LEFT) }}">
                                        <span class="fw-bold">{{ $student->sr_code ?? 'APP-' . str_pad((string) $application->id, 5, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td data-value="{{ $student->name ?? 'Unknown Applicant' }}">
                                        <div class="fw-semibold text-dark">{{ $student->name ?? 'Unknown Applicant' }}</div>
                                        <div class="small text-secondary">{{ $student->email ?? 'No email' }}</div>
                                    </td>
                                    <td data-value="{{ optional($student?->campus)->name ?? 'Unassigned' }}">{{ optional($student?->campus)->name ?? 'Unassigned' }}</td>
                                    <td data-value="{{ $scholarship->scholarship_name ?? 'Unknown Scholarship' }}">{{ $scholarship->scholarship_name ?? 'Unknown Scholarship' }}</td>
                                    <td data-value="{{ $application->status }}">
                                        <span class="badge text-bg-{{ $statusBadge($application->status) }}">{{ ucwords(str_replace('_', ' ', $application->status)) }}</span>
                                    </td>
                                    <td data-value="{{ optional($submittedAt)->timestamp ?? 0 }}">{{ optional($submittedAt)->format('M d, Y') ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('central.dashboard', ['tabs' => 'endorsed_applicants']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Review</a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="bsu-empty-row">
                                    <td colspan="7" class="text-center py-5 text-secondary">
                                        No applications available yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3 border-top">
                    <div class="small text-secondary" id="tableSummary">Showing 0 results</div>
                    <nav aria-label="Recent applications pagination">
                        <ul class="pagination pagination-sm mb-0" id="tablePagination"></ul>
                    </nav>
                </div>
            </section>
            @elseif(in_array($activeTab, $scholarshipTabs, true))
                @php
                    $isArchiveTab = $activeTab === 'archived_scholarships';
                    $scholarshipTypeFilter = $activeTab === 'private_scholarships' ? 'private' : ($activeTab === 'government_scholarships' ? 'government' : null);
                    $visibleScholarships = $isArchiveTab
                        ? $centralArchivedScholarshipRows
                        : ($scholarshipTypeFilter ? $centralScholarshipRows->where('scholarship_type', $scholarshipTypeFilter)->values() : $centralScholarshipRows);
                @endphp
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('central.dashboard', ['tabs' => 'dashboard']) }}">Central</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Scholarships</li>
                            </ol>
                        </nav>
                        <h1 class="bsu-page-title h2 mb-1">{{ $isArchiveTab ? 'Archived Scholarships' : 'Scholarship Programs' }}</h1>
                        <p class="text-secondary mb-0">{{ $isArchiveTab ? 'View scholarship programs removed from active student applications.' : 'Manage university-wide scholarship programs and application capacity.' }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-self-start">
                        <a href="{{ route('central.dashboard', ['tabs' => 'all_scholarships']) }}" class="btn btn-sm {{ $activeTab === 'all_scholarships' ? 'btn-bsu' : 'btn-outline-secondary' }}">All</a>
                        <a href="{{ route('central.dashboard', ['tabs' => 'private_scholarships']) }}" class="btn btn-sm {{ $activeTab === 'private_scholarships' ? 'btn-bsu' : 'btn-outline-secondary' }}">Private</a>
                        <a href="{{ route('central.dashboard', ['tabs' => 'government_scholarships']) }}" class="btn btn-sm {{ $activeTab === 'government_scholarships' ? 'btn-bsu' : 'btn-outline-secondary' }}">Government</a>
                        <a href="{{ route('central.dashboard', ['tabs' => 'archived_scholarships']) }}" class="btn btn-sm {{ $isArchiveTab ? 'btn-bsu' : 'btn-outline-secondary' }}">Archived</a>
                        @unless($isArchiveTab)
                            <a href="{{ route('central.scholarships.create') }}" class="btn btn-sm btn-bsu">Add Scholarship</a>
                        @endunless
                    </div>
                </div>

                <section class="bsu-card js-data-table" data-default-page-size="10">
                    <div class="bsu-card-header flex-column flex-lg-row align-items-lg-center">
                        <div>
                            <h2 class="bsu-card-title">{{ $isArchiveTab ? 'Archived' : ($scholarshipTypeFilter ? ucfirst($scholarshipTypeFilter) : 'All') }} Scholarships</h2>
                            <p class="bsu-card-subtitle">{{ $visibleScholarships->count() }} program{{ $visibleScholarships->count() === 1 ? '' : 's' }} found.</p>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
                            <input type="search" class="form-control js-table-search" placeholder="Search scholarships...">
                            <select class="form-select js-page-size" style="max-width:120px">
                                <option value="5">5 rows</option>
                                <option value="10" selected>10 rows</option>
                                <option value="15">15 rows</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table bsu-table mb-0">
                            <thead>
                                <tr>
                                    <th data-sort="0">Program</th>
                                    <th data-sort="1">Type</th>
                                    <th data-sort="2">Grant</th>
                                    <th data-sort="3">Deadline</th>
                                    <th data-sort="4">Applications</th>
                                    <th data-sort="5">Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visibleScholarships as $scholarship)
                                    <tr data-status="{{ $scholarship->is_active ? 'active' : 'archived' }}">
                                        <td data-value="{{ $scholarship->scholarship_name }}">
                                            <div class="fw-semibold text-dark">{{ $scholarship->scholarship_name }}</div>
                                            <div class="small text-secondary">{{ \Illuminate\Support\Str::limit($scholarship->description, 90) }}</div>
                                        </td>
                                        <td data-value="{{ $scholarship->scholarship_type }}"><span class="badge text-bg-light">{{ ucfirst($scholarship->scholarship_type) }}</span></td>
                                        <td data-value="{{ $scholarship->grant_amount ?? 0 }}">{{ $scholarship->grant_amount ? 'PHP ' . number_format((float) $scholarship->grant_amount, 2) : 'TBD' }}</td>
                                        <td data-value="{{ optional($scholarship->submission_deadline)->timestamp ?? 0 }}">{{ optional($scholarship->submission_deadline)->format('M d, Y') ?? 'No deadline' }}</td>
                                        <td data-value="{{ $scholarship->applications_count }}">{{ number_format($scholarship->applications_count) }}</td>
                                        <td data-value="{{ $scholarship->is_active ? 'active' : 'inactive' }}">
                                            <span class="badge text-bg-{{ $scholarship->is_active ? 'success' : 'secondary' }}">{{ $scholarship->is_active ? 'Active' : 'Archived' }}</span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('central.scholarships.edit', $scholarship->id) }}">Edit</a></li>
                                                    @if($scholarship->is_active)
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form method="POST" action="{{ route('central.scholarships.archive', $scholarship->id) }}" onsubmit="return confirm('Archive {{ addslashes($scholarship->scholarship_name) }}?')">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="dropdown-item text-warning">Archive</button>
                                                            </form>
                                                        </li>
<<<<<<< HEAD
                                                    @else
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form method="POST" action="{{ route('central.scholarships.unarchive', $scholarship->id) }}" onsubmit="return confirm('Restore {{ addslashes($scholarship->scholarship_name) }}?')">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="dropdown-item text-success">Restore</button>
                                                            </form>
                                                        </li>
=======
>>>>>>> ac0ab7ed7023aeef0abd0359714506de3871a55e
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bsu-empty-row"><td colspan="7" class="text-center py-5 text-secondary">No scholarships found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3 border-top">
                        <div class="small text-secondary js-table-summary">Showing 0 results</div>
                        <ul class="pagination pagination-sm mb-0 js-table-pagination"></ul>
                    </div>
                </section>
            @elseif(in_array($activeTab, $scholarTabs, true))
                @php
                    $scholarTypeFilter = $activeTab === 'new_scholars' ? 'new' : ($activeTab === 'old_scholars' ? 'old' : null);
                    $visibleScholars = $scholarTypeFilter ? $centralScholarRows->where('type', $scholarTypeFilter)->values() : $centralScholarRows;
                    $scholarStatusOptions = $visibleScholars->pluck('status')->filter()->unique()->sort()->values();
                @endphp
                <div class="mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('central.dashboard', ['tabs' => 'dashboard']) }}">Central</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Scholars</li>
                        </ol>
                    </nav>
                    <h1 class="bsu-page-title h2 mb-1">Scholars</h1>
                    <p class="text-secondary mb-0">Track accepted scholars, grants, campuses, and active scholarship records.</p>
                </div>
                <section class="bsu-card js-data-table" data-default-page-size="10">
                    <div class="bsu-card-header flex-column flex-lg-row align-items-lg-center">
                        <div>
                            <h2 class="bsu-card-title">{{ $scholarTypeFilter ? ucfirst($scholarTypeFilter) : 'All' }} Scholars</h2>
                            <p class="bsu-card-subtitle">{{ $visibleScholars->count() }} scholar{{ $visibleScholars->count() === 1 ? '' : 's' }} found.</p>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
                            <input type="search" class="form-control js-table-search" placeholder="Search scholars...">
                            <select class="form-select js-status-filter" style="max-width:150px" aria-label="Filter scholars by status">
                                <option value="all">All status</option>
                                @foreach($scholarStatusOptions as $statusOption)
                                    <option value="{{ $statusOption }}">{{ ucfirst(str_replace('_', ' ', $statusOption)) }}</option>
                                @endforeach
                            </select>
                            <select class="form-select js-page-size" style="max-width:120px">
                                <option value="5">5 rows</option>
                                <option value="10" selected>10 rows</option>
                                <option value="15">15 rows</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table bsu-table mb-0">
                            <thead>
                                <tr>
                                    <th data-sort="0">Student</th>
                                    <th data-sort="1">Campus</th>
                                    <th data-sort="2">Scholarship</th>
                                    <th data-sort="3">Type</th>
                                    <th data-sort="4">Grants</th>
                                    <th data-sort="5">Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visibleScholars as $scholar)
                                    <tr data-status="{{ $scholar->status }}">
                                        <td data-value="{{ $scholar->user->name ?? '' }}">
                                            <div class="fw-semibold text-dark">{{ $scholar->user->name ?? 'Unknown Student' }}</div>
                                            <div class="small text-secondary">{{ $scholar->user->email ?? 'No email' }}</div>
                                        </td>
                                        <td data-value="{{ $scholar->user->campus->name ?? '' }}">{{ $scholar->user->campus->name ?? 'Unassigned' }}</td>
                                        <td data-value="{{ $scholar->scholarship->scholarship_name ?? '' }}">{{ $scholar->scholarship->scholarship_name ?? 'Unknown Scholarship' }}</td>
                                        <td data-value="{{ $scholar->type }}"><span class="badge text-bg-info">{{ ucfirst($scholar->type) }}</span></td>
                                        <td data-value="{{ $scholar->grant_count }}">{{ number_format($scholar->grant_count) }}</td>
                                        <td data-value="{{ $scholar->status }}"><span class="badge text-bg-{{ $scholar->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($scholar->status) }}</span></td>
                                        <td>
                                            <a href="{{ route('central.scholars.show', $scholar->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                            <a href="{{ route('central.scholars.edit', $scholar->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bsu-empty-row"><td colspan="7" class="text-center py-5 text-secondary">No scholars found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3 border-top">
                        <div class="small text-secondary js-table-summary">Showing 0 results</div>
                        <ul class="pagination pagination-sm mb-0 js-table-pagination"></ul>
                    </div>
                </section>
            @elseif(in_array($activeTab, $applicantTabs, true))
                @php
                    $visibleApplications = $activeTab === 'endorsed_applicants'
                        ? collect($endorsedApplicants ?? [])
                        : collect($rejectedApplicants ?? []);
                    $applicantStatusOptions = $visibleApplications
                        ->map(fn ($record) => $activeTab === 'endorsed_applicants' ? ($record->status ?? 'in_progress') : 'rejected')
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();
                @endphp
                <div class="mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('central.dashboard', ['tabs' => 'dashboard']) }}">Central</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Applicants</li>
                        </ol>
                    </nav>
                    <h1 class="bsu-page-title h2 mb-1">{{ $activeTab === 'endorsed_applicants' ? 'Endorsed Applicants' : 'Rejected Applicants' }}</h1>
                    <p class="text-secondary mb-0">Review SFAO-endorsed applications and Central Administration decisions.</p>
                </div>
                <section class="bsu-card js-data-table" data-default-page-size="10">
                    <div class="bsu-card-header flex-column flex-lg-row align-items-lg-center">
                        <div>
                            <h2 class="bsu-card-title">{{ $activeTab === 'endorsed_applicants' ? 'For Central Validation' : 'Rejected Records' }}</h2>
                            <p class="bsu-card-subtitle">{{ $visibleApplications->count() }} record{{ $visibleApplications->count() === 1 ? '' : 's' }} found.</p>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
                            <input type="search" class="form-control js-table-search" placeholder="Search applicants...">
                            <select class="form-select js-status-filter" style="max-width:150px" aria-label="Filter applicants by status">
                                <option value="all">All status</option>
                                @foreach($applicantStatusOptions as $statusOption)
                                    <option value="{{ $statusOption }}">{{ ucfirst(str_replace('_', ' ', $statusOption === 'in_progress' ? 'endorsed' : $statusOption)) }}</option>
                                @endforeach
                            </select>
                            <select class="form-select js-page-size" style="max-width:120px">
                                <option value="5">5 rows</option>
                                <option value="10" selected>10 rows</option>
                                <option value="15">15 rows</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table bsu-table mb-0">
                            <thead>
                                <tr>
                                    <th data-sort="0">Applicant</th>
                                    <th data-sort="1">Campus</th>
                                    <th data-sort="2">Scholarship</th>
                                    <th data-sort="3">Status</th>
                                    <th data-sort="4">Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visibleApplications as $record)
                                    @php
                                        $application = $activeTab === 'endorsed_applicants' ? $record : $record->application;
                                        $student = $activeTab === 'endorsed_applicants' ? $record->user : $record->user;
                                        $scholarship = $activeTab === 'endorsed_applicants' ? $record->scholarship : $record->scholarship;
                                    @endphp
                                    <tr data-status="{{ $activeTab === 'endorsed_applicants' ? ($record->status ?? 'in_progress') : 'rejected' }}">
                                        <td data-value="{{ $student->name ?? '' }}">
                                            <div class="fw-semibold text-dark">{{ $student->name ?? 'Unknown Student' }}</div>
                                            <div class="small text-secondary">{{ $student->email ?? 'No email' }}</div>
                                        </td>
                                        <td data-value="{{ $student->campus->name ?? '' }}">{{ $student->campus->name ?? 'Unassigned' }}</td>
                                        <td data-value="{{ $scholarship->scholarship_name ?? '' }}">{{ $scholarship->scholarship_name ?? 'Unknown Scholarship' }}</td>
                                        <td data-value="{{ $activeTab === 'endorsed_applicants' ? 'endorsed' : 'rejected' }}">
                                            <span class="badge text-bg-{{ $activeTab === 'endorsed_applicants' ? 'info' : 'danger' }}">{{ $activeTab === 'endorsed_applicants' ? 'Endorsed' : 'Rejected' }}</span>
                                        </td>
                                        <td data-value="{{ optional($record->updated_at ?? $record->rejected_at)->timestamp ?? 0 }}">{{ optional($record->updated_at ?? $record->rejected_at)->format('M d, Y') ?? 'N/A' }}</td>
                                        <td>
                                            @if($activeTab === 'endorsed_applicants')
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="{{ route('central.endorsed.validate', $record->id) }}">View</a></li>
                                                        <li>
                                                            <form method="POST" action="{{ route('central.endorsed.accept', $record->id) }}">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item text-success">Approve</button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="{{ route('central.endorsed.reject', $record->id) }}" class="js-reject-applicant-form">
                                                                @csrf
                                                                <input type="hidden" name="rejection_reason">
                                                                <button type="button" class="dropdown-item text-danger js-reject-applicant" data-name="{{ $student->name ?? 'this applicant' }}">Reject</button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @else
                                                <span class="small text-secondary">{{ \Illuminate\Support\Str::limit($record->rejection_reason, 80) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bsu-empty-row"><td colspan="6" class="text-center py-5 text-secondary">No applicant records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3 border-top">
                        <div class="small text-secondary js-table-summary">Showing 0 results</div>
                        <ul class="pagination pagination-sm mb-0 js-table-pagination"></ul>
                    </div>
                </section>
            @elseif($activeTab === 'sfao_reports')
                <div class="mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('central.dashboard', ['tabs' => 'dashboard']) }}">Central</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Reports</li>
                        </ol>
                    </nav>
                    <h1 class="bsu-page-title h2 mb-1">SFAO Reports</h1>
                    <p class="text-secondary mb-0">Review submitted, reviewed, approved, and rejected SFAO reports.</p>
                </div>
                <section class="bsu-card js-data-table" data-default-page-size="10">
                    <div class="bsu-card-header flex-column flex-lg-row align-items-lg-center">
                        <div>
                            <h2 class="bsu-card-title">Reports</h2>
                            <p class="bsu-card-subtitle">{{ collect($allReportsForReportsTab ?? [])->count() }} report{{ collect($allReportsForReportsTab ?? [])->count() === 1 ? '' : 's' }} found.</p>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
                            <input type="search" class="form-control js-table-search" placeholder="Search reports...">
                            <select class="form-select js-page-size" style="max-width:120px">
                                <option value="5">5 rows</option>
                                <option value="10" selected>10 rows</option>
                                <option value="15">15 rows</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table bsu-table mb-0">
                            <thead>
                                <tr>
                                    <th data-sort="0">Title</th>
                                    <th data-sort="1">Campus</th>
                                    <th data-sort="2">Type</th>
                                    <th data-sort="3">Status</th>
                                    <th data-sort="4">Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(collect($allReportsForReportsTab ?? []) as $report)
                                    <tr>
                                        <td data-value="{{ $report->title }}"><div class="fw-semibold text-dark">{{ $report->title }}</div></td>
                                        <td data-value="{{ $report->campus_name }}">{{ $report->campus_name }}</td>
                                        <td data-value="{{ $report->report_type_display }}">{{ $report->report_type_display }}</td>
                                        <td data-value="{{ $report->status }}"><span class="badge text-bg-{{ $statusBadge($report->status) }}">{{ ucfirst($report->status) }}</span></td>
                                        <td data-value="{{ optional($report->submitted_at ?? $report->created_at)->timestamp ?? 0 }}">{{ $report->display_submitted_at }}</td>
                                        <td><a href="{{ route('central.reports.show', $report->id) }}" class="btn btn-sm btn-outline-secondary">{{ $report->status === 'submitted' ? 'Review' : 'View' }}</a></td>
                                    </tr>
                                @empty
                                    <tr class="bsu-empty-row"><td colspan="6" class="text-center py-5 text-secondary">No reports found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3 border-top">
                        <div class="small text-secondary js-table-summary">Showing 0 results</div>
                        <ul class="pagination pagination-sm mb-0 js-table-pagination"></ul>
                    </div>
                </section>
            @elseif($activeTab === 'staff')
                <div class="mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('central.dashboard', ['tabs' => 'dashboard']) }}">Central</a></li>
                            <li class="breadcrumb-item active" aria-current="page">User Management</li>
                        </ol>
                    </nav>
                    <h1 class="bsu-page-title h2 mb-1">User Management</h1>
                    <p class="text-secondary mb-0">Create and manage SFAO staff accounts for constituent campuses.</p>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-xl-4">
                        <section class="bsu-card">
                            <div class="bsu-card-header"><h2 class="bsu-card-title">Create SFAO Account</h2></div>
                            <form method="POST" action="{{ route('central.staff.invite') }}" class="p-3">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Campus</label>
                                    <select name="campus_id" class="form-select" required>
                                        <option value="">Select campus</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}">{{ $campus->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-bsu w-100">Create Account</button>
                            </form>
                        </section>
                    </div>
                    <div class="col-12 col-xl-8">
                        <section class="bsu-card js-data-table" data-default-page-size="10">
                            <div class="bsu-card-header flex-column flex-lg-row align-items-lg-center">
                                <div>
                                    <h2 class="bsu-card-title">SFAO Staff</h2>
                                    <p class="bsu-card-subtitle">{{ $centralStaffRows->count() }} staff account{{ $centralStaffRows->count() === 1 ? '' : 's' }} found.</p>
                                </div>
                                <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
                                    <input type="search" class="form-control js-table-search" placeholder="Search staff...">
                                    <select class="form-select js-page-size" style="max-width:120px">
                                        <option value="5">5 rows</option>
                                        <option value="10" selected>10 rows</option>
                                        <option value="15">15 rows</option>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table bsu-table mb-0">
                                    <thead><tr><th data-sort="0">Name</th><th data-sort="1">Email</th><th data-sort="2">Campus</th><th>Status</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        @forelse($centralStaffRows as $staff)
                                            <tr>
                                                <td data-value="{{ $staff->name }}">{{ $staff->name }}</td>
                                                <td data-value="{{ $staff->email }}">{{ $staff->email }}</td>
                                                <td data-value="{{ $staff->campus->name ?? '' }}">{{ $staff->campus->name ?? 'Unassigned' }}</td>
                                                <td><span class="badge text-bg-success">Active</span></td>
                                                <td>
                                                    <form method="POST" action="{{ route('central.staff.deactivate', $staff->id) }}" onsubmit="return confirm('Remove {{ addslashes($staff->name) }}?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="bsu-empty-row"><td colspan="5" class="text-center py-5 text-secondary">No SFAO staff found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3 border-top">
                                <div class="small text-secondary js-table-summary">Showing 0 results</div>
                                <ul class="pagination pagination-sm mb-0 js-table-pagination"></ul>
                            </div>
                        </section>
                    </div>
                </div>
            @elseif($activeTab === 'account_settings')
                <div class="mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('central.dashboard', ['tabs' => 'dashboard']) }}">Central</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Settings</li>
                        </ol>
                    </nav>
                    <h1 class="bsu-page-title h2 mb-1">Settings</h1>
                    <p class="text-secondary mb-0">Manage your Central Administration profile and account security.</p>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-xl-5">
                        <section class="bsu-card h-100">
                            <div class="bsu-card-header"><h2 class="bsu-card-title">Profile</h2></div>
                            <div class="p-4 text-center">
                                <img src="{{ $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) : asset('images/default-avatar.png') }}" alt="Profile" class="rounded-circle object-fit-cover mb-3" style="width: 112px; height: 112px;">
                                <h2 class="h5 mb-1">{{ $user->name }}</h2>
                                <p class="text-secondary mb-3">{{ $user->email }}</p>
                                <form method="POST" action="{{ url('/upload-profile-picture/central') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="profile_picture" class="form-control mb-3" accept="image/*" required>
                                    <button type="submit" class="btn btn-outline-secondary w-100">Upload Photo</button>
                                </form>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-7">
                        <section class="bsu-card mb-4">
                            <div class="bsu-card-header"><h2 class="bsu-card-title">Display Name</h2></div>
                            <form method="POST" action="{{ route('central.update-name') }}" class="p-4">
                                @csrf
                                <div class="input-group">
                                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                    <button class="btn btn-bsu" type="submit">Save</button>
                                </div>
                            </form>
                        </section>
                        <section class="bsu-card">
                            <div class="bsu-card-header"><h2 class="bsu-card-title">Password</h2></div>
                            <form method="POST" action="{{ route('central.change-password') }}" class="p-4">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">New Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" name="password_confirmation" class="form-control" required>
                                    </div>
                                </div>
                                <button class="btn btn-bsu mt-3" type="submit">Update Password</button>
                            </form>
                        </section>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">Unknown Central tab. <a href="{{ route('central.dashboard', ['tabs' => 'dashboard']) }}">Return to the dashboard</a>.</div>
            @endif
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        const dashboardData = {
            campusLabels: @json($campusLabels),
            campusApplications: @json($campusApplicationCounts),
            distributionLabels: @json($distributionLabels),
            distributionValues: @json($distributionValues),
            monthlyLabels: @json($monthlyTrendLabels),
            monthlyValues: @json($monthlyTrendValues),
            statusLabels: @json($statusLabels),
            statusValues: @json($statusValues)
        };

        const css = getComputedStyle(document.documentElement);
        const palette = {
            primary: css.getPropertyValue('--bsu-primary').trim(),
            secondary: css.getPropertyValue('--bsu-secondary').trim(),
            success: css.getPropertyValue('--bsu-success').trim(),
            warning: css.getPropertyValue('--bsu-warning').trim(),
            danger: css.getPropertyValue('--bsu-danger').trim(),
            info: css.getPropertyValue('--bsu-info').trim(),
            gray: css.getPropertyValue('--bsu-gray').trim()
        };

        const chartDefaults = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#475569', font: { family: 'Inter', weight: 600 } } }
            },
            scales: {
                x: { ticks: { color: '#64748b', font: { family: 'Inter' } }, grid: { color: '#f1f5f9' } },
                y: { ticks: { color: '#64748b', precision: 0, font: { family: 'Inter' } }, grid: { color: '#f1f5f9' } }
            }
        };

        const campusChart = document.getElementById('campusApplicationsChart');
        if (campusChart) {
            new Chart(campusChart, {
                type: 'bar',
                data: {
                    labels: dashboardData.campusLabels,
                    datasets: [{
                        label: 'Applications',
                        data: dashboardData.campusApplications,
                        backgroundColor: 'rgba(123, 17, 19, .82)',
                        borderRadius: 8,
                        maxBarThickness: 44
                    }]
                },
                options: chartDefaults
            });
        }

        const distributionChart = document.getElementById('scholarshipDistributionChart');
        if (distributionChart) {
            new Chart(distributionChart, {
                type: 'doughnut',
                data: {
                    labels: dashboardData.distributionLabels.length ? dashboardData.distributionLabels : ['No Data'],
                    datasets: [{
                        data: dashboardData.distributionValues.length ? dashboardData.distributionValues : [1],
                        backgroundColor: [palette.primary, palette.info, palette.warning, palette.success, palette.danger],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: chartDefaults.plugins }
            });
        }

        const trendsChart = document.getElementById('monthlyTrendsChart');
        if (trendsChart) {
            new Chart(trendsChart, {
                type: 'line',
                data: {
                    labels: dashboardData.monthlyLabels,
                    datasets: [{
                        label: 'Applications',
                        data: dashboardData.monthlyValues,
                        borderColor: palette.primary,
                        backgroundColor: 'rgba(123, 17, 19, .08)',
                        pointBackgroundColor: palette.primary,
                        pointRadius: 4,
                        fill: true,
                        tension: .35
                    }]
                },
                options: chartDefaults
            });
        }

        const statusChart = document.getElementById('statusBreakdownChart');
        if (statusChart) {
            new Chart(statusChart, {
                type: 'pie',
                data: {
                    labels: dashboardData.statusLabels,
                    datasets: [{
                        data: dashboardData.statusValues,
                        backgroundColor: [palette.success, palette.warning, palette.danger],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: chartDefaults.plugins }
            });
        }

        const shell = document.getElementById('bsuDashboardShell');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar() {
            if (window.innerWidth < 992) {
                shell.classList.toggle('bsu-sidebar-open');
            } else {
                shell.classList.toggle('bsu-sidebar-collapsed');
            }
        }

        if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
        if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', () => shell.classList.remove('bsu-sidebar-open'));

        function initDataTable(container) {
            const table = container.querySelector('table');
            const tbody = table?.querySelector('tbody');
            if (!table || !tbody) return;

            const allRows = Array.from(tbody.querySelectorAll('tr')).filter(row => !row.classList.contains('bsu-empty-row'));
            const searchInput = container.querySelector('.js-table-search') || document.getElementById('tableSearch');
            const statusFilter = container.querySelector('.js-status-filter');
            const pageSizeSelect = container.querySelector('.js-page-size') || document.getElementById('pageSize');
            const pagination = container.querySelector('.js-table-pagination') || document.getElementById('tablePagination');
            const summary = container.querySelector('.js-table-summary') || document.getElementById('tableSummary');
            let page = 1;
            let sortKey = null;
            let sortDirection = 1;

            function getCellValue(row, key) {
                const index = Number.isNaN(Number(key))
                    ? { student: 0, name: 1, campus: 2, scholarship: 3, status: 4, date: 5 }[key]
                    : Number(key);
                const cell = row.children[index];
                return (cell?.dataset.value || cell?.innerText || '').toString().toLowerCase();
            }

            function renderTable() {
                const term = (searchInput?.value || '').trim().toLowerCase();
                const status = (statusFilter?.value || 'all').toLowerCase();
                const pageSize = Number(pageSizeSelect?.value || container.dataset.defaultPageSize || 10);
                let rows = allRows.filter(row => {
                    const matchesSearch = row.innerText.toLowerCase().includes(term);
                    const matchesStatus = status === 'all' || (row.dataset.status || '').toLowerCase() === status;
                    return matchesSearch && matchesStatus;
                });

                if (sortKey !== null) {
                    rows = rows.sort((a, b) => {
                        const aVal = getCellValue(a, sortKey);
                        const bVal = getCellValue(b, sortKey);
                        return aVal.localeCompare(bVal, undefined, { numeric: true }) * sortDirection;
                    });
                }

                const pageCount = Math.max(1, Math.ceil(rows.length / pageSize));
                page = Math.min(page, pageCount);

                allRows.forEach(row => row.classList.add('d-none'));
                rows.slice((page - 1) * pageSize, page * pageSize).forEach(row => row.classList.remove('d-none'));

                if (summary) {
                    const from = rows.length ? ((page - 1) * pageSize) + 1 : 0;
                    const to = Math.min(page * pageSize, rows.length);
                    summary.textContent = `Showing ${from}-${to} of ${rows.length} results`;
                }

                if (pagination) {
                    pagination.innerHTML = '';
                    for (let i = 1; i <= pageCount; i++) {
                        const item = document.createElement('li');
                        item.className = `page-item ${i === page ? 'active' : ''}`;
                        item.innerHTML = `<button class="page-link" type="button">${i}</button>`;
                        item.addEventListener('click', () => { page = i; renderTable(); });
                        pagination.appendChild(item);
                    }
                }
            }

            table.querySelectorAll('thead th[data-sort]').forEach(header => {
                header.addEventListener('click', () => {
                    const key = header.dataset.sort;
                    sortDirection = sortKey === key ? sortDirection * -1 : 1;
                    sortKey = key;
                    renderTable();
                });
            });

            if (searchInput) searchInput.addEventListener('input', () => { page = 1; renderTable(); });
            if (statusFilter) statusFilter.addEventListener('change', () => { page = 1; renderTable(); });
            if (pageSizeSelect) pageSizeSelect.addEventListener('change', () => { page = 1; renderTable(); });
            renderTable();
        }

        document.querySelectorAll('.js-data-table').forEach(initDataTable);

        const dashboardTable = document.getElementById('applicationsTable')?.closest('section');
        if (dashboardTable && !dashboardTable.classList.contains('js-data-table')) {
            dashboardTable.classList.add('js-data-table');
            initDataTable(dashboardTable);
        }

        document.querySelectorAll('.js-reject-applicant').forEach(button => {
            button.addEventListener('click', () => {
                const form = button.closest('form');
                const reason = window.prompt(`Reason for rejecting ${button.dataset.name || 'this applicant'}:`);
                if (!reason || !reason.trim()) return;
                form.querySelector('input[name="rejection_reason"]').value = reason.trim();
                form.submit();
            });
        });

        const refreshDashboard = document.getElementById('refreshDashboard');
        if (refreshDashboard) {
            refreshDashboard.addEventListener('click', event => {
                event.preventDefault();
                window.location.reload();
            });
        }

        const globalSearch = document.getElementById('globalSearch');
        const globalSearchResults = document.getElementById('globalSearchResults');
        let globalSearchTimer = null;

        function hideGlobalSearchResults() {
            globalSearchResults?.classList.remove('show');
        }

        function renderGlobalSearchResults(groups) {
            if (!globalSearchResults) return;
            const entries = Object.entries(groups || {}).filter(([, items]) => Array.isArray(items) && items.length);
            if (!entries.length) {
                globalSearchResults.innerHTML = '<div class="px-3 py-3 small text-secondary">No matching records found.</div>';
                globalSearchResults.classList.add('show');
                return;
            }

            globalSearchResults.innerHTML = entries.map(([group, items]) => `
                <div class="border-bottom">
                    <div class="px-3 py-2 small fw-bold text-secondary text-uppercase">${group.replaceAll('_', ' ')}</div>
                    ${items.map(item => `
                        <a class="dropdown-item py-2" href="${item.url}">
                            <div class="fw-semibold">${item.text}</div>
                            <div class="small text-secondary">${item.detail || item.badge || ''}</div>
                        </a>
                    `).join('')}
                </div>
            `).join('');
            globalSearchResults.classList.add('show');
        }

        if (globalSearch) {
            globalSearch.addEventListener('input', () => {
                clearTimeout(globalSearchTimer);
                const term = globalSearch.value.trim();
                if (term.length < 2) {
                    hideGlobalSearchResults();
                    return;
                }

                globalSearchTimer = setTimeout(async () => {
                    try {
                        const response = await fetch(`/search/suggest?term=${encodeURIComponent(term)}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (!response.ok) throw new Error();
                        renderGlobalSearchResults(await response.json());
                    } catch {
                        globalSearchResults.innerHTML = '<div class="px-3 py-3 small text-danger">Search is temporarily unavailable.</div>';
                        globalSearchResults.classList.add('show');
                    }
                }, 250);
            });

            document.getElementById('globalSearchForm')?.addEventListener('submit', event => {
                event.preventDefault();
                const firstResult = globalSearchResults?.querySelector('a.dropdown-item');
                if (firstResult) window.location.href = firstResult.href;
            });

            document.addEventListener('click', event => {
                if (!document.getElementById('globalSearchForm')?.contains(event.target)) {
                    hideGlobalSearchResults();
                }
            });
        }
    </script>
</body>
</html>
