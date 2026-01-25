@props(['title', 'icon', 'active' => false])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }" class="space-y-1">
    <!-- Dropdown Header -->
    <button @click="open = !open" 
            class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 hover:bg-white/10 {{ $active ? 'bg-white/20 text-white' : 'text-[#E6E3F5]' }}"
            :class="{ 'justify-center': sidebarCollapsed }">
        <div class="flex items-center space-x-3">
            <div class="h-5 w-5 shrink-0">
                {{ $icon }}
            </div>
            <span x-show="!sidebarCollapsed" x-transition>{{ $title }}</span>
        </div>
        <svg x-show="!sidebarCollapsed" 
             :class="{ 'rotate-180': open }" 
             class="h-4 w-4 transition-transform duration-200" 
             fill="none" 
             stroke="currentColor" 
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown Items -->
    <div x-show="open && !sidebarCollapsed" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="ml-8 space-y-1">
        {{ $slot }}
    </div>
</div>
