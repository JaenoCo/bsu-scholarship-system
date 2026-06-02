@props([
    'id' => 'confirmationModal',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmText' => 'Yes, Continue',
    'cancelText' => 'Cancel',
    'confirmClass' => 'bg-red-600 hover:bg-red-700',
    'action' => null
])

<div id="{{ $id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-96">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">{{ $title }}</h2>
        <p class="mb-6 text-gray-600 dark:text-gray-300">{{ $message }}</p>

        @if($action)
            <form method="POST" action="{{ $action }}">
                @csrf
                {{ $slot }}
                
                <div class="flex justify-end gap-3">
                    <button type="button" 
                            onclick="closeModal('{{ $id }}')" 
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg">
                        {{ $cancelText }}
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-white rounded-lg {{ $confirmClass }}">
                        {{ $confirmText }}
                    </button>
                </div>
            </form>
        @else
            <div class="flex justify-end gap-3">
                <button type="button" 
                        onclick="closeModal('{{ $id }}')" 
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg">
                    {{ $cancelText }}
                </button>
                <button type="button" 
                        onclick="confirmAction('{{ $id }}')" 
                        class="px-4 py-2 text-white rounded-lg {{ $confirmClass }}">
                    {{ $confirmText }}
                </button>
            </div>
        @endif
    </div>
</div>

<script>
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
    
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }
    
    function confirmAction(modalId) {
        // Override this function in the parent component if needed
        closeModal(modalId);
    }
</script>

