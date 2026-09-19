@php
    $insightsDashboardConfig = [
        'endpoints' => [
            'scholarships' => route('sfao.analytics.scholarships'),
            'applicants' => route('sfao.analytics.applicants'),
            'scholars' => route('sfao.analytics.scholars'),
        ],
        'scholarships' => ($analytics['available_scholarships'] ?? collect())
            ->map(fn ($scholarship) => ['id' => $scholarship->id, 'name' => $scholarship->scholarship_name])
            ->values(),
        'colleges' => ($analytics['all_colleges'] ?? collect())
            ->map(fn ($college) => $college->short_name)
            ->filter()
            ->values(),
    ];
@endphp

<div x-show="(tab === 'analytics' || tab.startsWith('analytics_')) && tab !== 'analytics_gwa'"
     x-cloak
     x-data='sfaoInsightsDashboard(@json($insightsDashboardConfig))'
     @tab-changed.window="handleTab($event.detail)">
    <div class="space-y-5">
        <header class="analytics-hero rounded-xl p-6 shadow-sm">
            <p class="analytics-eyebrow">SFAO Analytics</p>
            <h1 class="analytics-title mt-2 text-2xl font-bold tracking-tight md:text-3xl" x-text="page.title">Scholarship Insights</h1>
            <p class="analytics-description mt-2 max-w-3xl text-sm leading-6" x-text="page.description"></p>
        </header>

        <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">Academic year
                    <select x-model="filters.academic_year" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <template x-for="year in academicYears" :key="year"><option :value="year" x-text="year === 'all' ? 'All academic years' : year"></option></template>
                    </select>
                </label>
                <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">Semester
                    <select x-model="filters.semester" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"><option value="all">All semesters</option><option value="first">First semester</option><option value="second">Second semester</option></select>
                </label>
                <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">College
                    <select x-model="filters.college" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"><option value="all">All colleges</option><template x-for="college in colleges" :key="college"><option :value="college" x-text="college"></option></template></select>
                </label>
                <label class="text-xs font-semibold text-slate-600 dark:text-gray-300">Scholarship program
                    <select x-model="filters.scholarship_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"><option value="all">All programs</option><template x-for="scholarship in scholarships" :key="scholarship.id"><option :value="scholarship.id" x-text="scholarship.name"></option></template></select>
                </label>
                <div class="flex items-end gap-2"><button type="button" @click="load()" :disabled="loading" class="rounded-lg bg-red-800 px-4 py-2 text-sm font-semibold text-white hover:bg-red-900 disabled:opacity-60">Apply filters</button><button type="button" @click="reset()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-600 dark:border-gray-600 dark:text-gray-300">Reset</button></div>
            </div>
        </section>

        <p x-show="error" x-text="error" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"></p>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6" :class="loading ? 'opacity-60' : ''">
            <template x-for="card in cards" :key="card.label">
                <article class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-800" :class="card.tone">
                    <p class="text-xs font-semibold uppercase tracking-wide" x-text="card.label"></p>
                    <p class="mt-2 text-2xl font-bold" x-text="card.value"></p>
                    <p class="mt-1 text-xs opacity-70" x-text="card.hint"></p>
                </article>
            </template>
        </section>

        <section class="grid gap-5 xl:grid-cols-3" :class="loading ? 'pointer-events-none opacity-60' : ''">
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-gray-700 dark:bg-gray-800"><h2 class="text-base font-bold text-slate-900 dark:text-white" x-text="chartOne.title"></h2><div class="relative mt-4 h-72"><canvas x-ref="primaryChart"></canvas><p x-show="!hasData" class="absolute inset-0 grid place-items-center text-sm text-slate-500">No data for this selection.</p></div></article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800"><h2 class="text-base font-bold text-slate-900 dark:text-white" x-text="chartTwo.title"></h2><div class="relative mt-4 h-72"><canvas x-ref="secondaryChart"></canvas><p x-show="!hasData" class="absolute inset-0 grid place-items-center text-sm text-slate-500">No data for this selection.</p></div></article>
        </section>

        <section class="grid gap-5 xl:grid-cols-3" :class="loading ? 'pointer-events-none opacity-60' : ''">
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 dark:border-gray-700 dark:bg-gray-800"><h2 class="text-base font-bold text-slate-900 dark:text-white" x-text="chartThree.title"></h2><div class="relative mt-4 h-72"><canvas x-ref="trendChart"></canvas></div></article>
            <aside class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800"><div class="flex items-center justify-between"><h2 class="text-base font-bold text-slate-900 dark:text-white">Key Insights</h2><span class="text-xs font-semibold text-red-800">LIVE</span></div><ul class="mt-4 space-y-3 text-sm leading-5 text-slate-600 dark:text-gray-300"><template x-for="insight in (data?.insights || [])" :key="insight"><li class="rounded-lg bg-slate-50 p-3 dark:bg-gray-700" x-text="insight"></li></template><li x-show="(data?.insights || []).length === 0" class="text-slate-500">Insights will appear when records match the selected filters.</li></ul></aside>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between gap-3"><h2 class="text-base font-bold text-slate-900 dark:text-white" x-text="tableTitle"></h2><span class="text-xs text-slate-500" x-text="`${tableRows.length} records`"></span></div>
            <div class="mt-4 overflow-x-auto"><table class="min-w-full text-sm"><thead class="border-b text-left text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-3 py-2">Program / group</th><th class="px-3 py-2 text-right">Total</th><th class="px-3 py-2 text-right" x-show="view !== 'scholars'">Approved</th><th class="px-3 py-2 text-right" x-show="view !== 'scholars'">Approval rate</th></tr></thead><tbody><template x-for="row in tableRows" :key="row.name"><tr class="border-b border-slate-100 dark:border-gray-700"><td class="px-3 py-3 font-medium text-slate-800 dark:text-gray-100" x-text="row.name"></td><td class="px-3 py-3 text-right" x-text="row.total"></td><td class="px-3 py-3 text-right" x-show="view !== 'scholars'" x-text="row.approved ?? 0"></td><td class="px-3 py-3 text-right" x-show="view !== 'scholars'" x-text="rate(row)"></td></tr></template><tr x-show="!tableRows.length"><td colspan="4" class="px-3 py-8 text-center text-slate-500">No records found.</td></tr></tbody></table></div>
        </section>
    </div>
</div>
