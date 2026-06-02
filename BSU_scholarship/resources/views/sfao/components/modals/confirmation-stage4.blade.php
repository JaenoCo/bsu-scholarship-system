<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 z-50 overflow-y-auto hidden" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0;">
    <!-- Background Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity duration-300"></div>
    
    <!-- Modal Container - Centered in Viewport -->
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all duration-300 scale-95 opacity-0 my-8" id="modalContent">
            <!-- Modal Icon -->
            <div class="flex justify-center mb-6">
                <div class="flex items-center justify-center h-20 w-20 rounded-full" id="modalIcon">
                    <!-- Icon will be set by JavaScript -->
                </div>
            </div>
            
            <!-- Modal Title -->
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 text-center" id="modalTitle">
                <!-- Title will be set by JavaScript -->
            </h3>
            
            <!-- Modal Message -->
            <div class="mb-8">
                <p class="text-base text-gray-600 dark:text-gray-400 text-center leading-relaxed" id="modalMessage">
                    <!-- Message will be set by JavaScript -->
                </p>
            </div>
            
            <!-- Modal Actions -->
            <div class="flex flex-col sm:flex-row justify-center gap-3">
                <button 
                    id="cancelButton"
                    onclick="hideConfirmationModal()"
                    class="px-6 py-3 bg-gray-500 text-white text-base font-semibold rounded-xl hover:bg-gray-600 transition-colors shadow-lg hover:shadow-xl"
                >
                    Cancel
                </button>
                <button 
                    id="confirmButton"
                    onclick="confirmAction()"
                    class="px-6 py-3 rounded-xl text-base font-semibold text-white transition-colors shadow-lg hover:shadow-xl"
                >
                    <!-- Button text and color will be set by JavaScript -->
                </button>
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
            <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        `;
        modalIcon.className = 'flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/30';
        modalTitle.textContent = 'Confirm Application Approval';
        modalMessage.textContent = 'Accept the system\'s decision to approve this application? All documents have been approved. The student will be notified.';
        confirmButton.textContent = 'Accept & Approve';
        confirmButton.className = 'px-6 py-3 rounded-xl text-base font-semibold text-white transition-colors shadow-lg hover:shadow-xl bg-green-600 hover:bg-green-700';
    } else if (autoDecision === 'reject') {
        // Reject styling
        modalIcon.innerHTML = `
            <svg class="h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        `;
        modalIcon.className = 'flex items-center justify-center h-20 w-20 rounded-full bg-red-100 dark:bg-red-900/30';
        modalTitle.textContent = 'Confirm Application Rejection';
        modalMessage.textContent = 'Accept the system\'s decision to reject this application? One or more documents have been rejected. The student will be notified.';
        confirmButton.textContent = 'Accept & Reject';
        confirmButton.className = 'px-6 py-3 rounded-xl text-base font-semibold text-white transition-colors shadow-lg hover:shadow-xl bg-red-600 hover:bg-red-700';
    } else {
        // Pending styling
        modalIcon.innerHTML = `
            <svg class="h-12 w-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        `;
        modalIcon.className = 'flex items-center justify-center h-20 w-20 rounded-full bg-yellow-100 dark:bg-yellow-900/30';
        modalTitle.textContent = 'Confirm Application Pending';
        modalMessage.textContent = 'Accept the system\'s decision to keep this application pending? One or more documents are still pending evaluation. The student will be notified.';
        confirmButton.textContent = 'Accept & Keep Pending';
        confirmButton.className = 'px-6 py-3 rounded-xl text-base font-semibold text-white transition-colors shadow-lg hover:shadow-xl bg-yellow-600 hover:bg-yellow-700';
    }
    
    modal.classList.remove('hidden');
    // Lock body scroll
    document.body.style.overflow = 'hidden';
    
    // Scroll modal into view
    setTimeout(() => {
        const modalContent = document.getElementById('modalContent');
        modalContent.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 50);
    
    // Trigger smooth animation
    setTimeout(() => {
        const modalContent = document.getElementById('modalContent');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 100);
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
        // Unlock body scroll
        document.body.style.overflow = '';
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
