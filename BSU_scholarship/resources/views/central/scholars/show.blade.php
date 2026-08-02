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
    @include('central.partials.page-header', [
        'title' => 'Scholar Details',
        'subtitle' => 'Review accepted scholar information and award details',
        'backUrl' => route('central.scholars.index'),
        'backText' => 'Back to Scholars',
    ])

    <div class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[1.5fr_0.9fr]">
            <section class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg overflow-hidden">
                <div class="px-6 py-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-[0.24em]">Scholar</p>
                            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $scholar->user->name ?? 'N/A' }}</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $scholar->user->email ?? 'No email provided' }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-green-100 text-green-800 px-3 py-1 text-xs font-semibold">{{ ucfirst($scholar->status) }}</span>
                            <span class="inline-flex items-center rounded-full bg-blue-100 text-blue-800 px-3 py-1 text-xs font-semibold">{{ ucfirst($scholar->scholarship->scholarship_type ?? 'Scholarship') }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-[0.16em] mb-4">Scholar Details</h3>
                            <div class="grid gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Campus</span><span>{{ $scholar->user->campus->name ?? 'N/A' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Program</span><span>{{ $scholar->user->program ?? 'N/A' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Year Level</span><span>{{ $scholar->user->year_level ?? 'N/A' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Type</span><span>{{ ucfirst($scholar->type ?? 'N/A') }}</span></div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-[0.16em] mb-4">Scholarship Details</h3>
                            <div class="grid gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Name</span><span>{{ $scholar->scholarship->scholarship_name ?? 'N/A' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Grant Type</span><span>{{ ucfirst(str_replace('_', ' ', $scholar->scholarship->grant_type ?? 'N/A')) }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Renewal</span><span>{{ $scholar->scholarship->renewal_allowed ? 'Yes' : 'No' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Archived</span><span>{{ $scholar->scholarship->is_active ? 'No' : 'Yes' }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
                        <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-[0.16em] mb-4">Application Summary</h3>
                            <div class="grid gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Application ID</span><span>{{ $scholar->application->id ?? 'N/A' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Status</span><span>{{ ucfirst($scholar->application->status ?? 'N/A') }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Grants Count</span><span>{{ $scholar->grant_count ?? 0 }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Total Grant Received</span><span>{{ $scholar->total_grant_received ? '₱' . number_format($scholar->total_grant_received, 2) : '₱0.00' }}</span></div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-[0.16em] mb-4">Scholarship Period</h3>
                            <div class="grid gap-3 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Start Date</span><span>{{ optional($scholar->scholarship_start_date)->format('M d, Y') ?? 'N/A' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">End Date</span><span>{{ optional($scholar->scholarship_end_date)->format('M d, Y') ?? 'N/A' }}</span></div>
                                <div class="flex justify-between gap-4"><span class="font-medium text-gray-700 dark:text-gray-300">Created</span><span>{{ optional($scholar->created_at)->format('M d, Y h:i A') ?? 'N/A' }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-5">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-[0.16em]">Scholar Notes</h3>
                            <span class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Optional</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $scholar->notes ?: 'No notes available.' }}</p>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Scholar At a Glance</h3>
                    <div class="grid gap-3 text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-gray-50 dark:bg-gray-900 p-4"><span class="font-medium text-gray-700 dark:text-gray-300">Campus</span><span>{{ $scholar->user->campus->name ?? 'N/A' }}</span></div>
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-gray-50 dark:bg-gray-900 p-4"><span class="font-medium text-gray-700 dark:text-gray-300">Scholarship</span><span>{{ $scholar->scholarship->scholarship_name ?? 'N/A' }}</span></div>
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-gray-50 dark:bg-gray-900 p-4"><span class="font-medium text-gray-700 dark:text-gray-300">Status</span><span>{{ ucfirst($scholar->status) }}</span></div>
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-gray-50 dark:bg-gray-900 p-4"><span class="font-medium text-gray-700 dark:text-gray-300">Grants</span><span>{{ $scholar->grant_count ?? 0 }}</span></div>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('central.scholars.index') }}" class="block rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition">Back to Scholars</a>
                        @if(method_exists($scholar, 'canEdit') ? $scholar->canEdit() : true)
                            <a href="{{ route('central.scholars.edit', $scholar->id) }}" class="block rounded-2xl bg-bsu-red text-white px-4 py-3 text-center text-sm font-semibold hover:bg-red-600 transition">Edit Scholar</a>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
