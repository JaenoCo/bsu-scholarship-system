@extends('layouts.dashboard', ['user' => $user, 'title' => 'Scholar Details'])

@section('sidebar-menu')
    @include('central.components.sidebar-menu', ['user' => $user, 'campuses' => $campuses])
@endsection

@section('navbar')
    <x-layout.navbar
        title="Scholar Details"
        subtitle="Central Dashboard"
        :user="$user"
        :profile="false"
        :settings="true"
        settings-click="$dispatch('switch-tab', 'account_settings')"
        :logout="true"
    />
@endsection

@section('content')
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
            --bsu-radius: 14px;
        }

        .bsu-page-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .bsu-card {
            background: #fff;
            border: 1px solid var(--bsu-border);
            border-radius: var(--bsu-radius);
            box-shadow: var(--bsu-shadow);
        }

        .bsu-card-header {
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

        .bsu-kpi-card {
            border: 1px solid var(--bsu-border);
            border-radius: var(--bsu-radius);
            background: #fff;
            box-shadow: var(--bsu-shadow);
            padding: 1.1rem;
        }

        .bsu-kpi-label {
            color: var(--bsu-gray);
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .bsu-kpi-value {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
        }

        .bsu-kpi-icon {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
        }

        .bsu-kpi-icon-primary { background: rgba(123,17,19,.12); color: var(--bsu-primary); }
        .bsu-kpi-icon-success { background: rgba(34,197,94,.13); color: var(--bsu-success); }
        .bsu-kpi-icon-info { background: rgba(59,130,246,.13); color: var(--bsu-info); }
        .bsu-kpi-icon-warning { background: rgba(245,158,11,.14); color: var(--bsu-warning); }

        .bsu-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .4rem .7rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 700;
        }

        .bsu-badge-success { background: rgba(34,197,94,.13); color: #15803d; }
        .bsu-badge-info { background: rgba(59,130,246,.13); color: #1d4ed8; }
        .bsu-badge-secondary { background: #f1f5f9; color: #475569; }

        .bsu-list-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .bsu-list-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .bsu-list-label {
            font-weight: 700;
            color: #334155;
        }

        .bsu-list-value {
            color: #475569;
            text-align: right;
        }
    </style>

    <div class="mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <a href="{{ route('central.scholars.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Scholars
                    </a>
                </div>
                <h1 class="bsu-page-title">Scholar Details</h1>
                <p class="text-muted mb-0">Review accepted scholar information, award details, and scholarship status.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if(method_exists($scholar, 'canEdit') ? $scholar->canEdit() : true)
                    <a href="{{ route('central.scholars.edit', $scholar->id) }}" class="btn btn-bsu">Edit Scholar</a>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="bsu-card overflow-hidden">
                <div class="bsu-card-header d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="bsu-badge bsu-badge-success">{{ ucfirst($scholar->status ?? 'active') }}</span>
                            <span class="bsu-badge bsu-badge-info">{{ ucfirst($scholar->scholarship->scholarship_type ?? 'Scholarship') }}</span>
                        </div>
                        <h2 class="bsu-card-title mb-1">{{ $scholar->user->name ?? 'N/A' }}</h2>
                        <p class="bsu-card-subtitle mb-0">{{ $scholar->user->email ?? 'No email provided' }}</p>
                    </div>
                    <div class="text-lg-end">
                        <div class="text-muted small">Scholarship</div>
                        <div class="fw-bold text-dark">{{ $scholar->scholarship->scholarship_name ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="bsu-kpi-card">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="bsu-kpi-label">Campus</span>
                                    <span class="bsu-kpi-icon bsu-kpi-icon-primary"><i class="bi bi-buildings"></i></span>
                                </div>
                                <div class="bsu-kpi-value">{{ $scholar->user->campus->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="bsu-kpi-card">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="bsu-kpi-label">Program</span>
                                    <span class="bsu-kpi-icon bsu-kpi-icon-info"><i class="bi bi-mortarboard"></i></span>
                                </div>
                                <div class="bsu-kpi-value">{{ $scholar->user->program ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="bsu-kpi-card">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="bsu-kpi-label">Year Level</span>
                                    <span class="bsu-kpi-icon bsu-kpi-icon-warning"><i class="bi bi-calendar3"></i></span>
                                </div>
                                <div class="bsu-kpi-value">{{ $scholar->user->year_level ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="bsu-kpi-card">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="bsu-kpi-label">Grant Count</span>
                                    <span class="bsu-kpi-icon bsu-kpi-icon-success"><i class="bi bi-cash-stack"></i></span>
                                </div>
                                <div class="bsu-kpi-value">{{ $scholar->grant_count ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="bsu-card">
                <div class="bsu-card-header">
                    <div>
                        <h3 class="bsu-card-title">Scholar at a Glance</h3>
                        <p class="bsu-card-subtitle">Quick reference for the current award</p>
                    </div>
                </div>
                <div class="p-4">
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Status</span>
                        <span class="bsu-list-value">{{ ucfirst($scholar->status ?? 'active') }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Scholarship</span>
                        <span class="bsu-list-value">{{ $scholar->scholarship->scholarship_name ?? 'N/A' }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Grant Type</span>
                        <span class="bsu-list-value">{{ ucfirst(str_replace('_', ' ', $scholar->scholarship->grant_type ?? 'N/A')) }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Renewal</span>
                        <span class="bsu-list-value">{{ $scholar->scholarship->renewal_allowed ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="bsu-card">
                <div class="bsu-card-header">
                    <div>
                        <h3 class="bsu-card-title">Scholar Profile</h3>
                        <p class="bsu-card-subtitle">Personal and academic details</p>
                    </div>
                </div>
                <div class="p-4">
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Campus</span>
                        <span class="bsu-list-value">{{ $scholar->user->campus->name ?? 'N/A' }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Program</span>
                        <span class="bsu-list-value">{{ $scholar->user->program ?? 'N/A' }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Year Level</span>
                        <span class="bsu-list-value">{{ $scholar->user->year_level ?? 'N/A' }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Type</span>
                        <span class="bsu-list-value">{{ ucfirst($scholar->type ?? 'N/A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="bsu-card">
                <div class="bsu-card-header">
                    <div>
                        <h3 class="bsu-card-title">Scholarship & Application</h3>
                        <p class="bsu-card-subtitle">Award and application details</p>
                    </div>
                </div>
                <div class="p-4">
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Application ID</span>
                        <span class="bsu-list-value">{{ $scholar->application->id ?? 'N/A' }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Application Status</span>
                        <span class="bsu-list-value">{{ ucfirst($scholar->application->status ?? 'N/A') }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Grant Received</span>
                        <span class="bsu-list-value">{{ $scholar->total_grant_received ? '₱' . number_format($scholar->total_grant_received, 2) : '₱0.00' }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">Start Date</span>
                        <span class="bsu-list-value">{{ optional($scholar->scholarship_start_date)->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                    <div class="bsu-list-item">
                        <span class="bsu-list-label">End Date</span>
                        <span class="bsu-list-value">{{ optional($scholar->scholarship_end_date)->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <div class="bsu-card">
            <div class="bsu-card-header">
                <div>
                    <h3 class="bsu-card-title">Scholar Notes</h3>
                    <p class="bsu-card-subtitle">Optional remarks for this scholar</p>
                </div>
            </div>
            <div class="p-4">
                <p class="text-muted mb-0">{{ $scholar->notes ?: 'No notes available.' }}</p>
            </div>
        </div>
    </div>
@endsection
