@extends('layouts.dashboard')

@section('navbar')
    <x-layout.navbar :title="'Search Results'" :user="$user" :sidebar="true" />
@endsection

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Search Results</h1>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Showing results for <span class="font-medium text-gray-900 dark:text-white">"{{ $term }}"</span>.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('search.index', ['term' => $term, 'group' => 'all']) }}" class="rounded-full border px-3 py-2 text-sm font-semibold {{ $group === 'all' ? 'bg-bsu-red text-white border-bsu-red' : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700' }}">All</a>
                    @foreach($groups as $groupName)
                        <a href="{{ route('search.index', ['term' => $term, 'group' => $groupName]) }}" class="rounded-full border px-3 py-2 text-sm font-semibold {{ $group === $groupName ? 'bg-bsu-red text-white border-bsu-red' : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700' }}">{{ ucwords(str_replace('_', ' ', $groupName)) }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        @if($term === '' || mb_strlen($term) < 2)
            <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <p class="text-gray-500 dark:text-gray-400">Enter at least 2 characters to search records.</p>
            </div>
        @elseif(empty($results) || collect($results)->flatMap(fn($items) => $items)->isEmpty())
            <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                <p class="text-gray-500 dark:text-gray-400">No matching records found for "{{ $term }}".</p>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach($filteredResults as $groupName => $items)
                    @if(! empty($items))
                        <div class="rounded-3xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ ucwords(str_replace('_', ' ', $groupName)) }}</h2>
                            <div class="space-y-3">
                                @foreach($items as $item)
                                    <a href="{{ $item['url'] }}" class="block rounded-2xl border border-gray-200 dark:border-gray-700 p-4 transition hover:border-bsu-red hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $item['text'] }}</div>
                                                @if($item['detail'])
                                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate">{{ $item['detail'] }}</div>
                                                @endif
                                            </div>
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $item['badge'] }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endsection
