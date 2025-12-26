<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <!-- Notification Bell Button -->
    <button 
        @click="open = !open"
        class="relative p-2 rounded-full hover:bg-gray-100 transition-colors"
        type="button"
    >
        <x-icon name="bell" class="w-5 h-5 text-gray-600" />
        
        <!-- Unread Badge -->
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full min-w-4.5">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-96 max-w-[calc(100vw-2rem)] bg-white rounded-xl shadow-2xl border border-gray-200 z-50"
        style="display: none;"
    >
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                Notifications
                @if($unreadCount > 0)
                    <span class="ml-2 text-sm text-violet-600">({{ $unreadCount }} non lues)</span>
                @endif
            </h3>
            @if($unreadCount > 0)
                <button 
                    wire:click="markAllAsRead"
                    class="text-sm text-violet-600 hover:text-violet-700 font-medium"
                >
                    Tout marquer lu
                </button>
            @endif
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div 
                    class="px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 {{ !$notification['read_at'] ? 'bg-violet-50/30' : '' }}"
                    wire:key="notification-{{ $notification['id'] }}"
                >
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="shrink-0">
                            <div class="w-10 h-10 rounded-full bg-{{ $notification['color'] }}-100 flex items-center justify-center">
                                @php
                                    $iconName = $notification['icon'] ?? 'bell';
                                    $iconColor = 'text-' . $notification['color'] . '-600';
                                @endphp
                                <x-icon :name="$iconName" class="w-5 h-5 {{ $iconColor }}" />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $notification['type'] }}
                                    </p>
                                    
                                    @if(isset($notification['data']['message']))
                                        <p class="mt-1 text-sm text-gray-600">
                                            {{ $notification['data']['message'] }}
                                        </p>
                                    @endif

                                    @if(isset($notification['data']['code']))
                                        <p class="mt-1 text-sm font-mono font-bold text-violet-600">
                                            Code: {{ $notification['data']['code'] }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-1 ml-2">
                                    @if(!$notification['read_at'])
                                        <button
                                            wire:click="markAsRead('{{ $notification['id'] }}')"
                                            class="p-1 rounded hover:bg-gray-200 transition-colors"
                                            title="Marquer comme lu"
                                        >
                                            <x-icon name="check" class="w-4 h-4 text-gray-600" />
                                        </button>
                                    @endif
                                    
                                    <button
                                        wire:click="deleteNotification('{{ $notification['id'] }}')"
                                        class="p-1 rounded hover:bg-red-100 transition-colors"
                                        title="Supprimer"
                                    >
                                        <x-icon name="x" class="w-4 h-4 text-red-600" />
                                    </button>
                                </div>
                            </div>

                            <!-- Timestamp -->
                            <p class="mt-1 text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <x-icon name="bell" class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                    <p class="text-sm text-gray-500">Aucune notification</p>
                </div>
            @endforelse
        </div>

        <!-- Footer - View All -->
        @if(count($notifications) > 0)
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 rounded-b-xl">
                <a 
                    href="#" 
                    class="block text-center text-sm font-medium text-violet-600 hover:text-violet-700"
                >
                    Voir toutes les notifications
                </a>
            </div>
        @endif
    </div>
</div>
