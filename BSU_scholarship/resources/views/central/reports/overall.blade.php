@extends('layouts.focused')

@section('title', 'Central Overall Visualization')
@section('navbar-title', 'Central Overall Visualization')
@section('back-url', route('central.dashboard', ['tabs' => 'sfao_reports']))
@section('back-text', 'Back to Reports')
@section('content-width', 'max-w-[95%] 2xl:max-w-full')

@section('content')
    <div class="mb-6 flex justify-end items-center gap-3 print:hidden">
        <button onclick="window.print()"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-bsu-red hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bsu-red">
            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print Report
        </button>
    </div>

    <div id="printable-area" class="bg-white text-black p-8 shadow-lg print:shadow-none print:p-0 w-full mx-auto min-h-[210mm] overflow-hidden mb-8">
        <div class="text-center mb-8 border-b-2 border-black pb-8">
            <div class="flex items-center justify-center mb-4">
                <img src="{{ asset('images/lugo.png') }}" alt="BSU Logo" class="h-24 w-24 object-contain">
            </div>
            <h2 class="text-xl font-bold uppercase tracking-wider">Batangas State University</h2>
            <h3 class="text-lg font-semibold text-bsu-red uppercase">The National Engineering University</h3>
            <h1 class="mt-6 text-2xl font-bold uppercase underline decoration-2 underline-offset-4">Central Overall Scholar Visualization</h1>
            <div class="mt-4 text-sm space-y-1">
                <p><span class="font-semibold">Campus:</span> All Campuses</p>
                <p><span class="font-semibold">Generated on:</span> {{ now()->format('F d, Y') }}</p>
                <p><span class="font-semibold">Prepared by:</span> {{ $user->name ?? 'Central Administration' }}</p>
            </div>
        </div>

        @include('sfao.reports.partials.student-summary-table', [
            'reportData' => $reportData,
            'studentType' => 'scholars',
            'dynamicTitle' => 'Central Overall Scholar Summary',
            'visualizationScope' => 'central',
        ])
    </div>

    <style>
        @media print {
            body {
                background: white;
                color: black;
                -webkit-print-color-adjust: exact;
            }

            .print\:hidden {
                display: none !important;
            }

            .print\:shadow-none {
                box-shadow: none !important;
            }

            .print\:p-0 {
                padding: 0 !important;
            }
        }
    </style>
@endsection
