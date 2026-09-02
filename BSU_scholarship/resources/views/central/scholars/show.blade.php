@extends('layouts.focused')

@section('navbar-title', 'Scholar Details')
@section('back-url', route('central.dashboard', ['tabs' => 'all_scholars']))
@section('back-text', 'Back to Scholars')

@section('content')
@php
    $statusClasses = [
        'active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
        'inactive' => 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'suspended' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
        'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200',
    ];

    $typeClasses = [
        'new' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200',
        'old' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200',
    ];

    $grantHistory = is_array($scholar->grant_history ?? null) ? $scholar->grant_history : [];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-bsu-red">Scholar Record</p>
            <h1 class="mt-1 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $scholar->user->name ?? 'Unknown Student' }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $scholar->user->email ?? 'No email on file' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('central.scholars.edit', $scholar->id) }}" class="inline-flex items-center rounded-lg bg-bsu-red px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                Edit Scholar
            </a>
            <form action="{{ route('central.scholars.destroy', $scholar->id) }}" method="POST" onsubmit="return confirm('Delete this scholar record? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-700 dark:bg-gray-800 dark:text-red-300 dark:hover:bg-red-950/30">
                    Delete
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.5fr_0.8fr]">
        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-5 flex items-center justify-between gap-3 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Student Details</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Profile and academic information</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $typeClasses[$scholar->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                        {{ ucfirst($scholar->type ?? 'new') }} scholarship
                    </span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Campus</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $scholar->user->campus->name ?? 'Unassigned' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Program</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $scholar->user->form?->program ?? 'Not available' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Year Level</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $scholar->user->form?->year_level ?? 'Not available' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Previous GWA</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $scholar->user->form?->previous_gwa ? number_format((float) $scholar->user->form->previous_gwa, 2) : 'Not available' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Contact Number</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $scholar->user->contact_number ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">SR Code</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $scholar->user->sr_code ?? 'Not provided' }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-5 border-b border-gray-200 pb-4 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Scholarship Details</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Scholarship Program</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $scholar->scholarship->scholarship_name ?? 'Unknown Scholarship' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</p>
                        <div class="mt-1">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$scholar->status] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                {{ ucfirst($scholar->status ?? 'active') }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Start Date</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ optional($scholar->scholarship_start_date)->format('F d, Y') ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">End Date</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ optional($scholar->scholarship_end_date)->format('F d, Y') ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Grant Count</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format((int) ($scholar->grant_count ?? 0)) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Grant Received</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">₱{{ number_format((float) ($scholar->total_grant_received ?? 0), 2) }}</p>
                    </div>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Quick Summary</h2>
                <div class="mt-5 space-y-4">
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900/40">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Award Type</div>
                        <div class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ ucfirst($scholar->type ?? 'new') }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900/40">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Grant History</div>
                        <div class="mt-1 text-lg font-bold text-gray-900 dark:text-white">{{ number_format((int) ($scholar->grant_count ?? 0)) }} disbursements</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900/40">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Scholarship Period</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                            {{ optional($scholar->scholarship_start_date)->format('M d, Y') ?? 'N/A' }}
                            @if($scholar->scholarship_end_date)
                                - {{ $scholar->scholarship_end_date->format('M d, Y') }}
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            @if($scholar->application)
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Original Application</h2>
                    <div class="mt-4 space-y-3 text-sm text-gray-700 dark:text-gray-300">
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500 dark:text-gray-400">Application ID</span>
                            <span class="font-semibold">#{{ $scholar->application->id }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500 dark:text-gray-400">Status</span>
                            <span class="font-semibold capitalize">{{ $scholar->application->status ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-500 dark:text-gray-400">Submitted</span>
                            <span class="font-semibold">{{ optional($scholar->application->created_at)->format('M d, Y') ?? 'N/A' }}</span>
                        </div>
                    </div>
                </section>
            @endif
        </aside>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 border-b border-gray-200 pb-4 dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Notes</h2>
        </div>
        <p class="whitespace-pre-wrap text-sm leading-6 text-gray-700 dark:text-gray-300">
            {{ $scholar->notes ?: 'No notes recorded for this scholar.' }}
        </p>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-4 flex items-center justify-between gap-3 border-b border-gray-200 pb-4 dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Grant History</h2>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                {{ count($grantHistory) }} entries
            </span>
        </div>

        @if(!empty($grantHistory))
            <div class="space-y-3">
                @foreach($grantHistory as $entry)
                    <div class="flex flex-col gap-2 rounded-xl border border-gray-200 bg-gray-50 p-4 md:flex-row md:items-center md:justify-between dark:border-gray-700 dark:bg-gray-900/40">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">
                                Grant #{{ $entry['grant_number'] ?? 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $entry['description'] ?? 'General grant release' }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-bsu-red">₱{{ number_format((float) ($entry['amount'] ?? 0), 2) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ isset($entry['date']) ? \Carbon::parse($entry['date'])->format('F d, Y') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-900/30 dark:text-gray-300">
                No grant history has been recorded for this scholar yet.
            </div>
        @endif
    </section>
</div>
@endsection
