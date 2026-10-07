@props([
    'title',
    'value',
    'trend' => '0%',
    'trendLabel' => null,
    'trendDirection' => 'up',
    'variant' => 'primary',
    'showTrend' => true,
    'trendPeriod' => null,
])

<div class="bsu-kpi-card h-100">
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div>
            <p class="bsu-kpi-label mb-2">{{ $title }}</p>
            <h3 class="bsu-kpi-value mb-2">{{ $value }}</h3>
            @if($showTrend)
                <span class="bsu-trend bsu-trend-{{ $trendDirection }}" @if($trendPeriod) title="{{ $trendPeriod }}" @endif>
                    {{ $trendLabel ?? (($trendDirection === 'down' ? 'Down' : 'Up') . ' ' . $trend) }}
                </span>
            @else
                <span class="bsu-trend text-secondary" style="background: rgba(148, 163, 184, .12); color: #64748b;">
                    No data @if($trendPeriod)<span class="visually-hidden"> for {{ $trendPeriod }}</span>@endif
                </span>
            @endif
        </div>
        <div class="bsu-kpi-icon bsu-kpi-icon-{{ $variant }}">
            {!! $icon ?? '' !!}
        </div>
    </div>
</div>
