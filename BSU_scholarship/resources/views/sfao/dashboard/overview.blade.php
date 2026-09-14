<div x-show="tab === 'dashboard'" x-cloak class="sfao-overview space-y-6">
    <header class="overview-hero rounded-xl p-6 shadow-sm">
        <p class="overview-eyebrow">SFAO workspace</p>
        <h1 class="overview-title mt-2 text-2xl font-bold tracking-tight md:text-3xl">Dashboard</h1>
        <p class="overview-description mt-2 max-w-3xl text-sm leading-6">A quick operational view of the students, applications, and reports that need your attention.</p>
    </header>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="overview-kpi overview-kpi-blue rounded-xl p-5"><p class="overview-kpi-label">Students in scope</p><p class="overview-kpi-value mt-2 text-3xl font-bold">{{ number_format($analytics['total_students'] ?? 0) }}</p></div>
        <div class="overview-kpi overview-kpi-amber rounded-xl p-5"><p class="overview-kpi-label">Needs decision</p><p class="overview-kpi-value mt-2 text-3xl font-bold">{{ number_format(($analytics['pending_applications'] ?? 0) + ($analytics['in_progress_applications'] ?? 0)) }}</p><p class="overview-kpi-note mt-1 text-xs">Pending and under review</p></div>
        <div class="overview-kpi overview-kpi-green rounded-xl p-5"><p class="overview-kpi-label">Active scholars</p><p class="overview-kpi-value mt-2 text-3xl font-bold">{{ number_format($scholarCounts['active'] ?? 0) }}</p></div>
        <div class="overview-kpi overview-kpi-violet rounded-xl p-5"><p class="overview-kpi-label">Approval rate</p><p class="overview-kpi-value mt-2 text-3xl font-bold">{{ $analytics['approval_rate'] ?? 0 }}%</p></div>
    </div>

    <section class="overview-panel rounded-xl p-6 shadow-sm"
             x-data='sfaoCampusComparison({ endpoint: @json(route("sfao.dashboard.campus-comparison")), campuses: @json($comparisonCampusOptions), scholarships: @json($comparisonScholarshipOptions), academicYears: @json($academicYears) })'>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="overview-panel-title text-lg font-bold">Campus scholarship comparison</h2>
                <p class="overview-panel-description mt-1 text-sm">Compare submitted applications with approved or claimed awards across campuses.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <label class="overview-filter-label text-sm font-medium">Campus
                    <select x-model="filters.campus" class="overview-filter mt-1 block w-full rounded-lg px-3 py-2 text-sm">
                        <option value="all">All campuses</option>
                        <template x-for="campus in campuses" :key="campus.id"><option :value="campus.id" x-text="campus.name"></option></template>
                    </select>
                </label>
                <label class="overview-filter-label text-sm font-medium">Scholarship
                    <select x-model="filters.scholarshipId" class="overview-filter mt-1 block w-full rounded-lg px-3 py-2 text-sm">
                        <option value="all">All scholarships</option>
                        <template x-for="scholarship in scholarships" :key="scholarship.id"><option :value="scholarship.id" x-text="scholarship.scholarship_name"></option></template>
                    </select>
                </label>
                <label class="overview-filter-label text-sm font-medium">Academic year
                    <select x-model="filters.academicYear" class="overview-filter mt-1 block w-full rounded-lg px-3 py-2 text-sm">
                        <option value="all">All academic years</option>
                        <template x-for="year in academicYears" :key="year"><option :value="year" x-text="year"></option></template>
                    </select>
                </label>
            </div>
        </div>
        <div class="relative mt-6 h-80" :class="loading ? 'opacity-50' : ''">
            <canvas x-ref="comparisonChart" aria-label="Campus scholarship comparison chart" role="img"></canvas>
        </div>
        <p x-show="error" x-text="error" class="mt-3 text-sm text-red-600 dark:text-red-300" role="alert"></p>
    </section>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="overview-panel rounded-xl p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between gap-4"><div><h2 class="overview-panel-title text-lg font-bold">Quick actions</h2><p class="overview-panel-description mt-1 text-sm">Open the workflow you need without duplicating analytics.</p></div></div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <button type="button" @click="$dispatch('switch-tab', 'applicants-pending')" class="overview-action overview-action-amber rounded-lg p-4 text-left"><p class="overview-action-title font-semibold">Review applications</p><p class="overview-action-description mt-1 text-sm">Process pending and under-review applications.</p></button>
                <button type="button" @click="$dispatch('switch-tab', 'scholars')" class="overview-action overview-action-green rounded-lg p-4 text-left"><p class="overview-action-title font-semibold">Manage scholars</p><p class="overview-action-description mt-1 text-sm">View active, new, and continuing scholars.</p></button>
                <a href="{{ route('sfao.reports.student-summary', ['student_type' => 'applicants', 'campus_id' => 'all']) }}" class="overview-action overview-action-red rounded-lg p-4"><p class="overview-action-title font-semibold">Student summary</p><p class="overview-action-description mt-1 text-sm">Generate a scoped student report.</p></a>
                <button type="button" @click="$dispatch('switch-tab', 'analytics_scholarships')" class="overview-action overview-action-indigo rounded-lg p-4 text-left"><p class="overview-action-title font-semibold">Explore insights</p><p class="overview-action-description mt-1 text-sm">Analyze scholarships with filters and exports.</p></button>
            </div>
        </section>
        <section class="overview-panel rounded-xl p-6 shadow-sm">
            <h2 class="overview-panel-title text-lg font-bold">Recent reports</h2>
            <div class="mt-4 space-y-3">
                @forelse($reports as $report)
                    <a href="{{ route('sfao.reports.show', $report->id) }}" class="overview-report block rounded-lg p-3"><p class="overview-action-title truncate font-semibold">{{ $report->title }}</p><p class="overview-action-description mt-1 text-xs">{{ ucfirst($report->status) }} &middot; {{ $report->created_at?->format('M d, Y') }}</p></a>
                @empty
                    <p class="overview-empty rounded-lg p-4 text-sm">No reports have been created yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
