/**
 * Central Dashboard Logic
 */

window.centralDashboard = function (config = {}) {
    return {
        tab: config.initialTab || new URLSearchParams(window.location.search).get('tabs') || new URLSearchParams(window.location.search).get('tab') || localStorage.getItem('activeTab') || 'all_scholarships',
        currentStatsCampus: 'all',
        showLogoutModal: false,
        rightSidebarOpen: false,
        initialParams: new URLSearchParams(window.location.search),

        getTabGroup(tab) {
            if (tab === 'all_scholarships' || tab === 'private_scholarships' || tab === 'government_scholarships') return 'scholarships';
            if (tab === 'sfao-reports' || tab === 'sfao_reports') return 'reports';
            if (tab === 'all_statistics' || (tab.endsWith('_statistics') && tab !== 'all_statistics')) return 'statistics';
            if (tab === 'all_scholars' || tab === 'new_scholars' || tab === 'old_scholars') return 'scholars';
            if (tab === 'endorsed_applicants' || tab === 'rejected_applicants') return 'applications';
            if (tab === 'staff') return 'staff';
            return 'default';
        },

        normalizeTab(t) {
            const map = {
                'scholarships': 'all_scholarships',
                'scholarships-private': 'private_scholarships',
                'scholarships-government': 'government_scholarships',
                'scholars': 'all_scholars',
                'scholars-new': 'new_scholars',
                'scholars-old': 'old_scholars',
                'endorsed-applicants': 'endorsed_applicants',
                'rejected-applicants': 'rejected_applicants',
                'reports': 'sfao-reports',
                'sfao_reports': 'sfao-reports', // Normalized to dash
                'statistics': 'all_statistics',
                'settings': 'account_settings'
            };
            return map[t] || t;
        },

        getGroupKeys(group) {
            const keys = {
                'scholarships': ['sort_by', 'sort_order', 'page_all', 'page_private', 'page_gov'],
                'reports': ['type', 'campus', 'sort', 'order', 'page_submitted', 'page_reviewed', 'page_approved', 'page_rejected', 'status'],
                'statistics': ['timePeriod', 'campus'],
                'scholars': ['page_scholars_all', 'page_scholars_new', 'page_scholars_old', 'sort_by', 'status', 'type'],
                'applications': ['sort_by', 'status_filter', 'campus_filter', 'scholarship_filter']
            };
            return keys[group] || [];
        },

        switchTab(newTab) {
            // 1. Identify Groups
            const currentGroup = this.getTabGroup(this.tab);
            const newGroup = this.getTabGroup(newTab);

            // 2. Save Current Group State
            if (currentGroup !== 'default') {
                const currentParams = new URLSearchParams(window.location.search);
                const groupKeys = this.getGroupKeys(currentGroup);
                const stateToSave = new URLSearchParams();

                groupKeys.forEach(key => {
                    if (currentParams.has(key)) {
                        stateToSave.set(key, currentParams.get(key));
                    }
                });
                localStorage.setItem('groupState_' + currentGroup, stateToSave.toString());
            }

            // 3. Restore New Group State
            let newParams = new URLSearchParams();
            if (newGroup !== 'default') {
                const savedState = localStorage.getItem('groupState_' + newGroup);
                if (savedState) {
                    newParams = new URLSearchParams(savedState);
                }
            }

            // Always set the new tab
            newParams.set('tabs', newTab); // Use 'tabs'
            newParams.delete('tab'); // Clean legacy

            // Special Case: Remove 'campus' param for campus-specific statistics tabs
            if (newTab.endsWith('_statistics') && newTab !== 'all_statistics') {
                newParams.delete('campus');
            }

            // CLEANUP: Remove default values to keep URL clean
            const defaults = {
                'campus': 'all',
                'campus_filter': 'all',
                'timePeriod': 'all',
                'status_filter': 'all',
                'scholarship_filter': 'all',
                'sort_by': 'created_at',
                'sort_order': 'desc',
                'type': 'all',
                'status': 'all'
            };

            // Remove pagination parameters if they are page 1
            Array.from(newParams.keys()).forEach(key => {
                if (key.startsWith('page_') && newParams.get(key) === '1') {
                    newParams.delete(key);
                }
            });

            // Remove standard defaults
            for (const [key, def] of Object.entries(defaults)) {
                if (newParams.get(key) === def) {
                    newParams.delete(key);
                }
            }

            // 4. Construct New URL
            const newUrl = new URL(window.location);
            newUrl.search = newParams.toString();

            // 5. Smart Switch Logic
            const initialParamsCopy = new URLSearchParams(this.initialParams);
            initialParamsCopy.delete('tabs');
            initialParamsCopy.delete('tab');

            const checkParams = new URLSearchParams(newParams);
            checkParams.delete('tabs');
            checkParams.delete('tab');

            if (checkParams.toString() === initialParamsCopy.toString() || newGroup === 'statistics') {
                this.tab = newTab;
                window.history.pushState({}, '', newUrl);
            } else {
                window.location.href = newUrl.toString();
            }
        },

        init() {
            this.$watch('tab', val => localStorage.setItem('activeTab', val));

            // Ensure initialParams is set correctly
            this.initialParams = new URLSearchParams(window.location.search);

            // Normalize tab for backward compatibility
            this.tab = this.normalizeTab(this.tab);

            // Global event listeners
            window.addEventListener('change-stats-campus', (event) => {
                this.currentStatsCampus = event.detail;
            });

            window.addEventListener('switch-tab', (event) => {
                this.switchTab(event.detail);
            });

            // Handle Profile Sidebar close on escape
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.rightSidebarOpen = false;
            });
        }
    }
};

// Fix Safari Back Cache Bug
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
