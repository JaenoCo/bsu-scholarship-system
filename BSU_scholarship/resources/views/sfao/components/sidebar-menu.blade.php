@props(['user', 'sfaoCampus'])

@php
    $dashboardUrl = route('sfao.dashboard', ['tab' => 'dashboard']);
    $applicationFormsUrl = route('sfao.application-forms.index', ['tabs' => 'all-app-forms']);
    $settingsUrl = route('sfao.settings', ['tabs' => 'account-info']);
@endphp

<div class="sidebar-profile p-6">
    <div class="flex flex-col items-center">
        <img src="{{ $user && $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) . '?' . now()->timestamp : asset('images/default-avatar.png') }}"
             alt="Profile Picture"
             class="h-20 w-20 rounded-full border-4 border-gray-600 object-cover mb-3">
        <h3 class="sidebar-label text-lg font-bold text-white text-center">{{ $user?->name ?? 'SFAO User' }}</h3>
        <p class="sidebar-label text-sm text-gray-300 font-medium">SFAO Staff</p>
    </div>
</div>

<nav class="mt-2 px-4 pb-4 overflow-y-auto flex-1 space-y-5 custom-scrollbar"
     aria-label="SFAO navigation"
     x-data="{
        activeTab: 'dashboard',
        openMenu: null,
        init() {
            const params = new URLSearchParams(window.location.search);
            const requestedTab = params.get('tabs') || params.get('tab') || (window.location.pathname.endsWith('/sfao') ? null : localStorage.getItem('sfaoTab'));
            this.activeTab = requestedTab ? this.normalizeTab(requestedTab) : 'dashboard';
            this.openMenu = this.sectionForTab(this.activeTab);
        },
        normalizeTab(tab) {
            const map = {
                overview: 'analytics_scholarships',
                analytics: 'analytics_scholarships',
                all_scholarships: 'scholarships',
                private_scholarships: 'scholarships-private',
                government_scholarships: 'scholarships-government',
                all_applicants: 'applicants',
                applicants_in_progress: 'applicants-in_progress',
                applicants_pending: 'applicants-pending',
                applicants_approved: 'applicants-approved',
                all_scholars: 'scholars',
                new_scholars: 'scholars-new',
                old_scholars: 'scholars-old',
                reports_student_summary: 'reports',
                reports_grant_summary: 'reports',
                account_settings: 'account-info'
            };
            return map[tab] || tab || 'dashboard';
        },
        sectionForTab(tab) {
            const normalized = this.normalizeTab(tab);
            if (normalized.startsWith('scholarships') || normalized.startsWith('applicants') || normalized.startsWith('scholars')) return 'management';
            if (normalized.startsWith('analytics')) return 'analytics';
            if (normalized === 'all-app-forms' || normalized === 'up-app-form' || normalized === 'import-scholarships') return 'forms';
            if (normalized.startsWith('reports')) return 'reports';
            return null;
        },
        isActive(tab) {
            return this.normalizeTab(tab) === this.activeTab;
        },
        navigate(tab) {
            this.activeTab = this.normalizeTab(tab);
            this.openMenu = this.sectionForTab(this.activeTab);
            localStorage.setItem('sfaoTab', this.activeTab);
            this.$dispatch('switch-tab', tab);
        },
        itemClass(tab) {
            return this.isActive(tab) ? 'sidebar-tab-active' : '';
        }
     }"
     x-on:switch-tab.window="activeTab = normalizeTab($event.detail); openMenu = sectionForTab(activeTab)">

    <a href="{{ $dashboardUrl }}"
       class="sidebar-nav-item"
       :class="itemClass('dashboard')"
       :aria-current="isActive('dashboard') ? 'page' : null"
       title="Dashboard">
        <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h14V10M9 20v-6h6v6" /></svg>
        <span class="sidebar-label">Dashboard</span>
    </a>

    <section class="sidebar-section" aria-labelledby="sidebar-management-heading">
        <button type="button" id="sidebar-management-heading" class="sidebar-section-toggle" @click="openMenu = openMenu === 'management' ? null : 'management'" :aria-expanded="openMenu === 'management'">
            <span class="sidebar-section-title sidebar-label">Scholarship Management</span>
            <svg class="sidebar-chevron sidebar-label" :class="openMenu === 'management' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </button>
        <div x-show="openMenu === 'management'" x-cloak class="sidebar-submenu">
            <button type="button" class="sidebar-nav-item" :class="itemClass('scholarships')" @click="navigate('scholarships')" title="Scholarship Programs">
                <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3L3 8l9 5 9-5-9-5zM5 10v5c0 2 3 4 7 4s7-2 7-4v-5" /></svg>
                <span class="sidebar-label">Scholarship Programs</span>
            </button>
            <button type="button" class="sidebar-nav-item" :class="itemClass('applicants')" @click="navigate('applicants')" title="Application Review">
                <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6M7 3h10a2 2 0 012 2v16H5V5a2 2 0 012-2zm2 7l2 2 4-4" /></svg>
                <span class="sidebar-label">Application Review</span>
            </button>
            <button type="button" class="sidebar-nav-item" :class="itemClass('scholars')" @click="navigate('scholars')" title="Scholar Management">
                <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zm13 10v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" /></svg>
                <span class="sidebar-label">Scholar Management</span>
            </button>
        </div>
    </section>

    <section class="sidebar-section" aria-labelledby="sidebar-analytics-heading">
        <button type="button" id="sidebar-analytics-heading" class="sidebar-section-toggle" @click="openMenu = openMenu === 'analytics' ? null : 'analytics'" :aria-expanded="openMenu === 'analytics'">
            <span class="sidebar-section-title sidebar-label">Analytics &amp; Insights</span>
            <svg class="sidebar-chevron sidebar-label" :class="openMenu === 'analytics' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </button>
        <div x-show="openMenu === 'analytics'" x-cloak class="sidebar-submenu">
            <button type="button" class="sidebar-nav-item" :class="itemClass('analytics_scholarships')" @click="navigate('analytics_scholarships')" title="Scholarship Insights">
                <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m0 14h16M8 16v-4m4 4V8m4 8V4" /></svg>
                <span class="sidebar-label">Scholarship Insights</span>
            </button>
            <button type="button" class="sidebar-nav-item" :class="itemClass('analytics_applications')" @click="navigate('analytics_applications')" title="Applicant Insights">
                <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8m8-1v6m-3-3h6" /></svg>
                <span class="sidebar-label">Applicant Insights</span>
            </button>
            <button type="button" class="sidebar-nav-item" :class="itemClass('analytics_scholars')" @click="navigate('analytics_scholars')" title="Scholar Insights">
                <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h4l3-8 4 16 3-8h4" /></svg>
                <span class="sidebar-label">Scholar Insights</span>
            </button>
            <button type="button" class="sidebar-nav-item" :class="itemClass('analytics_gwa')" @click="navigate('analytics_gwa')" title="GWA Prediction">
                <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 17l6-6 4 4 6-8M20 7h-5m5 0v5" /></svg>
                <span class="sidebar-label">GWA Prediction</span>
            </button>
        </div>
    </section>

    <section class="sidebar-section" aria-labelledby="sidebar-forms-heading">
        <button type="button" id="sidebar-forms-heading" class="sidebar-section-toggle" @click="openMenu = openMenu === 'forms' ? null : 'forms'" :aria-expanded="openMenu === 'forms'">
            <span class="sidebar-section-title sidebar-label">Tools</span>
            <svg class="sidebar-chevron sidebar-label" :class="openMenu === 'forms' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </button>
        <div x-show="openMenu === 'forms'" x-cloak class="sidebar-submenu">
            <a href="{{ $applicationFormsUrl }}" class="sidebar-nav-item" :class="itemClass('all-app-forms')" title="Application Forms">
                <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.59L19 7.41V19a2 2 0 01-2 2z" /></svg>
                <span class="sidebar-label">Application Forms</span>
            </a>
            <button type="button" class="sidebar-nav-item" :class="itemClass('up-app-form')" @click="navigate('up-app-form')" title="Upload Application Form">
                <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 0L8 8m4-4l4 4M5 20h14" /></svg>
                <span class="sidebar-label">Upload Application Form</span>
            </button>
        </div>
    </section>

    <section class="sidebar-section" aria-labelledby="sidebar-reports-heading">
        <button type="button" id="sidebar-reports-heading" class="sidebar-section-toggle" @click="openMenu = openMenu === 'reports' ? null : 'reports'" :aria-expanded="openMenu === 'reports'">
            <span class="sidebar-section-title sidebar-label">Reports</span>
            <svg class="sidebar-chevron sidebar-label" :class="openMenu === 'reports' ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
        </button>
        <div x-show="openMenu === 'reports'" x-cloak class="sidebar-submenu">
        <a href="{{ route('sfao.reports.student-summary', ['student_type' => 'applicants', 'campus_id' => 'all']) }}" class="sidebar-nav-item sidebar-nav-item-sub" title="Generate Student Summary">
            <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012 2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            <span class="sidebar-label">Student Summary</span>
        </a>
        <a href="{{ route('sfao.reports.grant-summary', ['campus_id' => 'all']) }}" class="sidebar-nav-item sidebar-nav-item-sub" title="Generate Grant Summary">
            <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="sidebar-label">Grant Summary</span>
        </a>
        </div>
    </section>

    <section class="sidebar-section" aria-labelledby="sidebar-system-heading">
        <div id="sidebar-system-heading" class="sidebar-section-heading sidebar-label">System</div>
        <a href="{{ $settingsUrl }}" class="sidebar-nav-item" :class="window.location.pathname.includes('/sfao/settings') ? 'sidebar-tab-active' : ''" title="Settings">
            <svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7zm7.94-2.5a7.98 7.98 0 000-2l2.06-1.6-2-3.46-2.48 1a8.1 8.1 0 00-1.73-1L13.5 3h-4l-.3 2.94a8.1 8.1 0 00-1.73 1l-2.48-1-2 3.46L5.06 11a7.98 7.98 0 000 2L3 14.6l2 3.46 2.48-1a8.1 8.1 0 001.73 1L9.5 21h4l.3-2.94a8.1 8.1 0 001.73-1l2.48 1 2-3.46L19.94 13z" /></svg>
            <span class="sidebar-label">Settings</span>
        </a>
    </section>
</nav>
