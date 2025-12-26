@props(['href', 'active' => false, 'icon'])

<a 
    href="{{ $href }}" 
    {{ $attributes->merge(['class' => 'flex items-center px-4 py-3 rounded-lg transition-colors group ' . ($active ? 'bg-[#9C7DC7]/30 border-l-4 border-[#E8B77D]' : 'hover:bg-[#9C7DC7]/20')]) }}
    :class="{ 'justify-center': sidebarCollapsed }"
    :title="sidebarCollapsed ? '{{ $slot }}' : ''"
>
    <div class="w-5 h-5 group-hover:scale-110 transition-transform" :class="{ 'mr-0': sidebarCollapsed, 'mr-3': !sidebarCollapsed }">
        {!! $icon !!}
    </div>
    <span class="font-medium" x-show="!sidebarCollapsed" x-transition>{{ $slot }}</span>
</a>
