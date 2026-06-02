@props(['paginator'])

@if($paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'mt-8']) }}>
        {{ $paginator->links('vendor.pagination.custom') }}
    </div>
@endif
