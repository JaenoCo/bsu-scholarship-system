<div class="relative w-full max-w-2xl mx-auto" x-data="globalSearchComponent()" @click.outside="open = false">
    <label for="global-search" class="sr-only">Global search</label>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input
            id="global-search"
            type="search"
            x-model="query"
            @input.debounce.250ms="search()"
            @focus="open = query.trim().length >= minLength"
            @keydown.down.prevent="moveHighlight(1)"
            @keydown.up.prevent="moveHighlight(-1)"
            @keydown.enter.prevent="selectHighlighted()"
            @keydown.escape="open = false"
            autocomplete="off"
            placeholder="Search records, scholarships, applications..."
            class="block w-full rounded-full border border-gray-300 bg-white py-2.5 pl-10 pr-24 text-sm text-gray-700 shadow-sm focus:border-bsu-red focus:outline-none focus:ring-2 focus:ring-bsu-red/20"
        >
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-2">
            <button
                type="button"
                x-show="query"
                @click="clear()"
                class="rounded-full p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                title="Clear search"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <span x-show="loading" class="text-xs text-gray-400">Searching</span>
        </div>
    </div>

    <div
        x-show="open"
        x-cloak
        class="absolute left-0 right-0 z-50 mt-2 max-h-[70vh] overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"
    >
        <template x-if="query.trim().length < minLength">
            <div class="px-4 py-3 text-sm text-gray-500">Type at least 2 characters.</div>
        </template>

        <template x-if="error">
            <div class="px-4 py-3 text-sm text-red-600" x-text="error"></div>
        </template>

        <template x-if="!loading && !error && query.trim().length >= minLength && !hasResults()">
            <div class="px-4 py-3 text-sm text-gray-500">No matching records found.</div>
        </template>

        <template x-for="[group, items] in groupedResults()" :key="group">
            <div class="border-b border-gray-100 last:border-b-0">
                <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500" x-text="labelFor(group)"></div>
                <template x-for="item in items" :key="item.id">
                    <a
                        :href="item.url"
                        class="flex items-start justify-between gap-4 px-4 py-3 text-sm transition"
                        :class="highlightedId === item.id ? 'bg-bsu-light text-bsu-red' : 'text-gray-700 hover:bg-gray-50'"
                        @mouseenter="highlightedId = item.id"
                    >
                        <span class="min-w-0">
                            <span class="block truncate font-medium" x-text="item.text"></span>
                            <span x-show="item.detail" class="block truncate text-xs text-gray-500" x-text="item.detail"></span>
                        </span>
                        <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500" x-text="item.badge || labelFor(group)"></span>
                    </a>
                </template>
            </div>
        </template>
    </div>
</div>

<script>
    function globalSearchComponent() {
        return {
            query: '',
            loading: false,
            open: false,
            error: '',
            minLength: 2,
            highlightedId: null,
            abortController: null,
            results: {},
            labels: {
                students: 'Students',
                staff: 'Staff',
                scholarships: 'Scholarships',
                applications: 'Applications',
                scholars: 'Scholars',
                reports: 'Reports',
                forms: 'Forms',
                documents: 'Documents',
                notifications: 'Notifications',
                programs: 'Programs',
                colleges: 'Colleges',
                campuses: 'Campuses'
            },

            groupedResults() {
                return Object.entries(this.results || {}).filter(([, items]) => Array.isArray(items) && items.length > 0);
            },

            flatResults() {
                return this.groupedResults().flatMap(([, items]) => items);
            },

            hasResults() {
                return this.flatResults().length > 0;
            },

            labelFor(group) {
                return this.labels[group] || group.replace(/_/g, ' ');
            },

            clear() {
                this.query = '';
                this.results = {};
                this.error = '';
                this.open = false;
                this.highlightedId = null;
                if (this.abortController) this.abortController.abort();
            },

            search() {
                const term = (this.query || '').trim();
                this.error = '';

                if (term.length < this.minLength) {
                    this.results = {};
                    this.highlightedId = null;
                    this.open = term.length > 0;
                    if (this.abortController) this.abortController.abort();
                    return;
                }

                this.fetchResults(term);
            },

            moveHighlight(offset) {
                const items = this.flatResults();
                if (!items.length) return;

                const currentIndex = Math.max(0, items.findIndex((item) => item.id === this.highlightedId));
                const nextIndex = (currentIndex + offset + items.length) % items.length;
                this.highlightedId = items[nextIndex].id;
                this.open = true;
            },

            selectHighlighted() {
                const selected = this.flatResults().find((item) => item.id === this.highlightedId) || this.flatResults()[0];
                if (selected) window.location.href = selected.url;
            },

            async fetchResults(term) {
                if (this.abortController) {
                    this.abortController.abort();
                }

                this.loading = true;
                this.open = true;
                this.abortController = new AbortController();
                const controller = this.abortController;

                try {
                    const response = await fetch(`/search/suggest?term=${encodeURIComponent(term)}`, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        signal: controller.signal
                    });

                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }

                    if (!response.ok) {
                        throw new Error('Search is temporarily unavailable.');
                    }

                    const data = await response.json();
                    this.results = data || {};
                    const first = this.flatResults()[0];
                    this.highlightedId = first ? first.id : null;
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        this.error = error.message || 'Search is temporarily unavailable.';
                        this.results = {};
                    }
                } finally {
                    if (this.abortController === controller) {
                        this.loading = false;
                    }
                }
            }
        };
    }
</script>
