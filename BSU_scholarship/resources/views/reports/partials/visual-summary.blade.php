@php
    $visualScope = $visualizationScope ?? 'sfao';
    $recordLabel = $recordLabel ?? (($studentType ?? 'scholars') === 'applicants' ? 'applicants' : 'scholars');
    $rawCampuses = collect($reportData ?? []);

    $gradeBand = function ($gwa) {
        if (! is_numeric($gwa) || (float) $gwa <= 0) {
            return 'unknown';
        }

        $gwa = (float) $gwa;

        if ($gwa <= 1.75) {
            return 'high';
        }

        if ($gwa <= 2.50) {
            return 'medium';
        }

        return 'low';
    };

    $formatName = function ($record) {
        $first = data_get($record, 'first_name');
        $last = data_get($record, 'last_name');

        if ($first || $last) {
            return trim($first . ' ' . $last);
        }

        return data_get($record, 'name', 'Student');
    };

    $campusSummaries = $rawCampuses->map(function ($campusData) use ($gradeBand, $formatName) {
        $records = collect(data_get($campusData, 'students', data_get($campusData, 'scholars', [])));
        $total = $records->count();
        $bands = ['high' => 0, 'medium' => 0, 'low' => 0, 'unknown' => 0];
        $scholarships = [];
        $statuses = [];

        $rows = $records->take(5)->map(function ($record) use ($gradeBand, $formatName, &$bands, &$scholarships, &$statuses) {
            $gwa = data_get($record, 'gwa', data_get($record, 'current_gwa', data_get($record, 'previous_gwa')));
            $band = $gradeBand($gwa);
            $bands[$band]++;

            $scholarship = data_get($record, 'scholarship');
            if ($scholarship) {
                $scholarships[$scholarship] = ($scholarships[$scholarship] ?? 0) + 1;
            }

            $status = data_get($record, 'status_remarks', data_get($record, 'status'));
            if ($status) {
                $statuses[$status] = ($statuses[$status] ?? 0) + 1;
            }

            return [
                'name' => $formatName($record),
                'program' => data_get($record, 'course', data_get($record, 'program', 'N/A')),
                'gwa' => is_numeric($gwa) ? number_format((float) $gwa, 2) : 'N/A',
                'band' => $band,
            ];
        });

        $records->slice(5)->each(function ($record) use ($gradeBand, &$bands, &$scholarships, &$statuses) {
            $gwa = data_get($record, 'gwa', data_get($record, 'current_gwa', data_get($record, 'previous_gwa')));
            $bands[$gradeBand($gwa)]++;

            $scholarship = data_get($record, 'scholarship');
            if ($scholarship) {
                $scholarships[$scholarship] = ($scholarships[$scholarship] ?? 0) + 1;
            }

            $status = data_get($record, 'status_remarks', data_get($record, 'status'));
            if ($status) {
                $statuses[$status] = ($statuses[$status] ?? 0) + 1;
            }
        });

        arsort($scholarships);
        arsort($statuses);

        return [
            'campus' => data_get($campusData, 'campus.display_name', data_get($campusData, 'campus.name', data_get($campusData, 'campus_name', 'Campus'))),
            'total' => $total,
            'bands' => $bands,
            'rows' => $rows,
            'top_scholarship' => array_key_first($scholarships) ?: 'N/A',
            'top_status' => array_key_first($statuses) ?: 'N/A',
        ];
    })->values();

    $overall = [
        'total' => $campusSummaries->sum('total'),
        'bands' => [
            'high' => $campusSummaries->sum(fn ($campus) => $campus['bands']['high']),
            'medium' => $campusSummaries->sum(fn ($campus) => $campus['bands']['medium']),
            'low' => $campusSummaries->sum(fn ($campus) => $campus['bands']['low']),
            'unknown' => $campusSummaries->sum(fn ($campus) => $campus['bands']['unknown']),
        ],
    ];

    $percent = function ($count, $total) {
        return $total > 0 ? round(($count / $total) * 100, 1) : 0;
    };

    $bandLabels = [
        'high' => 'High standing',
        'medium' => 'Medium standing',
        'low' => 'Needs attention',
    ];
@endphp

<div class="report-visual-summary mb-8 break-inside-avoid">
    <div class="mb-4">
        <h3 class="text-xl font-bold text-gray-900 uppercase">
            {{ $visualScope === 'central' ? 'Central Office Dashboard' : 'Scholarship Personnel Dashboard' }}
        </h3>
        <p class="text-sm text-gray-600">
            {{ $visualScope === 'central' ? 'Overall view across included campuses.' : 'Campus-scoped view for this generated report.' }}
        </p>
    </div>

    @if($visualScope === 'central')
        <div class="mb-4 border border-gray-300 rounded-lg p-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Overall total {{ $recordLabel }}</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($overall['total']) }}</p>
                </div>
                <div class="flex-1">
                    <div class="h-3 flex rounded-full overflow-hidden bg-gray-100 border border-gray-200">
                        <div class="bg-green-500" style="width: {{ $percent($overall['bands']['high'], $overall['total']) }}%"></div>
                        <div class="bg-yellow-400" style="width: {{ $percent($overall['bands']['medium'], $overall['total']) }}%"></div>
                        <div class="bg-red-500" style="width: {{ $percent($overall['bands']['low'], $overall['total']) }}%"></div>
                    </div>
                    <div class="mt-2 grid grid-cols-3 gap-2 text-sm">
                        @foreach(['high', 'medium', 'low'] as $band)
                            <div class="flex justify-between gap-2">
                                <span>{{ $bandLabels[$band] }}</span>
                                <span class="font-semibold">{{ $percent($overall['bands'][$band], $overall['total']) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="{{ $visualScope === 'central' ? 'grid grid-cols-1 md:grid-cols-2 gap-4' : 'space-y-4' }}">
        @forelse($campusSummaries as $campus)
            <div class="border border-gray-300 rounded-lg p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 truncate">{{ $campus['campus'] }}</h4>
                        <p class="text-sm text-gray-600">Total {{ $recordLabel }}: {{ number_format($campus['total']) }}</p>
                    </div>
                    <div class="text-right text-xs text-gray-500">
                        <div>{{ $campus['top_scholarship'] }}</div>
                        <div>{{ $campus['top_status'] }}</div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="h-2.5 flex rounded-full overflow-hidden bg-gray-100 border border-gray-200">
                        <div class="bg-green-500" style="width: {{ $percent($campus['bands']['high'], $campus['total']) }}%"></div>
                        <div class="bg-yellow-400" style="width: {{ $percent($campus['bands']['medium'], $campus['total']) }}%"></div>
                        <div class="bg-red-500" style="width: {{ $percent($campus['bands']['low'], $campus['total']) }}%"></div>
                    </div>
                    <div class="mt-3 space-y-1 text-sm">
                        @foreach(['high', 'medium', 'low'] as $band)
                            <div class="flex justify-between gap-3">
                                <span>{{ $bandLabels[$band] }}</span>
                                <span class="font-semibold">{{ $percent($campus['bands'][$band], $campus['total']) }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($visualScope !== 'central' && $campus['rows']->isNotEmpty())
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="border-b border-gray-300 text-left text-gray-600">
                                    <th class="py-2 pr-3 font-semibold">Name</th>
                                    <th class="py-2 pr-3 font-semibold">Program</th>
                                    <th class="py-2 pr-3 font-semibold text-center">GWA</th>
                                    <th class="py-2 font-semibold">Standing</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($campus['rows'] as $row)
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2 pr-3 font-medium text-gray-900">{{ $row['name'] }}</td>
                                        <td class="py-2 pr-3 text-gray-700">{{ $row['program'] }}</td>
                                        <td class="py-2 pr-3 text-center">{{ $row['gwa'] }}</td>
                                        <td class="py-2">
                                            <span class="inline-flex h-2 w-20 rounded-full overflow-hidden bg-gray-100 align-middle">
                                                <span class="{{ $row['band'] === 'high' ? 'bg-green-500 w-full' : ($row['band'] === 'medium' ? 'bg-yellow-400 w-2/3' : ($row['band'] === 'low' ? 'bg-red-500 w-1/3' : 'bg-gray-300 w-1/4')) }}"></span>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @empty
            <div class="border border-gray-300 rounded-lg p-6 text-center text-gray-500">
                No visualization data available for this report.
            </div>
        @endforelse
    </div>
</div>
