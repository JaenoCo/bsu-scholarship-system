@php
    // Pre-calculate counts for AlpineJS logic
    $counts = [
        'all' => $notifications->count(),
        'scholarship_created' => $notifications->where('type', 'scholarship_created')->count(),
        'application_status' => $notifications->where('type', 'application_status')->count(),
        'sfao_comment' => $notifications->where('type', 'sfao_comment')->count(),
    ];
@endphp

<div class="space-y-8" x-data="{ 
    counts: {{ json_encode($counts) }},
    currentFilter: 'all',
    selectedNotification: null,
    isModalOpen: false,
    init() {
        this.$watch('subTab', val => this.currentFilter = val || 'all');
        this.currentFilter = this.subTab || 'all';
    },
    markAsRead(id, type) {
        fetch('{{ route('notifications.mark-read', ':id') }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                $dispatch('notification-changed', { id: id, type: type, status: 'read' });
            }
        });
    },
    markAsUnread(id, type) {
        fetch('{{ route('notifications.mark-unread', ':id') }}'.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                $dispatch('notification-changed', { id: id, type: type, status: 'unread' });
            }
        });
    },
    openNotification(notification) {
        this.selectedNotification = notification;
        this.isModalOpen = true;
        
        if (!notification.is_read) {
            this.markAsRead(notification.id, notification.type);
            notification.is_read = true; // Update local object
        }
    },
    toggleUnreadInModal() {
        if (this.selectedNotification) {
            this.markAsUnread(this.selectedNotification.id, this.selectedNotification.type);
            this.selectedNotification.is_read = false;
            this.closeModal();
        }
    },
    closeModal() {
        this.isModalOpen = false;
        setTimeout(() => {
            this.selectedNotification = null;
        }, 300);
    }
}">
    <!-- Header Section -->
    <!-- Header Section -->
    <div class="bg-bsu-red dark:bg-red-900 rounded-xl shadow-lg p-6 flex flex-col sm:flex-row justify-between items-center gap-6 relative overflow-hidden group">
        <!-- Background Pattern -->
        <div class="absolute top-0 right-0 w-32 h-full bg-white/10 skew-x-12 transform translate-x-16"></div>

        <div class="relative z-10 text-white">
            <h2 class="text-3xl font-extrabold tracking-tight font-sans">
                Notifications
            </h2>
            <p class="text-base text-red-100 mt-1 font-medium">
                Manage your alerts and updates
            </p>
        </div>
        
        <div x-show="unreadCount > 0" class="relative z-10">
            <button 
                @click="
                    fetch('{{ route('notifications.mark-all-read') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            $dispatch('notifications-read-all');
                            window.location.reload(); 
                        }
                    })
                "
                class="group flex items-center px-6 py-2.5 bg-white text-bsu-red rounded-lg text-sm font-semibold hover:bg-red-50 transition-all duration-300 shadow-sm hover:shadow-md"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2.5 text-bsu-red group-hover:text-red-700 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Mark all as read
            </button>
        </div>
    </div>

    <!-- Notifications List (Vertical) -->
    <div class="min-h-[400px]">
        @if($notifications->count() > 0)
            <div class="flex flex-col space-y-3">
                @foreach($notifications as $notification)
                    <!-- Dynamic styling based on type -->
                    @php
                        $iconStyle = match($notification->type) {
                            'scholarship_created' => 'bg-green-100 text-green-600',
                            'application_status' => 'bg-blue-100 text-blue-600', 
                            'sfao_comment' => 'bg-yellow-100 text-yellow-600',
                            default => 'bg-gray-100 text-gray-600'
                        };
                        
                        // Prepare notification object for Alpine
                        $notificationJson = json_encode([
                            'id' => $notification->id,
                            'type' => $notification->type,
                            'title' => $notification->title,
                            'message' => $notification->message,
                            'time_ago' => $notification->time_ago,
                            'is_read' => $notification->is_read,
                            'icon_html' => $notification->icon, 
                            'icon_style' => $iconStyle
                        ]);
                    @endphp

                    <div 
                        x-show="currentFilter === 'all' || currentFilter === '{{ $notification->type }}'"
                        x-data="{ 
                            localRead: {{ $notification->is_read ? 'true' : 'false' }} 
                        }"
                        @notification-changed.window="
                            if ($event.detail.id == {{ $notification->id }}) {
                                localRead = ($event.detail.status === 'read');
                            }
                        "
                        @click="
                            openNotification({{ $notificationJson }}); 
                            localRead = true;
                        "
                        class="group relative bg-white dark:bg-gray-800 rounded-xl p-4 border transition-all duration-200 cursor-pointer hover:shadow-md hover:bg-gray-50 dark:hover:bg-gray-700/50"
                        :class="localRead 
                            ? 'border-transparent dark:border-transparent' 
                            : 'border-bsu-red dark:border-red-500 shadow-md'"
                    >
                        <div class="flex items-start gap-4 pr-10">
                            <!-- Icon -->
                            <div class="flex-shrink-0 mt-1">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full {{ $iconStyle }} text-lg">
                                    {!! $notification->icon !!}
                                </span>
                            </div>
                            
                            <!-- Content Summary -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white pr-2" 
                                        :class="!localRead ? 'font-extrabold' : 'font-semibold'">
                                        {{ $notification->title }}
                                    </h3>
                                    <span class="text-xs text-gray-500 mt-1">
                                        {{ $notification->time_ago }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Unread/Read Toggle Button (Absolute positioned) -->
                        <div class="absolute top-4 right-4 z-10">
                            <button 
                                @click.stop="
                                    if (localRead) {
                                        markAsUnread({{ $notification->id }}, '{{ $notification->type }}');
                                        localRead = false;
                                    } else {
                                        markAsRead({{ $notification->id }}, '{{ $notification->type }}');
                                        localRead = true;
                                    }
                                "
                                class="p-2 rounded-full transition-all duration-200 focus:outline-none transform hover:scale-105"
                                :class="localRead 
                                    ? 'text-gray-400 hover:text-bsu-red hover:bg-gray-100 dark:hover:bg-gray-700' 
                                    : 'text-bsu-red bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40'"
                                :title="localRead ? 'Mark as Unread' : 'Mark as Read'"
                            >
                                <!-- Envelope Open (Read) / Envelope Closed (Unread) -->
                                <svg x-show="localRead" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                                </svg>
                                <svg x-show="!localRead" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty States -->
            <div x-show="counts.all === 0" x-cloak class="flex flex-col items-center justify-center py-20 px-4 text-center">
                 <div class="bg-gray-100 p-6 rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">All caught up!</h3>
                <p class="text-gray-500 mt-2 max-w-sm">You have no new notifications.</p>
            </div>
             
             <!-- Filter Specific Empty States -->
             <div x-show="currentFilter !== 'all' && counts[currentFilter] === 0" x-cloak class="flex flex-col items-center justify-center py-20 px-4 text-center">
                <div class="bg-gray-50 p-6 rounded-full mb-4 border border-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">No notifications found</h3>
                <p class="text-gray-500 mt-2 max-w-sm">No notifications match this filter.</p>
            </div>

        @else
            <!-- Global Empty State -->
             <div class="flex flex-col items-center justify-center py-24 px-4 text-center">
                <div class="bg-gray-100 p-8 rounded-full mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">All caught up!</h3>
                <p class="text-gray-500 mt-3">You have no notifications.</p>
            </div>
        @endif
    </div>

    <!-- Notification Details Modal (Vertical Style) -->
    <template x-teleport="body">
        <div x-show="isModalOpen" 
             x-cloak
             class="fixed inset-0 flex items-center justify-center p-4 sm:p-6"
             style="z-index: 9999;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md" @click="closeModal()"></div>

            <!-- Modal Card -->
            <div class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95">
                
                <!-- Hero Header (Vertical Style) -->
                <div class="relative h-32 sm:h-40 bg-gradient-to-br from-bsu-red to-red-900 shrink-0">
                    <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                    
                    <!-- Close Button -->
                    <button @click="closeModal()" 
                            class="absolute top-4 right-4 p-2 bg-black/20 hover:bg-black/40 text-white rounded-full backdrop-blur-md transition-all duration-200 z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>

                    <!-- Icon & Type Badge -->
                    <div class="absolute -bottom-8 left-8 flex items-end">
                        <div class="w-16 h-16 rounded-xl shadow-lg flex items-center justify-center text-3xl bg-white dark:bg-gray-800 border-4 border-white dark:border-gray-800"
                             :class="selectedNotification?.icon_style">
                            <span x-html="selectedNotification?.icon_html"></span>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="pt-12 px-8 pb-8 overflow-y-auto custom-scrollbar">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">
                                <span x-text="selectedNotification?.type.replace('_', ' ')"></span>
                            </span>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight" x-text="selectedNotification?.title"></h3>
                        </div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap mt-1" x-text="selectedNotification?.time_ago"></span>
                    </div>
                    
                    <div class="prose dark:prose-invert max-w-none bg-gray-50 dark:bg-gray-700/30 p-6 rounded-xl border border-gray-100 dark:border-gray-700">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed text-base" x-text="selectedNotification?.message"></p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-8 py-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-end items-center shrink-0">
                    <button @click="closeModal()" 
                            class="px-6 py-2.5 bg-gray-900 dark:bg-gray-700 text-white rounded-lg font-semibold hover:bg-gray-800 dark:hover:bg-gray-600 transition-all shadow-lg shadow-gray-200 dark:shadow-none transform hover:-translate-y-0.5">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
