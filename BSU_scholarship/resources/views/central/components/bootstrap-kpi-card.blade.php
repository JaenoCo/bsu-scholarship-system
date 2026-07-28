@props([
    'title',
    'value',
    'trend' => '0%',
    'trendDirection' => 'up',
    'variant' => 'primary',
])

<div class="bsu-kpi-card h-100">
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div>
            <p class="bsu-kpi-label mb-2">{{ $title }}</p>
            <h3 class="bsu-kpi-value mb-2">{{ $value }}</h3>
            <span class="bsu-trend bsu-trend-{{ $trendDirection }}">
                {{ $trendDirection === 'down' ? 'Down' : 'Up' }} {{ $trend }}
            </span>
        </div>
        <div class="bsu-kpi-icon bsu-kpi-icon-{{ $variant }}">
            {!! $icon ?? '' !!}
        </div>
    </div>
</div>
