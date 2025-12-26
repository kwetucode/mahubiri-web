@props(['icon' => null, 'href' => null, 'danger' => false])

@php
    $classes = 'group flex items-center w-full px-4 py-3 text-sm transition-colors duration-150 ' . 
               ($danger 
                   ? 'text-red-700 hover:bg-red-50 hover:text-red-900' 
                   : 'text-gray-700 hover:bg-[#E6E3F5]/50 hover:text-[#6B4EAF]');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <span class="mr-2 flex-shrink-0 w-5 h-5">
                {!! $icon !!}
            </span>
        @endif
        <span class="flex-1">{{ $slot }}</span>
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        @if($icon)
            <span class="mr-2 flex-shrink-0 w-5 h-5">
                {!! $icon !!}
            </span>
        @endif
        <span class="flex-1">{{ $slot }}</span>
    </button>
@endif
