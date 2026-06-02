@php
    // Precompute conditions and documents BEFORE head scripts
    $user = \App\Models\User::find(session('user_id'));
    $conditionsData = old('conditions', isset($scholarship)
        ? $scholarship->conditions->map(function($c){
            return ['type' => $c->name, 'value' => $c->value];
        })->toArray()
        : []
    );

    $documentsData = old('documents', isset($scholarship)
        ? $scholarship->requiredDocuments->map(function($r){
            return ['name' => $r->document_name, 'type' => $r->document_type, 'mandatory' => $r->is_mandatory];
        })->toArray()
        : []
    );

@endphp

@extends('layouts.focused')

@section('navbar-title', isset($scholarship) ? 'Edit Scholarship' : 'Create Scholarship')
@section('back-url', route('central.dashboard'))
@section('back-text', 'Back to Dashboard')

@section('head-scripts')
    <!-- Data for Alpine.js (JSON blocks to avoid JS lint issues) -->
    <script id="conditions-data" type="application/json">
{!! json_encode($conditionsData, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) !!}
    </script>
    <script id="documents-data" type="application/json">
{!! json_encode($documentsData, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) !!}
    </script>
@endsection

@section('content')
{{-- Page Header - Upper Left Corner --}}
<div class="pt-4 pb-6 pl-4 pr-8">
    <div class="flex items-center gap-4">
        <div class="p-3 bg-gradient-to-br from-bsu-red to-red-700 rounded-2xl shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                {{ isset($scholarship) ? 'Edit Scholarship' : 'Create New Scholarship' }}
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ isset($scholarship) ? 'Update scholarship details and requirements' : 'Set up a new scholarship program for students' }}
            </p>
        </div>
    </div>
</div>

{{-- Form Container --}}
<div class="pl-4 pr-8 pb-12">
    <form method="POST" enctype="multipart/form-data"
          action="{{ isset($scholarship) 
                    ? route('central.scholarships.update', $scholarship->id) 
                    : route('central.scholarships.store') }}">
        @csrf
        @if(isset($scholarship))
            @method('PUT')
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-8 rounded-2xl bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 p-6 shadow-lg max-w-[1800px]">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-7 w-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base font-bold text-red-800 dark:text-red-200">Please fix the following errors:</h3>
                        <div class="mt-3 text-sm text-red-700 dark:text-red-300">
                            <ul class="list-disc pl-5 space-y-1.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 max-w-[1800px]">
            
            {{-- Left Column: Main Info --}}
            <div class="xl:col-span-8 space-y-6">
                
                {{-- Scheme Details Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="px-6 py-4 bg-gradient-to-r from-bsu-red to-red-700 flex items-center gap-3">
                        <div class="p-2 bg-white/20 backdrop-blur-sm rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-white">Scholarship Details</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Name --}}
                        <div>
                            <label for="scholarship_name" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Scholarship Name *</label>
                            <input type="text" id="scholarship_name" name="scholarship_name"
                                   value="{{ old('scholarship_name', $scholarship->scholarship_name ?? '') }}" required
                                   placeholder="e.g. Academic Excellence Scholarship"
                                   class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-bsu-red dark:focus:border-red-500 focus:ring-2 focus:ring-bsu-red/20 dark:focus:ring-red-500/20 transition-all duration-200 px-4 py-3">
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Description *</label>
                            <textarea id="description" name="description" required rows="4"
                                      placeholder="Describe the scholarship scheme, its purpose, and target beneficiaries..."
                                      class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-bsu-red dark:focus:border-red-500 focus:ring-2 focus:ring-bsu-red/20 dark:focus:ring-red-500/20 transition-all duration-200 px-4 py-3">{{ old('description', $scholarship->description ?? '') }}</textarea>
                        </div>

                        {{-- Type Selection --}}
                        <div x-data="{ type: '{{ old('scholarship_type', $scholarship->scholarship_type ?? 'private') }}' }">
                            <label class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-3">Scholarship Type *</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="relative flex cursor-pointer rounded-xl border-2 p-5 shadow-md hover:shadow-lg focus:outline-none transition-all duration-200"
                                       :class="type === 'private' ? 'border-green-600 ring-2 ring-green-500/30 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 bg-white dark:bg-gray-700'">
                                    <input type="radio" name="scholarship_type" value="private" class="sr-only" x-model="type">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-bold text-gray-900 dark:text-gray-100">Private</span>
                                            <span class="mt-1 flex items-center text-sm text-gray-600 dark:text-gray-400">Funded by private organizations or individuals.</span>
                                        </span>
                                    </span>
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-500" :class="type === 'private' ? '' : 'invisible'" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </label>

                                <label class="relative flex cursor-pointer rounded-xl border-2 p-5 shadow-md hover:shadow-lg focus:outline-none transition-all duration-200"
                                       :class="type === 'government' ? 'border-green-600 ring-2 ring-green-500/30 bg-green-50 dark:bg-green-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 bg-white dark:bg-gray-700'">
                                    <input type="radio" name="scholarship_type" value="government" class="sr-only" x-model="type">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-bold text-gray-900 dark:text-gray-100">Government</span>
                                            <span class="mt-1 flex items-center text-sm text-gray-600 dark:text-gray-400">Funded by government agencies.</span>
                                        </span>
                                    </span>
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-500" :class="type === 'government' ? '' : 'invisible'" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </label>
                            </div>
                        </div>

                        {{-- Campus Selection --}}
                        <div x-data="{ 
                            selectedCampuses: {{ json_encode(old('campuses', isset($scholarship) ? $scholarship->campuses->pluck('id')->toArray() : [])) }},
                            allSelected: false,
                            toggleAll() {
                                if (this.allSelected) {
                                    this.selectedCampuses = [];
                                } else {
                                    this.selectedCampuses = [{{ $campuses->pluck('id')->implode(',') }}];
                                }
                                this.allSelected = !this.allSelected;
                            },
                            updateAllState() {
                                this.allSelected = this.selectedCampuses.length === {{ $campuses->count() }};
                            }
                        }" x-init="updateAllState()">
                            <label class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-3">Available For Campus *</label>
                            
                            <div class="mb-3">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" @click="toggleAll()" x-model="allSelected"
                                           class="rounded border-gray-300 text-bsu-red shadow-sm focus:border-bsu-red focus:ring focus:ring-bsu-red focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 font-semibold">Select All Campuses</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto custom-scrollbar p-1">
                                @foreach($campuses as $campus)
                                    <label class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                        <input type="checkbox" name="campuses[]" value="{{ $campus->id }}" 
                                               x-model="selectedCampuses" @change="updateAllState()"
                                               class="rounded border-gray-300 text-bsu-red shadow-sm focus:border-bsu-red focus:ring focus:ring-bsu-red focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $campus->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Select the campuses where this scholarship will be available. Select all for university-wide availability.</p>
                        </div>
                    </div>
                </div>

                {{-- Conditions & Requirements --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 flex items-center gap-3">
                        <div class="p-2 bg-white/20 backdrop-blur-sm rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-white">Requirements & Conditions</h2>
                    </div>
                    
                    <div class="px-6 pb-6 pt-2">
                        {{-- Side by Side: Eligibility Conditions | Required Documents --}}
                        <div x-data="{ 
                            pairs: (function(){
                                try {
                                    const condEl = document.getElementById('conditions-data');
                                    const docEl = document.getElementById('documents-data');
                                    const conditions = JSON.parse((condEl && condEl.textContent) || '[]');
                                    const documents = JSON.parse((docEl && docEl.textContent) || '[]');
                                    
                                    // Merge into pairs
                                    const maxLength = Math.max(conditions.length, documents.length);
                                    const result = [];
                                    for(let i = 0; i < maxLength; i++) {
                                        result.push({
                                            condition: conditions[i] || {type:'gwa', value:''},
                                            document: documents[i] || {name:'', type:'pdf', mandatory:1}
                                        });
                                    }
                                    return result.length > 0 ? result : [];
                                } catch(e) { return []; }
                            })()
                        }">
                            {{-- Headers --}}
                            <div class="grid grid-cols-2 gap-6 mb-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Eligibility Conditions</h3>
                                </div>
                                <div class="flex justify-between items-center">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">Required Documents</h3>
                                </div>
                            </div>

                            <button type="button" @click="pairs.push({condition:{type:'gwa',value:''}, document:{name:'', type:'pdf', mandatory:1}})"
                                    class="mb-4 text-sm text-green-600 dark:text-green-500 font-semibold hover:text-green-700 dark:hover:text-green-400 flex items-center gap-2 px-4 py-2 border-2 border-green-600 dark:border-green-500 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add Condition + Document
                            </button>

                            {{-- Empty State --}}
                            <div x-show="pairs.length === 0" class="text-center py-8 bg-gray-50 dark:bg-gray-700/50 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 mb-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No eligibility conditions set. Click "Add Condition + Document" to create one.</p>
                            </div>

                            {{-- Side by Side Rows --}}
                            <div class="space-y-4">
                                <template x-for="(pair, index) in pairs" :key="'pair-'+index">
                                    <div class="grid grid-cols-2 gap-6 p-5 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border-2 border-gray-200 dark:border-gray-600 relative group hover:shadow-md transition-all">
                                        
                                        {{-- Left: Eligibility Condition --}}
                                        <div class="space-y-3">
                                            <label class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase">Condition Type</label>
                                            <select x-model="pair.condition.type" :name="'conditions['+index+'][type]'" 
                                                    class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-4 py-3 font-medium">
                                                <option value="gwa">Minimum GWA</option>
                                                <option value="year_level">Year Level</option>
                                                <option value="income">Maximum Income</option>
                                                <option value="disability">Disability</option>
                                                <option value="college">College</option>
                                                <option value="others">Others (Custom)</option>
                                            </select>

                                            <div>
                                                <label class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase">Value</label>
                                                {{-- GWA --}}
                                                <template x-if="pair.condition.type === 'gwa'">
                                                    <input type="number" step="0.01" x-model="pair.condition.value" :name="'conditions['+index+'][value]'" placeholder="e.g. 2.50" class="mt-1 w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-4 py-3">
                                                </template>
                                                {{-- Year Level --}}
                                                <template x-if="pair.condition.type === 'year_level'">
                                                    <select x-model="pair.condition.value" :name="'conditions['+index+'][value]'" class="mt-1 w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-4 py-3">
                                                        <option value="1st Year">1st Year</option>
                                                        <option value="2nd Year">2nd Year</option>
                                                        <option value="3rd Year">3rd Year</option>
                                                        <option value="4th Year">4th Year</option>
                                                    </select>
                                                </template>
                                                {{-- Income --}}
                                                <template x-if="pair.condition.type === 'income'">
                                                    <input type="number" x-model="pair.condition.value" :name="'conditions['+index+'][value]'" placeholder="Amount (₱)" class="mt-1 w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-4 py-3">
                                                </template>
                                                {{-- Disability --}}
                                                <template x-if="pair.condition.type === 'disability'">
                                                    <select x-model="pair.condition.value" :name="'conditions['+index+'][value]'" class="mt-1 w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-4 py-3">
                                                        <option value="yes">Yes</option>
                                                        <option value="no">No</option>
                                                    </select>
                                                </template>
                                                {{-- College --}}
                                                <template x-if="pair.condition.type === 'college'">
                                                    <select x-model="pair.condition.value" :name="'conditions['+index+'][value]'" class="mt-1 w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-4 py-3">
                                                        <option value="">Select College</option>
                                                        @foreach($colleges as $college)
                                                            <option value="{{ $college }}">{{ $college }}</option>
                                                        @endforeach
                                                    </select>
                                                </template>
                                                {{-- Others (Custom) --}}
                                                <template x-if="pair.condition.type === 'others'">
                                                    <input type="text" x-model="pair.condition.value" :name="'conditions['+index+'][value]'" placeholder="Describe custom condition" class="mt-1 w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-4 py-3">
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Right: Corresponding Document --}}
                                        <div class="space-y-3">
                                            <label class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase">Document Name</label>
                                            <input type="text" x-model="pair.document.name" :name="'documents['+index+'][name]'" placeholder="e.g. Proof of disability, Barangay Clearance" class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-4 py-3">
                                            
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase">File Type</label>
                                                    <select x-model="pair.document.type" :name="'documents['+index+'][type]'" class="mt-1 w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-3 py-2.5">
                                                        <option value="pdf">PDF</option>
                                                        <option value="image">Image</option>
                                                        <option value="both">Both</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase">Required?</label>
                                                    <select x-model="pair.document.mandatory" :name="'documents['+index+'][mandatory]'" class="mt-1 w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-600 px-3 py-2.5">
                                                        <option value="1">Yes</option>
                                                        <option value="0">No</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Delete Button --}}
                                        <button type="button" @click="pairs.splice(index,1)" class="absolute -right-3 -top-3 p-2.5 bg-white dark:bg-gray-800 rounded-full text-gray-400 hover:text-red-600 dark:hover:text-red-500 border-2 border-gray-300 dark:border-gray-600 shadow-lg hover:shadow-xl transition-all hover:scale-110">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        {{-- Eligibility Notes --}}
                        <div class="mt-8">
                            <label for="eligibility_notes" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Additional Eligibility Notes</label>
                            <textarea id="eligibility_notes" name="eligibility_notes" rows="3"
                                      placeholder="Any other specific requirements or notes..."
                                      class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-green-600 dark:focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:focus:ring-green-500/20 transition-all duration-200 px-4 py-3">{{ old('eligibility_notes', $scholarship->eligibility_notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Configuration --}}
            <div class="xl:col-span-4 space-y-6">
                
                {{-- Configuration Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="px-6 py-4 bg-gradient-to-r from-yellow-500 to-orange-500 flex items-center gap-3">
                        <div class="p-2 bg-white/20 backdrop-blur-sm rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-white">Configuration</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        {{-- Grant Amount --}}
                        <div>
                            <label for="grant_amount" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Grant Amount (₱) *</label>
                            <input type="number" step="0.01" id="grant_amount" name="grant_amount" min="0"
                                   value="{{ old('grant_amount', $scholarship->grant_amount ?? '') }}"
                                   placeholder="10,000.00"
                                   class="block w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 px-4 py-3 focus:border-bsu-red dark:focus:border-red-500 focus:ring-2 focus:ring-bsu-red/20 dark:focus:ring-red-500/20 transition-all duration-200">
                        </div>

                        {{-- Slots --}}
                        <div>
                            <label for="slots_available" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Available Slots</label>
                            <input type="number" id="slots_available" name="slots_available" min="0"
                                   value="{{ old('slots_available', $scholarship->slots_available ?? '') }}"
                                   placeholder="Leave empty for unlimited"
                                   class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-bsu-red dark:focus:border-red-500 focus:ring-2 focus:ring-bsu-red/20 dark:focus:ring-red-500/20 transition-all duration-200 px-4 py-3">
                        </div>

                        {{-- Grant Frequency --}}
                        <div>
                            <label for="grant_type" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Grant Frequency *</label>
                            <select id="grant_type" name="grant_type" required
                                    class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-bsu-red dark:focus:border-red-500 focus:ring-2 focus:ring-bsu-red/20 dark:focus:ring-red-500/20 transition-all duration-200 px-4 py-3">
                                <option value="recurring" {{ old('grant_type', $scholarship->grant_type ?? '') == 'recurring' ? 'selected' : '' }}>Recurring (Renewable)</option>
                                <option value="one_time" {{ old('grant_type', $scholarship->grant_type ?? '') == 'one_time' ? 'selected' : '' }}>One-time Grant</option>
                                <option value="discontinued" {{ old('grant_type', $scholarship->grant_type ?? '') == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                            </select>
                        </div>

                        {{-- Allow Existing Scholarship --}}
                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50">
                            <input id="allow_existing_scholarship" name="allow_existing_scholarship" type="checkbox" value="1"
                                   {{ old('allow_existing_scholarship', $scholarship->allow_existing_scholarship ?? false) ? 'checked' : '' }}
                                   class="focus:ring-bsu-red dark:focus:ring-red-500 h-5 w-5 text-bsu-red dark:text-red-500 border-gray-300 dark:border-gray-600 rounded cursor-pointer flex-shrink-0">
                            <div>
                                <label for="allow_existing_scholarship" class="font-bold text-gray-900 dark:text-gray-100 cursor-pointer">Stackable Scholarship</label>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Allow students with existing scholarships to apply for this scheme.</p>
                            </div>
                        </div>
                        </div>
                        </div>


                {{-- Application Dates Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 flex items-center gap-3">
                        <div class="p-2 bg-white/20 backdrop-blur-sm rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-white">Application Dates</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        {{-- Opening Date --}}
                        <div>
                            <label for="application_start_date" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Opening Date</label>
                            <input type="date" id="application_start_date" name="application_start_date"
                                   min="{{ date('Y-m-d') }}"
                                   value="{{ old('application_start_date', isset($scholarship->application_start_date) ? \Carbon\Carbon::parse($scholarship->application_start_date)->format('Y-m-d') : '') }}"
                                   class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-bsu-red dark:focus:border-red-500 focus:ring-2 focus:ring-bsu-red/20 dark:focus:ring-red-500/20 transition-all duration-200 px-4 py-3">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Leave empty to open immediately.</p>
                        </div>

                        {{-- Submission Deadline --}}
                        <div>
                            <label for="submission_deadline" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-2">Submission Deadline *</label>
                            <input type="date" id="submission_deadline" name="submission_deadline" required
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   value="{{ old('submission_deadline', isset($scholarship->submission_deadline) ? \Carbon\Carbon::parse($scholarship->submission_deadline)->format('Y-m-d') : '') }}"
                                   class="w-full rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:border-bsu-red dark:focus:border-red-500 focus:ring-2 focus:ring-bsu-red/20 dark:focus:ring-red-500/20 transition-all duration-200 px-4 py-3">
                        </div>
                    </div>
                </div>

                {{-- Visuals Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700 flex items-center gap-3">
                        <div class="p-2 bg-white/20 backdrop-blur-sm rounded-xl">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <h2 class="text-lg font-bold text-white">Visuals</h2>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Background Image</label>
                        
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-gray-50 transition-colors cursor-pointer" onclick="document.getElementById('background_image').click()">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="background_image" class="relative cursor-pointer bg-white rounded-md font-medium text-bsu-red hover:text-bsu-redDark focus-within:outline-none">
                                        <span>Upload a file</span>
                                        <input id="background_image" name="background_image" type="file" class="sr-only" accept="image/*">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                            </div>
                        </div>

                        @if(isset($scholarship) && $scholarship->background_image)
                            <div class="mt-4 relative rounded-lg overflow-hidden h-32 border border-gray-200">
                                <img src="{{ $scholarship->getBackgroundImageUrl() }}" alt="Current background" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center text-white text-sm font-medium">
                                    Current Image
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="w-full flex justify-center items-center gap-3 py-5 px-6 border border-transparent rounded-2xl shadow-xl text-lg font-bold text-white bg-gradient-to-r from-bsu-red via-red-600 to-red-700 hover:from-red-700 hover:via-red-600 hover:to-bsu-red focus:outline-none focus:ring-4 focus:ring-red-300 dark:focus:ring-red-800 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ isset($scholarship) ? 'Update Scholarship' : 'Create Scholarship' }}
                </button>

            </div>
        </div>
    </form>
</div>
@endsection
