@extends('sfao.layouts.evaluation')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        @include('sfao.partials.page-header', [
            'title' => request('from_status') ? 'Evaluation Details' : 'Document Evaluation',
            'subtitle' => request('from_status') ? 'View application evaluation details and status' : 'Stage 4: Final Review & Application Decision'
        ])

        <!-- Student & Scholarship Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="h-16 w-16 rounded-full bg-bsu-red flex items-center justify-center">
                        <span class="text-xl font-bold text-white">
                            {{ strtoupper(substr($student->name, 0, 2)) }}
                        </span>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $student->name }}</h2>
                        <p class="text-gray-600 dark:text-gray-400">{{ $student->email }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $scholarship->scholarship_name }}</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ $scholarship->scholarship_type }} • {{ ucfirst($scholarship->priority_level) }}</p>
                </div>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="mb-8">
            @if(request('from_status'))
                <!-- Simplified progress for status view -->
                <div class="flex items-center justify-center space-x-2">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Evaluation Complete</span>
                    </div>
                    <div class="w-12 h-1 bg-green-500"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-bsu-red text-white rounded-full flex items-center justify-center text-sm font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">View Details</span>
                    </div>
                </div>
            @else
                <!-- Full progress indicator for normal evaluation flow -->
                <div class="flex items-center justify-center space-x-2">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Select Scholarship</span>
                    </div>
                    <div class="w-12 h-1 bg-green-500"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">SFAO Documents</span>
                    </div>
                    <div class="w-12 h-1 bg-green-500"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Scholarship Documents</span>
                    </div>
                    <div class="w-12 h-1 bg-bsu-red"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-bsu-red text-white rounded-full flex items-center justify-center text-sm font-bold">4</div>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Final Review</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Evaluation Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Document Status Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Document Status</h3>
                <div class="space-y-3">
                    @php
                        $totalDocs = $evaluatedDocuments->count();
                        $approvedDocs = $evaluatedDocuments->where('evaluation_status', 'approved')->count();
                        $rejectedDocs = $evaluatedDocuments->where('evaluation_status', 'rejected')->count();
                        $pendingDocs = $evaluatedDocuments->where('evaluation_status', 'pending')->count();
                    @endphp
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Documents:</span>
                        <span class="font-semibold">{{ $totalDocs }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-green-600 dark:text-green-400">Approved:</span>
                        <span class="font-semibold text-green-600 dark:text-green-400">{{ $approvedDocs }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-red-600 dark:text-red-400">Rejected:</span>
                        <span class="font-semibold text-red-600 dark:text-red-400">{{ $rejectedDocs }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-yellow-600 dark:text-yellow-400">Pending:</span>
                        <span class="font-semibold text-yellow-600 dark:text-yellow-400">{{ $pendingDocs }}</span>
                    </div>
                </div>
            </div>

            <!-- Scholarship Requirements -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Scholarship Requirements</h3>
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">Conditions:</h4>
                        <ul class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            @forelse($scholarship->conditions as $condition)
                                <li>• {{ $condition->getConditionDisplayName() }}: {{ $condition->getValueDisplayName() }}</li>
                            @empty
                                <li>No specific conditions</li>
                            @endforelse
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">Required Documents:</h4>
                        <ul class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            @forelse($scholarship->requiredDocuments as $doc)
                                <li>• {{ $doc->document_name }} {{ $doc->is_mandatory ? '(Required)' : '(Optional)' }}</li>
                            @empty
                                <li>No specific documents required</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Application Status -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Application Status</h3>
                @if($application)
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Status:</span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $application->status === 'approved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                   ($application->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                {{ ucfirst($application->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Applied:</span>
                            <span class="font-semibold">{{ $application->created_at->format('M d, Y') }}</span>
                        </div>
                        @if($application->updated_at != $application->created_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Last Updated:</span>
                                <span class="font-semibold">{{ $application->updated_at->format('M d, Y') }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">No application found for this scholarship.</p>
                @endif
            </div>
        </div>

        <!-- Document Evaluation Details -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Document Evaluation Details</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Review all evaluated documents and their status.</p>
            </div>
            
            <div class="p-6">
                @if($evaluatedDocuments->count() > 0)
                    <div class="space-y-4">
                        @foreach($evaluatedDocuments as $document)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $document->document_name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $document->getDocumentCategoryDisplayName() }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">
                                            {{ $document->getFileTypeDisplayName() }} • {{ $document->getFileSizeFormatted() }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ $document->getViewUrl() }}" target="_blank" 
                                           class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                            View Document
                                        </a>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $document->getEvaluationStatusBadgeColor() }}">
                                            {{ $document->getEvaluationStatusDisplayName() }}
                                        </span>
                                    </div>
                                </div>
                                
                                @if($document->evaluation_notes)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 mb-3">
                                        <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Evaluation Notes:</h5>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $document->evaluation_notes }}</p>
                                    </div>
                                @endif
                                
                                @if($document->evaluated_at)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Evaluated by {{ $document->evaluator->name ?? 'Unknown' }} on {{ $document->evaluated_at->format('M d, Y g:i A') }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No documents found for this scholarship.</p>
                @endif
            </div>
        </div>

        <!-- System Decision Display -->
        @if($application && in_array($application->status, ['in_progress', 'pending']) && isset($autoDecision))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">System Decision</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Based on document evaluation status, the system has automatically determined the final decision.</p>
            </div>
            
            <div class="p-6">
                <!-- System Decision Display -->
                <div class="mb-6">
                    <div class="rounded-lg p-6 border-2 
                        @if($autoDecision === 'approve')
                            bg-green-50 dark:bg-green-900/20 border-green-300 dark:border-green-600
                        @elseif($autoDecision === 'reject')
                            bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-600
                        @else
                            bg-yellow-50 dark:bg-yellow-900/20 border-yellow-300 dark:border-yellow-600
                        @endif">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                @if($autoDecision === 'approve')
                                    <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xl font-bold text-green-800 dark:text-green-200">Application Approved</h4>
                                        <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                                            All documents have been approved. The application will be marked as approved.
                                        </p>
                                    </div>
                                @elseif($autoDecision === 'reject')
                                    <div class="flex-shrink-0 w-12 h-12 bg-red-500 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xl font-bold text-red-800 dark:text-red-200">Application Rejected</h4>
                                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                                            One or more documents have been rejected. The application will be marked as rejected.
                                        </p>
                                    </div>
                                @else
                                    <div class="flex-shrink-0 w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-xl font-bold text-yellow-800 dark:text-yellow-200">Application Pending</h4>
                                        <p class="text-sm text-yellow-600 dark:text-yellow-400 mt-1">
                                            One or more documents are still pending evaluation. The application will remain pending.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remarks Section -->
                <div class="mb-6">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Evaluation Remarks <span class="text-gray-500">(Optional)</span>
                    </label>
                    <textarea 
                        id="remarks" 
                        name="remarks" 
                        rows="4" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-bsu-red focus:border-bsu-red dark:bg-gray-700 dark:text-white"
                        placeholder="Provide any additional comments or feedback for the student..."
                        maxlength="1000"
                    >{{ old('remarks', $application->remarks) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Maximum 1000 characters. This will be visible to the student.
                    </p>
                    @error('remarks')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Single Accept Button -->
                <div class="flex justify-end">
                    <button 
                        type="button" 
                        onclick="showConfirmationModal()"
                        class="text-white px-8 py-3 rounded-lg transition-colors font-semibold text-lg flex items-center space-x-2
                        @if($autoDecision === 'approve')
                            bg-green-600 hover:bg-green-700
                        @elseif($autoDecision === 'reject')
                            bg-red-600 hover:bg-red-700
                        @else
                            bg-yellow-600 hover:bg-yellow-700
                        @endif"
                    >
                        <span>Accept System Decision</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
            @if(request('from_status'))
                <a href="{{ route('sfao.evaluation.show', $student->id) }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Select Scholarship
                    </span>
                </a>
            @else
                <a href="{{ route('sfao.evaluation.sfao-documents', ['user_id' => $student->id, 'scholarship_id' => $scholarship->id]) }}" 
                   class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Document Evaluation
                    </span>
                </a>
            @endif
            
            <div class="flex space-x-4">
                @if($application)
                    @if($application->status === 'pending')
                        <span class="text-gray-500 dark:text-gray-400 px-6 py-2">
                            Use the form above to make your decision
                        </span>
                    @else
                        <div class="text-center">
                            <span class="text-gray-500 dark:text-gray-400 px-6 py-2 block">
                                Application {{ $application->status }}
                            </span>
                            @if($application->remarks)
                                <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">SFAO Remarks:</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $application->remarks }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <span class="text-gray-500 dark:text-gray-400 px-6 py-2">
                        No application found
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 transition-opacity duration-300" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-auto transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <div class="p-6 text-center">
                <!-- Modal Icon -->
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" id="modalIcon">
                    <!-- Icon will be set by JavaScript -->
                </div>
                
                <!-- Modal Title -->
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2" id="modalTitle">
                    <!-- Title will be set by JavaScript -->
                </h3>
                
                <!-- Modal Message -->
                <div class="mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400" id="modalMessage">
                        <!-- Message will be set by JavaScript -->
                    </p>
                </div>
                
                <!-- Modal Actions -->
                <div class="flex justify-center space-x-4">
                    <button 
                        id="cancelButton"
                        onclick="hideConfirmationModal()"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        id="confirmButton"
                        onclick="confirmAction()"
                        class="px-4 py-2 rounded-md text-sm font-medium text-white transition-colors"
                    >
                        <!-- Button text and color will be set by JavaScript -->
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for submission -->
<form id="hiddenForm" method="POST" action="" style="display: none;">
    @csrf
    <textarea name="remarks" id="hiddenRemarks"></textarea>
</form>

<script>
function showConfirmationModal() {
    const autoDecision = "{{ $autoDecision ?? 'pending' }}";
    const modal = document.getElementById('confirmationModal');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const confirmButton = document.getElementById('confirmButton');
    
    if (autoDecision === 'approve') {
        // Approve styling
        modalIcon.innerHTML = `
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        `;
        modalIcon.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4 bg-green-100 dark:bg-green-900';
        modalTitle.textContent = 'Confirm Application Approval';
        modalMessage.textContent = 'Accept the system\'s decision to approve this application? All documents have been approved. The student will be notified.';
        confirmButton.textContent = 'Accept & Approve';
        confirmButton.className = 'px-4 py-2 rounded-md text-sm font-medium text-white transition-colors bg-green-600 hover:bg-green-700';
    } else if (autoDecision === 'reject') {
        // Reject styling
        modalIcon.innerHTML = `
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        `;
        modalIcon.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4 bg-red-100 dark:bg-red-900';
        modalTitle.textContent = 'Confirm Application Rejection';
        modalMessage.textContent = 'Accept the system\'s decision to reject this application? One or more documents have been rejected. The student will be notified.';
        confirmButton.textContent = 'Accept & Reject';
        confirmButton.className = 'px-4 py-2 rounded-md text-sm font-medium text-white transition-colors bg-red-600 hover:bg-red-700';
    } else {
        // Pending styling
        modalIcon.innerHTML = `
            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        `;
        modalIcon.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4 bg-yellow-100 dark:bg-yellow-900';
        modalTitle.textContent = 'Confirm Application Pending';
        modalMessage.textContent = 'Accept the system\'s decision to keep this application pending? One or more documents are still pending evaluation. The student will be notified.';
        confirmButton.textContent = 'Accept & Keep Pending';
        confirmButton.className = 'px-4 py-2 rounded-md text-sm font-medium text-white transition-colors bg-yellow-600 hover:bg-yellow-700';
    }
    
    modal.classList.remove('hidden');
    
    // Trigger smooth animation
    setTimeout(() => {
        const modalContent = document.getElementById('modalContent');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function hideConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    const modalContent = document.getElementById('modalContent');
    
    // Animate out
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    
    // Hide modal after animation
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function confirmAction() {
    // Set the hidden form values (action is no longer needed, but keeping for compatibility)
    document.getElementById('hiddenRemarks').value = document.getElementById('remarks').value;
    
    // Set the form action
    const form = document.getElementById('hiddenForm');
    form.action = "{{ route('sfao.evaluation.final-submit', ['user_id' => $student->id, 'scholarship_id' => $scholarship->id]) }}";
    
    // Submit the form
    form.submit();
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('confirmationModal');
    if (event.target === modal) {
        hideConfirmationModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideConfirmationModal();
    }
});
</script>

@endsection
