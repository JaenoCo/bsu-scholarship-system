@extends('layouts.focused')
@section('page-title', 'Grant Summary Report')
@section('navbar-title', 'Grant Summary Report | All Campuses')
@section('back-url', route('sfao.dashboard'))
@section('back-text', 'Back to Dashboard')

@section('content')

    <!-- Report Actions -->
    <div class="mb-6 flex justify-end items-center no-print">
        <button onclick="window.print()" class="bg-bsu-red text-white px-4 py-2 rounded-md hover:bg-bsu-redDark flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print Report
        </button>
    </div>

    <!-- Report Content -->
    @include('sfao.components.modals.success-report-submission')

    <div class="bg-white shadow-lg rounded-lg overflow-hidden print:shadow-none">
        <!-- Report Header -->
        <div class="px-8 py-6 border-b border-gray-200 text-center">
            <img src="{{ asset('images/lugo.png') }}" alt="BSU Logo" class="h-20 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-900 uppercase">Batangas State University</h1>
            <h2 class="text-xl font-semibold text-gray-700 mt-2">The National Engineering University</h2>
            <h3 class="text-lg font-medium text-gray-600 mt-4 uppercase tracking-wide">Grant Summary Report</h3>
            <!-- Dynamic Campus Subtitle -->
            <div class="mt-2 inline-block px-4 py-1 rounded-full bg-red-50 text-bsu-red font-bold text-sm uppercase tracking-wider border border-red-100">
                Campus: {{ request('campus_id') == 'all' ? 'All Campuses' : $monitoredCampuses->where('id', request('campus_id'))->first()->name ?? 'Unknown Campus' }}
            </div>

            <p class="text-sm text-gray-500 mt-4">Generated on {{ now()->format('F d, Y') }}</p>
            <p class="text-sm text-gray-500">Prepared by: {{ $user->name }}</p>
        </div>

        <div class="p-8">

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-100 flex items-start justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-blue-600 uppercase tracking-wider">Total Grants</h4>
                        <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($totalGrants) }}</p>
                        <p class="text-xs text-blue-500 mt-1">Approved & Claimed</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="bg-green-50 p-6 rounded-lg border border-green-100 flex items-start justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-green-600 uppercase tracking-wider">Claimed Grants</h4>
                        <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($statusStats['claimed'] ?? 0) }}</p>
                        <p class="text-xs text-green-500 mt-1">
                            @if($totalGrants > 0)
                                {{ round(($statusStats['claimed'] ?? 0) / $totalGrants * 100, 1) }}% Claim Rate
                            @else
                                0% Claim Rate
                            @endif
                        </p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full text-green-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="bg-yellow-50 p-6 rounded-lg border border-yellow-100 flex items-start justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-yellow-600 uppercase tracking-wider">Unclaimed Grants</h4>
                        <p class="text-3xl font-bold text-yellow-900 mt-2">{{ number_format($statusStats['approved'] ?? 0) }}</p>
                        <p class="text-xs text-yellow-500 mt-1">Approved but not claimed</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Detailed Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Claim Status Breakdown -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Claim Status Breakdown</h4>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($statusStats as $status => $count)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 capitalize flex items-center">
                                        <span class="w-2 h-2 rounded-full mr-2 
                                            {{ $status == 'claimed' ? 'bg-green-500' : 'bg-yellow-500' }}">
                                        </span>
                                        {{ $status == 'approved' ? 'Unclaimed (Approved)' : $status }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right font-semibold">{{ number_format($count) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Grants by Type -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Grants by Scholarship Type</h4>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($typeStats as $type => $count)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 capitalize">{{ $type }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right font-semibold">{{ number_format($count) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Submit Report Form -->
            <div class="mt-12 bg-gray-50 border border-gray-200 rounded-lg p-6 w-full no-print text-left">
                <h4 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4">Submit Report to Central Office</h4>
                <form action="{{ route('sfao.reports.summary-submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="report_type" value="grant_summary">
                    <input type="hidden" name="campus_id" value="{{ request('campus_id', 'all') }}">

                    <div>
                        <label for="report_frequency" class="block text-sm font-medium text-gray-700">Report Frequency</label>
                        <select id="report_frequency" name="frequency" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-bsu-red focus:border-bsu-red sm:text-sm rounded-md">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="semi-annual">Semi-Annual</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>

                    <div>
                        <label for="report_description" class="block text-sm font-medium text-gray-700">Additional Notes / Description</label>
                        <textarea id="report_description" name="description" rows="3" class="shadow-sm focus:ring-bsu-red focus:border-bsu-red mt-1 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Enter any additional notes for the central office..."></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-bsu-red hover:bg-bsu-redDark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red w-full sm:w-auto">
                            Submit Grant Summary to Central
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-8 border-t border-gray-200 text-center text-sm text-gray-500 mb-8">
                <p>This report is system-generated and serves as an official summary of scholarship grants.</p>
                <p class="mt-1">Prepared by: {{ $user->name }} (SFAO Admin)</p>
            </div>
        </div>


<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white;
        }
        .shadow-lg {
            box-shadow: none !important;
        }
        .max-w-7xl {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
    }
</style>
@endsection
