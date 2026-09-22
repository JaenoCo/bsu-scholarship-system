@php
    $gwaPrediction = $analytics['gwa_prediction'] ?? [];
    $gwaPredictionSummary = $gwaPrediction['summary'] ?? [];
    $gwaPredictionRows = collect($gwaPrediction['scholarships'] ?? []);
@endphp

@include('sfao.analytics.insights-dashboard')

<div x-show="tab === 'analytics_gwa'"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     x-cloak 
    x-data='sfaoStatisticsTab({ analytics: @json($analytics ?? []), campusOptions: @json($campusOptions), insightsEndpoint: @json(route("sfao.analytics.insights")), analyticsEndpoints: @json(["scholarships" => route("sfao.analytics.scholarships"), "applicants" => route("sfao.analytics.applicants"), "scholars" => route("sfao.analytics.scholars")]) })'
    @tab-changed.window="handleTabChange($event.detail)"
    class="sfao-gwa-analytics">
    <div class="space-y-6">
        <header class="analytics-hero rounded-xl p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="analytics-eyebrow">SFAO Analytics</p>
                    <h1 class="analytics-title mt-2 text-2xl font-bold tracking-tight md:text-3xl" x-text="analyticsPage.title">Scholarship Insights</h1>
                    <p class="analytics-description mt-2 max-w-3xl text-sm leading-6" x-text="analyticsPage.description">Monitor scholarship applications, scholar distribution, program performance, and institutional trends across campuses and scholarship programs.</p>
                </div>
                <!-- <div class="flex shrink-0 flex-wrap items-center gap-2" aria-label="Report export actions">
                    <a :href="analyticsExportUrl('pdf')" class="inline-flex min-h-[40px] items-center gap-2 rounded-lg bg-red-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-900"><span aria-hidden="true">&#128196;</span> Export PDF</a>
                    <a :href="analyticsExportUrl('xlsx')" class="inline-flex min-h-[40px] items-center gap-2 rounded-lg border border-red-800 px-4 py-2 text-sm font-semibold text-red-800 transition hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-900/20"><span aria-hidden="true">&#128202;</span> Export Excel</a>
                    <a :href="analyticsExportUrl('print')" target="_blank" rel="noopener" class="inline-flex min-h-[40px] items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"><span aria-hidden="true">&#128424;</span> Print Report</a>
                </div> -->
            </div>
        </header>


        <!-- Filter Controls -->
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <!-- Scholarship Program Filter -->
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">Scholarship program
                    <div class="relative">
                        <select x-model="filters.search" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">All Programs</option>
                            <template x-for="scholarship in (analyticsData.available_scholarships || [])" :key="scholarship.id">
                                <option :value="scholarship.scholarship_name" x-text="scholarship.scholarship_name"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    </label>
                </div>
   
                <!-- College Filter (Global) -->
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">College
                    <div class="relative">
                        <select x-model="localFilters.college" 
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                style="border-width: 1px;">
                            <option value="all">All</option>
                            <template x-for="college in availableColleges" :key="college.short_name">
                                <option :value="college.short_name" x-text="college.short_name"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    </label>
                </div>

                <!-- Program Filter (Global) -->
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">Program
                    <div class="relative">
                    <select x-model="localFilters.program" 
                                :key="localFilters.college"
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                style="border-width: 1px;">
                            <option value="all">All</option>
                            <template x-for="prog in availablePrograms" :key="prog">
                                <option :value="prog" x-text="prog"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    </label>
                </div>

                <!-- Track Filter (Global) -->
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">Track / Major
                    <div class="relative">
                    <select x-model="localFilters.track" 
                                :disabled="!availableTracks || availableTracks.length === 0"
                                :class="{'opacity-50 cursor-not-allowed': !availableTracks || availableTracks.length === 0}"
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                style="border-width: 1px;">
                            <option value="all" x-text="(!availableTracks || availableTracks.length === 0) ? 'No Tracks Available' : 'All'"></option>
                            <template x-for="track in availableTracks" :key="track">
                                <option :value="track" x-text="track"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    </label>
                </div>

                <!-- Time Period Filter -->
                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">Academic year
                    <div class="relative">
                        <select x-model="filters.timePeriod" 
                                class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                style="border-width: 1px;">
                            <option value="all">All</option>
                            <template x-for="ay in academicYearOptions" :key="ay">
                                <option :value="ay" x-text="ay"></option>
                            </template>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-400">
                             <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    </label>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">Semester
                    <select x-model="filters.semester" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="all">All Semesters</option><option value="first">First Semester</option><option value="second">Second Semester</option>
                    </select>
                    </label>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">Application status
                    <select x-model="filters.status" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="all">All Statuses</option><option value="pending">Pending</option><option value="under_review">Under Review</option><option value="approved">Approved</option><option value="rejected">Rejected</option>
                    </select>
                    </label>
                </div>

                <div class="flex items-end gap-2"><button type="button" @click="applyFilters()" :disabled="isLoading" class="rounded-lg bg-red-800 px-4 py-2 text-sm font-semibold text-white hover:bg-red-900 disabled:opacity-60">Apply filters</button><button type="button" @click="resetFilters()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-600 dark:border-gray-600 dark:text-gray-300">Reset</button></div>
            </div>
            
            <!-- Global Legend Buttons (Row 2) -->
            <div class="mt-4 flex flex-wrap justify-between gap-4 w-full">
                 <!-- Applicants Mode Legend -->
                <template x-if="subTab === 'applicants'">
                    <div class="grid w-full grid-cols-2 gap-2 lg:grid-cols-4" aria-label="Toggle application statuses on charts">
                        <!-- Approved -->
                         <button @click="chartLegend.approved = !chartLegend.approved" :aria-pressed="chartLegend.approved"
                                :class="{ 'is-active': chartLegend.approved }"
                                class="analytics-legend-toggle legend-approved">
                                <span class="analytics-legend-swatch"></span>
                                Approved
                        </button>
                        <!-- Rejected -->
                        <button @click="chartLegend.rejected = !chartLegend.rejected" :aria-pressed="chartLegend.rejected"
                                :class="{ 'is-active': chartLegend.rejected }"
                                class="analytics-legend-toggle legend-rejected">
                                <span class="analytics-legend-swatch"></span>
                                Rejected
                        </button>
                        <!-- Pending -->
                         <button @click="chartLegend.pending = !chartLegend.pending" :aria-pressed="chartLegend.pending"
                                :class="{ 'is-active': chartLegend.pending }"
                                class="analytics-legend-toggle legend-pending">
                                <span class="analytics-legend-swatch"></span>
                                Pending
                        </button>
                        <!-- Under Review (stored as in_progress) -->
                        <button @click="chartLegend.inProgress = !chartLegend.inProgress" :aria-pressed="chartLegend.inProgress"
                                :class="{ 'is-active': chartLegend.inProgress }"
                                class="analytics-legend-toggle legend-review">
                                <span class="analytics-legend-swatch"></span>
                                Under Review
                        </button>
                    </div>
                </template>

                 <!-- Scholars Mode Legend -->
                 <template x-if="subTab === 'scholars'">
                    <div class="grid w-full grid-cols-2 gap-2" aria-label="Toggle scholar types on charts">
                        <!-- Old Scholars -->
                        <button @click="chartLegend.oldScholars = !chartLegend.oldScholars" :aria-pressed="chartLegend.oldScholars"
                                :class="{ 'is-active': chartLegend.oldScholars }"
                                class="analytics-legend-toggle legend-continuing">
                                <span class="analytics-legend-swatch"></span>
                                Continuing Scholars
                        </button>
                        <!-- New Scholars -->
                        <button @click="chartLegend.newScholars = !chartLegend.newScholars" :aria-pressed="chartLegend.newScholars"
                                :class="{ 'is-active': chartLegend.newScholars }"
                                class="analytics-legend-toggle legend-new">
                                <span class="analytics-legend-swatch"></span>
                                New Scholars
                        </button>
                    </div>
                </template>

                <!-- Scholarships (Comparison) Mode Legend Moved Below Chart -->
            </div>
        </div>

        <div x-show="isLoading" x-cloak class="rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-800" role="status">
            Loading analytics...
        </div>

        <!-- GWA Qualification Prediction -->
        <div x-show="subTab === 'gwa'" x-data="{ metricModal: null, riskFilter: 'all', visibleRiskStudents() { const students = this.filteredData.gwa_prediction?.risk_students || []; return students.filter(student => this.riskFilter === 'all' || student.status === this.riskFilter); } }" x-cloak class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">GWA Qualification Prediction</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Predicted scholarship fit from student GWA against active scholarship GWA requirements.</p>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Generated {{ $gwaPredictionSummary['generated_at'] ?? now()->format('F d, Y h:i A') }}
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <button type="button" @click="metricModal = 'verified'" class="group rounded-xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between"><span class="text-lg text-slate-500 dark:text-gray-300" aria-hidden="true">&#10003;</span><span class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-gray-400">Verified GWA</span></div>
                    <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-white" x-text="filteredData.gwa_prediction?.summary?.students_with_gwa || 0"></p>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-gray-700"><div class="h-full rounded-full bg-slate-500 transition-all" :style="`width: ${filteredData.gwa_prediction?.summary?.total_students ? ((filteredData.gwa_prediction.summary.students_with_gwa / filteredData.gwa_prediction.summary.total_students) * 100) : 0}%`"></div></div>
                </button>
                <button type="button" @click="metricModal = 'missing'" class="group rounded-xl border border-red-100 bg-red-50 p-4 text-left shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-red-800 dark:bg-red-900/20">
                    <div class="flex items-center justify-between"><span class="text-lg text-red-600 dark:text-red-300" aria-hidden="true">&#9888;</span><span class="text-xs font-semibold uppercase tracking-wide text-red-600 dark:text-red-400">Unverified / Missing</span></div>
                    <p class="mt-3 text-2xl font-bold text-red-700 dark:text-red-300" x-text="filteredData.gwa_prediction?.summary?.students_missing_gwa || 0"></p>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-red-100 dark:bg-red-950"><div class="h-full rounded-full bg-red-500 transition-all" :style="`width: ${filteredData.gwa_prediction?.summary?.total_students ? ((filteredData.gwa_prediction.summary.students_missing_gwa / filteredData.gwa_prediction.summary.total_students) * 100) : 0}%`"></div></div>
                </button>
                <button type="button" @click="metricModal = 'qualified'" class="group rounded-xl border border-green-100 bg-green-50 p-4 text-left shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-green-800 dark:bg-green-900/20">
                    <div class="flex items-center justify-between"><span class="text-lg text-green-600 dark:text-green-300" aria-hidden="true">&#10003;</span><span class="text-xs font-semibold uppercase tracking-wide text-green-600 dark:text-green-400">Qualified Matches</span></div>
                    <p class="mt-3 text-2xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.gwa_prediction?.summary?.qualified_matches || 0"></p>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-green-100 dark:bg-green-950"><div class="h-full rounded-full bg-green-500 transition-all" :style="`width: ${filteredData.gwa_prediction?.summary?.evaluated_matches ? ((filteredData.gwa_prediction.summary.qualified_matches / filteredData.gwa_prediction.summary.evaluated_matches) * 100) : 0}%`"></div></div>
                </button>
                <button type="button" @click="metricModal = 'near-miss'" class="group rounded-xl border border-yellow-100 bg-yellow-50 p-4 text-left shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-yellow-800 dark:bg-yellow-900/20">
                    <div class="flex items-center justify-between"><span class="text-lg text-yellow-600 dark:text-yellow-300" aria-hidden="true">&#8599;</span><span class="text-xs font-semibold uppercase tracking-wide text-yellow-600 dark:text-yellow-400">Near Misses</span></div>
                    <p class="mt-3 text-2xl font-bold text-yellow-700 dark:text-yellow-300" x-text="filteredData.gwa_prediction?.summary?.near_miss_matches || 0"></p>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-yellow-100 dark:bg-yellow-950"><div class="h-full rounded-full bg-yellow-500 transition-all" :style="`width: ${filteredData.gwa_prediction?.summary?.evaluated_matches ? ((filteredData.gwa_prediction.summary.near_miss_matches / filteredData.gwa_prediction.summary.evaluated_matches) * 100) : 0}%`"></div></div>
                </button>
                <button type="button" @click="metricModal = 'rate'" class="group rounded-xl border border-blue-100 bg-blue-50 p-4 text-left shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex items-center justify-between"><span class="text-lg text-blue-600 dark:text-blue-300" aria-hidden="true">&#9673;</span><span class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">Prediction Rate</span></div>
                    <p class="mt-3 text-2xl font-bold text-blue-700 dark:text-blue-300" x-text="(filteredData.gwa_prediction?.summary?.qualification_rate || 0) + '%' "></p>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-blue-100 dark:bg-blue-950"><div class="h-full rounded-full bg-blue-500 transition-all" :style="`width: ${filteredData.gwa_prediction?.summary?.qualification_rate || 0}%`"></div></div>
                </button>
            </div>

            <div x-show="metricModal" x-cloak x-transition @keydown.escape.window="metricModal = null" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="metricModal = null">
                <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-xl bg-white dark:bg-gray-800 p-6 shadow-2xl" role="dialog" aria-modal="true">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white" x-text="{
                                verified: 'Verified GWA', missing: 'Unverified / Missing', qualified: 'Qualified Matches', 'near-miss': 'Near Misses', rate: 'Prediction Rate'
                            }[metricModal]"></h4>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300" x-text="{
                                verified: 'Students with an approved grades document and a recorded GWA.',
                                missing: 'Students in scope who still need an approved semester grades record with a GWA.',
                                qualified: 'Applied-scholarship matches where the latest approved GWA meets the scholarship requirement.',
                                'near-miss': 'Applied-scholarship matches within 0.25 of the required GWA.',
                                rate: 'Qualified matches divided by all evaluated applied-scholarship matches.'
                            }[metricModal]"></p>
                        </div>
                        <button type="button" @click="metricModal = null" class="text-gray-400 hover:text-gray-700 dark:hover:text-white" aria-label="Close modal">&times;</button>
                    </div>
                    <div class="mt-6 rounded-lg bg-gray-50 dark:bg-gray-700/50 p-5 text-center">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white" x-text="metricModal === 'verified' ? (filteredData.gwa_prediction?.summary?.students_with_gwa || 0) : metricModal === 'missing' ? (filteredData.gwa_prediction?.summary?.students_missing_gwa || 0) : metricModal === 'qualified' ? (filteredData.gwa_prediction?.summary?.qualified_matches || 0) : metricModal === 'near-miss' ? (filteredData.gwa_prediction?.summary?.near_miss_matches || 0) : (filteredData.gwa_prediction?.summary?.qualification_rate || 0) + '%'"></span>
                    </div>
                    <div class="mt-5 max-h-80 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full text-left text-sm">
                            <thead class="sticky top-0 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-200">
                                <tr><th class="px-3 py-2">Student</th><th class="px-3 py-2">SR Code</th><th class="px-3 py-2">Campus</th><th class="px-3 py-2">GWA</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="student in getGwaMetricStudents(metricModal)" :key="student.id">
                                    <tr><td class="px-3 py-2 text-gray-900 dark:text-white" x-text="student.name || 'Unnamed student'"></td><td class="px-3 py-2 text-gray-600 dark:text-gray-300" x-text="student.sr_code || 'Not provided'"></td><td class="px-3 py-2 text-gray-600 dark:text-gray-300" x-text="student.campus_name || 'Unknown campus'"></td><td class="px-3 py-2 font-semibold text-gray-900 dark:text-white" x-text="Number(student.gwa) > 0 ? Number(student.gwa).toFixed(2) : 'Missing'"></td></tr>
                                </template>
                                <tr x-show="getGwaMetricStudents(metricModal).length === 0"><td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No students match this metric and the selected filters.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
                <div class="xl:col-span-3">
                    <div class="text-center mb-4">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">Predicted Qualification by Scholarship</h4>
                    </div>
                    <div class="relative min-h-[520px] w-full">
                        <canvas id="sfaoGwaQualificationChart"></canvas>
                    </div>
                </div>
                <div class="xl:col-span-2">
                    <div class="text-center mb-4">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">Student GWA Distribution</h4>
                    </div>
                    <div class="relative h-80 w-full">
                        <canvas id="sfaoGwaBandChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Scholarship</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Required GWA</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Evaluated</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Predicted qualified</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Near miss</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="row in (filteredData.gwa_prediction?.scholarships || []).slice(0, 5)" :key="row.scholarship_id">
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-white"><div class="font-semibold" x-text="row.scholarship_name"></div><div class="text-xs text-gray-500 dark:text-gray-400" x-text="(row.scholarship_type || 'Unspecified').charAt(0).toUpperCase() + (row.scholarship_type || 'Unspecified').slice(1)"></div></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><span x-text="Number(row.required_gwa).toFixed(2)"></span> or better</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="row.total_evaluated"></td>
                                <td class="px-4 py-3 text-green-700 dark:text-green-300 font-semibold" x-text="row.qualified"></td>
                                <td class="px-4 py-3 text-yellow-700 dark:text-yellow-300 font-semibold" x-text="row.near_miss"></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300"><span x-text="Number(row.qualification_rate).toFixed(1)"></span>%</td>
                            </tr>
                        </template>
                        <tr x-show="!(filteredData.gwa_prediction?.scholarships || []).length">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No applied scholarships with a GWA requirement match these filters.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">Scholar Academic Risk</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Prioritize adviser follow-up using current GWA trend and scholarship retention risk.</p>
                    </div>
                    <select x-model="riskFilter" class="rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 dark:text-white" aria-label="Filter scholars by academic risk">
                        <option value="all">All statuses</option>
                        <option value="Critical">Critical</option>
                        <option value="At-Risk">At-Risk</option>
                        <option value="On Track">On Track</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr><th class="px-3 py-3 text-left">Student</th><th class="px-3 py-3 text-left">Scholarship</th><th class="px-3 py-3 text-left">Current GWA</th><th class="px-3 py-3 text-left">Graduation</th><th class="px-3 py-3 text-left">Retention</th><th class="px-3 py-3 text-left">Status</th><th class="px-3 py-3 text-left">Reason</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="student in visibleRiskStudents()" :key="student.student_id">
                                <tr :class="student.status === 'Critical' ? 'bg-red-50 dark:bg-red-900/20' : (student.status === 'At-Risk' ? 'bg-yellow-50 dark:bg-yellow-900/20' : '')">
                                    <td class="px-3 py-2"><div class="font-semibold text-gray-900 dark:text-white" x-text="student.name || 'Unknown Student'"></div><div class="text-xs text-gray-500" x-text="student.sr_code || 'No SR code'"></div></td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300" x-text="student.scholarship_name || 'No active scholarship'"></td>
                                    <td class="px-3 py-2 font-semibold text-gray-900 dark:text-white"><span x-text="student.latest_gwa ? Number(student.latest_gwa).toFixed(2) : 'Missing'"></span><span class="ml-2 inline-flex items-center text-sm font-bold" :class="student.trend === 'improving' ? 'text-green-600 dark:text-green-400' : (student.trend === 'declining' ? 'text-red-600 dark:text-red-400' : 'text-gray-400')" :title="student.trend === 'improving' ? 'Improving GWA' : (student.trend === 'declining' ? 'Declining GWA' : 'Stable or limited GWA history')" x-text="student.trend === 'improving' ? '↓' : (student.trend === 'declining' ? '↑' : '↔')"></span></td>
                                    <td class="px-3 py-2" x-text="student.graduation?.status || 'On Track'"></td>
                                    <td class="px-3 py-2" x-text="student.retention?.status || 'On Track'"></td>
                                    <td class="px-3 py-2"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset" :class="student.status === 'Critical' ? 'bg-red-100 text-red-900 ring-red-200 dark:bg-red-950/60 dark:text-red-100 dark:ring-red-800' : (student.status === 'At-Risk' ? 'bg-amber-100 text-amber-900 ring-amber-200 dark:bg-amber-950/60 dark:text-amber-100 dark:ring-amber-800' : 'bg-green-100 text-green-900 ring-green-200 dark:bg-green-950/60 dark:text-green-100 dark:ring-green-800')" x-text="student.status"></span></td>
                                    <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300" x-text="student.reasons?.[0] || 'No immediate risk identified'"></td>
                                    <!-- <td class="px-3 py-2"><div class="flex justify-end gap-1"><button type="button" @click="riskAction(student, 'contact')" class="rounded-md p-1.5 text-blue-700 hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-900/30" title="Email" aria-label="Contact adviser"><span aria-hidden="true">&#9993;</span></button></div></td> -->
                                </tr>
                            </template>
                            <tr x-show="visibleRiskStudents().length === 0"><td colspan="8" class="px-3 py-10"><div class="mx-auto flex max-w-sm flex-col items-center text-center"><div class="grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-2xl text-slate-500 dark:bg-gray-700 dark:text-gray-300" aria-hidden="true">&#128269;</div><p class="mt-3 font-semibold text-gray-900 dark:text-white">No scholars in this risk view</p><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try another status or reset the risk filter to review the full scholar population.</p><button type="button" @click="riskFilter = 'all'" class="mt-4 rounded-lg bg-red-800 px-4 py-2 text-sm font-semibold text-white hover:bg-red-900">Reset Risk Filters</button></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SCHOLARSHIPS SubTab Content -->
        <div x-show="subTab === 'scholarships'" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mt-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 text-center">Scholarship Applications by Program</h3>
            
            <!-- Scholarship Insights KPI Summary -->
            <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">Total Applications</p>
                    <p class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300" x-text="filteredData.scholarshipDistribution?.totalApplications || 0"></p>
                    <p class="mt-1 text-xs text-blue-700/70 dark:text-blue-300/70">Submitted applications in the selected scope</p>
                </div>
                <div class="rounded-lg border border-green-100 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <p class="text-xs font-semibold uppercase tracking-wide text-green-600 dark:text-green-400">Approved Scholars</p>
                    <p class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.scholarshipDistribution?.approvedApplications || 0"></p>
                    <p class="mt-1 text-xs text-green-700/70 dark:text-green-300/70">Approved applications in the selected scope</p>
                </div>
                <div class="rounded-lg border border-yellow-100 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <p class="text-xs font-semibold uppercase tracking-wide text-yellow-700 dark:text-yellow-400">Pending Applications</p>
                    <p class="mt-2 text-2xl font-bold text-yellow-700 dark:text-yellow-300" x-text="filteredData.scholarshipDistribution?.pendingApplications || 0"></p>
                    <p class="mt-1 text-xs text-yellow-700/70 dark:text-yellow-300/70">Applications still under evaluation</p>
                </div>
                <div class="rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-600 dark:text-red-400">Rejected Applications</p>
                    <p class="mt-2 text-2xl font-bold text-red-700 dark:text-red-300" x-text="filteredData.scholarshipDistribution?.rejectedApplications || 0"></p>
                    <p class="mt-1 text-xs text-red-700/70 dark:text-red-300/70">Applications not approved in the selected scope</p>
                </div>
                <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4 dark:border-indigo-800 dark:bg-indigo-900/20">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Approval Rate</p>
                    <p class="mt-2 text-2xl font-bold text-indigo-700 dark:text-indigo-300" x-text="(filteredData.scholarshipDistribution?.approvalRate || 0) + '%' "></p>
                    <p class="mt-1 text-xs text-indigo-700/70 dark:text-indigo-300/70">Approved applications out of all submitted applications</p>
                </div>
                <div class="rounded-lg border border-purple-100 bg-purple-50 p-4 dark:border-purple-800 dark:bg-purple-900/20">
                    <p class="text-xs font-semibold uppercase tracking-wide text-purple-600 dark:text-purple-400">Active Scholarship Programs</p>
                    <p class="mt-2 text-2xl font-bold text-purple-700 dark:text-purple-300" x-text="filteredData.scholarshipDistribution?.activePrograms || 0"></p>
                    <p class="mt-1 text-xs text-purple-700/70 dark:text-purple-300/70">Currently available scholarship programs</p>
                </div>
            </div>

            <!-- Most Applied Scholarship Programs -->
            <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-100 dark:border-gray-700 p-4 mb-6">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-3 text-center uppercase tracking-wide">Most Applied Scholarship Programs</h4>
                <template x-if="!filteredData.scholarshipDistribution?.ranking || filteredData.scholarshipDistribution.ranking.length === 0">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-2">No applicant data available for this selection.</p>
                </template>
                <div x-show="filteredData.scholarshipDistribution?.ranking && filteredData.scholarshipDistribution.ranking.length > 0"
                     class="relative w-full" style="height: 220px;">
                    <canvas id="sfaoScholarshipRankingChart"></canvas>
                </div>
            </div>

            <div class="mb-3 mt-8 flex items-center justify-between gap-3">
                <h4 class="text-base font-bold text-gray-900 dark:text-white">Scholarship Applications and Scholars by College</h4>
            </div>
            <div class="analytics-chart-legend mb-4" aria-label="Chart legend">
                <span class="analytics-legend-key legend-applicants"><span class="analytics-legend-swatch"></span>Applicants</span>
                <span class="analytics-legend-key legend-approved-scholars"><span class="analytics-legend-swatch"></span>Approved Scholars</span>
            </div>
            <div class="relative h-96 w-full mb-6">
                 <div x-show="chartStatus.comparison" class="h-full w-full">
                    <canvas id="sfaoComparisonChart"></canvas>
                </div>
                 <!-- No Data Message -->
                 <div x-show="!chartStatus.comparison" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="text-center p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white" x-text="(!chartLegend.applicants && !chartLegend.scholars) ? 'Select a category to view data' : ((filters.search && filters.search.trim() !== '') ? 'There is no Scholarship named ' + filters.search : 'No Comparison Data')"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters.</p>
                    </div>
                </div>
            </div>

            <!-- Scholarship Applications Trend -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h4 class="text-md font-bold text-gray-900 dark:text-white mb-1 text-center">Scholarship Applications Trend</h4>
                <p class="mb-4 text-center text-sm text-gray-500 dark:text-gray-400">Track application and scholar activity over time for the selected filters.</p>
                <div class="relative h-64 w-full">
                    <div x-show="chartStatus.trend" class="h-full w-full">
                        <canvas id="sfaoTrendChart"></canvas>
                    </div>
                    <div x-show="!chartStatus.trend" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No trend data available for this selection.</p>
                    </div>
                </div>
            </div>

            <section class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                <h4 class="text-base font-bold text-gray-900 dark:text-white">Key Insights</h4>
                <ul class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <template x-for="insight in (serverInsights?.insights || [])" :key="insight"><li>• <span x-text="insight"></span></li></template>
                    <li x-show="!serverInsights">• Loading filter-specific insights…</li>
                </ul>
            </section>

            <!-- Scholarships (Comparison) Mode Legend (Moved Here) -->

        </div>

        <!-- APPLICANTS & SCHOLARS Charts Section -->
        <div x-show="subTab === 'applicants' || subTab === 'scholars'" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mt-6">
            <!-- College Chart Header -->
            <div class="mb-4 text-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="getChartTitle()">Scholarship Distribution by Campus</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="filters.campus === 'all' ? 'Compare applicants and approved scholars across campuses.' : 'Compare applicants and approved scholars across colleges.'">Compare applicants and approved scholars across campuses.</p>
            </div>

            <div class="analytics-chart-legend mb-6 justify-center" aria-label="College chart legend">
                <span class="analytics-legend-key legend-applicants"><span class="analytics-legend-swatch"></span>Applicants</span>
                <span class="analytics-legend-key legend-approved-scholars"><span class="analytics-legend-swatch"></span>Approved Scholars</span>
            </div>


            <!-- Filters Section within Card - REMOVED (Moved to Global) -->

            <!-- Dynamic Summary Counts -->
            <template x-if="subTab === 'applicants'">
                <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                     <!-- Total -->
                     <div x-on:click="openStudentDetails('total')"
                          x-on:keydown.enter.prevent="openStudentDetails('total')"
                          x-on:keydown.space.prevent="openStudentDetails('total')"
                          role="button"
                          tabindex="0"
                          class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center border border-gray-100 dark:border-gray-600 cursor-pointer hover:ring-1 hover:ring-gray-300 transition">
                         <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</p>
                         <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="filteredData.counts?.total || 0"></p>
                     </div>
                     <!-- Approved -->
                     <div x-on:click="openStudentDetails('approved')"
                          x-on:keydown.enter.prevent="openStudentDetails('approved')"
                          x-on:keydown.space.prevent="openStudentDetails('approved')"
                          role="button"
                          tabindex="0"
                          class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center border border-green-100 dark:border-green-800 cursor-pointer hover:ring-1 hover:ring-green-300 transition">
                         <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Approved</p>
                         <p class="text-xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.counts?.approved || 0"></p>
                     </div>
                     <!-- Rejected -->
                     <div x-on:click="openStudentDetails('rejected')"
                          x-on:keydown.enter.prevent="openStudentDetails('rejected')"
                          x-on:keydown.space.prevent="openStudentDetails('rejected')"
                          role="button"
                          tabindex="0"
                          class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 text-center border border-red-100 dark:border-red-800 cursor-pointer hover:ring-1 hover:ring-red-300 transition">
                         <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase">Rejected</p>
                         <p class="text-xl font-bold text-red-700 dark:text-red-300" x-text="filteredData.counts?.rejected || 0"></p>
                     </div>
                     <!-- Active / Pending -->
                     <div x-on:click="openStudentDetails('active')"
                          x-on:keydown.enter.prevent="openStudentDetails('active')"
                          x-on:keydown.space.prevent="openStudentDetails('active')"
                          role="button"
                          tabindex="0"
                          class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 text-center border border-yellow-100 dark:border-yellow-800 cursor-pointer hover:ring-1 hover:ring-yellow-300 transition">
                         <p class="text-xs font-semibold text-yellow-600 dark:text-yellow-400 uppercase">Pending / Under Review</p>
                         <p class="text-xl font-bold text-yellow-700 dark:text-yellow-300" x-text="filteredData.counts?.active || 0"></p>
                     </div>
                     <!-- Rate -->
                     <div x-on:click="openStudentDetails('approvalRate')"
                          x-on:keydown.enter.prevent="openStudentDetails('approvalRate')"
                          x-on:keydown.space.prevent="openStudentDetails('approvalRate')"
                          role="button"
                          tabindex="0"
                          class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center border border-blue-100 dark:border-blue-800 cursor-pointer hover:ring-1 hover:ring-blue-300 transition">
                         <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Approval Rate</p>
                         <p class="text-xl font-bold text-blue-700 dark:text-blue-300" x-text="(filteredData.counts?.approvalRate || '0.0') + '%'"></p>
                     </div>
                </div>
            </template>

            <template x-if="subTab === 'scholars'">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                     <!-- Total Scholars -->
                     <div x-on:click="openStudentDetails('total')"
                          x-on:keydown.enter.prevent="openStudentDetails('total')"
                          x-on:keydown.space.prevent="openStudentDetails('total')"
                          role="button"
                          tabindex="0"
                          class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 text-center border border-gray-100 dark:border-gray-600 cursor-pointer hover:ring-1 hover:ring-gray-300 transition">
                         <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Scholars</p>
                         <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="filteredData.counts?.total || 0"></p>
                     </div>
                     <!-- New Scholars -->
                     <div x-on:click="openStudentDetails('newScholars')"
                          x-on:keydown.enter.prevent="openStudentDetails('newScholars')"
                          x-on:keydown.space.prevent="openStudentDetails('newScholars')"
                          role="button"
                          tabindex="0"
                          class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 text-center border border-blue-100 dark:border-blue-800 cursor-pointer hover:ring-1 hover:ring-blue-300 transition">
                         <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">New Scholars</p>
                         <p class="text-xl font-bold text-blue-700 dark:text-blue-300" x-text="filteredData.counts?.newScholars || 0"></p>
                     </div>
                     <!-- Continuing / Old Scholars -->
                     <div x-on:click="openStudentDetails('oldScholars')"
                          x-on:keydown.enter.prevent="openStudentDetails('oldScholars')"
                          x-on:keydown.space.prevent="openStudentDetails('oldScholars')"
                          role="button"
                          tabindex="0"
                          class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 text-center border border-green-100 dark:border-green-800 cursor-pointer hover:ring-1 hover:ring-green-300 transition">
                         <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Continuing Scholars</p>
                         <p class="text-xl font-bold text-green-700 dark:text-green-300" x-text="filteredData.counts?.oldScholars || 0"></p>
                     </div>
                </div>
            </template>

            <!-- Chart Container -->
            <div class="relative h-96 w-full mb-6">
                <div x-show="chartStatus.college" class="h-full w-full">
                    <canvas id="sfaoCollegeChart"></canvas>
                </div>
                <!-- No Data Message -->
                <div x-show="!chartStatus.college" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="text-center p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white" 
                            x-text="filters.search ? 'No scholarship named \'' + filters.search + '\' found' : (viewMode === 'applicants' ? 'No Applicants Found' : 'No Scholars Found')">
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" 
                           x-text="filters.search ? 'Try checking for typos or use a different keyword.' : 'Try adjusting your filters.'">
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Student Details Modal -->
        <div x-show="studentDetails.open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/50" @click="closeStudentDetails()"></div>
            <div class="relative max-w-4xl w-full bg-white dark:bg-gray-900 rounded-lg shadow-lg overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="studentDetails.title"></h3>
                    <button @click="closeStudentDetails()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 max-h-[60vh] overflow-auto">
                    <template x-if="!studentDetails.rows || studentDetails.rows.length === 0">
                        <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">No students to display for this selection.</div>
                    </template>

                    <template x-if="studentDetails.rows && studentDetails.rows.length > 0">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Student #</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Name</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Campus</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">College</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Program</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Scholarship</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="row in studentDetails.rows" :key="row.key">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.studentNumber"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.name"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.campus"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.college"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.program"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.scholarship"></td>
                                        <td class="px-3 py-2 text-gray-800 dark:text-gray-200" x-text="row.status"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </template>
                </div>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                    <button @click="closeStudentDetails()" class="px-4 py-2 rounded-md bg-bsu-red text-white">Close</button>
                </div>
            </div>
        </div>
    </div>

<script>
    (() => {
        const prediction = @json($gwaPrediction);
        const state = window.sfaoGwaPredictionCharts || (window.sfaoGwaPredictionCharts = {});

        function textColor() {
            return document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#374151';
        }

        function gridColor() {
            return document.documentElement.classList.contains('dark') ? 'rgba(229, 231, 235, .14)' : 'rgba(15, 23, 42, .08)';
        }

        function renderSfaoGwaPredictionCharts() {
            if (typeof Chart === 'undefined') return;
            const currentPrediction = window.sfaoFilteredGwaPrediction || prediction;
            const rows = (currentPrediction.scholarships || []).slice(0, 8);
            const bands = currentPrediction.bands || [];

            const qualificationCanvas = document.getElementById('sfaoGwaQualificationChart');
            const bandCanvas = document.getElementById('sfaoGwaBandChart');
            if (!qualificationCanvas || !bandCanvas) return;

            qualificationCanvas.parentElement.style.height = `${Math.max(520, rows.length * 58)}px`;

            if (state.qualification) state.qualification.destroy();
            if (state.bands) state.bands.destroy();

            state.qualification = new Chart(qualificationCanvas, {
                type: 'bar',
                data: {
                    labels: rows.map(row => row.scholarship_name),
                    datasets: [
                        {
                            label: 'Predicted qualified',
                            data: rows.map(row => row.qualified),
                            backgroundColor: '#10B981',
                            borderRadius: 8,
                            maxBarThickness: 26,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'Not qualified by GWA',
                            data: rows.map(row => row.not_qualified),
                            backgroundColor: '#EF4444',
                            borderRadius: 8,
                            maxBarThickness: 26,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { left: 8, right: 12, top: 8, bottom: 8 } },
                    scales: {
                        x: { stacked: true, beginAtZero: true, ticks: { color: textColor(), precision: 0 }, grid: { color: gridColor() } },
                        y: { stacked: true, ticks: { color: textColor(), autoSkip: false, font: { size: 11 }, padding: 14 }, grid: { display: false }, border: { display: false } }
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textColor() } },
                        tooltip: {
                            mode: 'nearest',
                            axis: 'y',
                            intersect: false,
                            position: 'nearest'
                        }
                    }
                }
            });

            state.bands = new Chart(bandCanvas, {
                type: 'doughnut',
                data: {
                    labels: bands.length ? bands.map(band => band.label) : ['No GWA'],
                    datasets: [{
                        data: bands.length ? bands.map(band => band.count) : [1],
                        backgroundColor: ['#7F1D1D', '#B91C1C', '#10B981', '#F59E0B', '#3B82F6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textColor() } }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => setTimeout(renderSfaoGwaPredictionCharts, 150));
        window.addEventListener('gwa-prediction-updated', (event) => {
            window.sfaoFilteredGwaPrediction = event.detail;
            setTimeout(renderSfaoGwaPredictionCharts, 50);
        });
        window.addEventListener('switch-tab', event => {
            if (!event.detail || String(event.detail).startsWith('analytics')) {
                setTimeout(renderSfaoGwaPredictionCharts, 150);
            }
        });
        window.addEventListener('tab-changed', () => setTimeout(renderSfaoGwaPredictionCharts, 150));
    })();
</script>
