@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'violet', // violet, green, blue, red, white
    'size' => 'default', // default, compact
])

@php
    $colorClasses = [
        'violet' => 'bg-gradient-to-br from-violet-500 to-violet-600 text-white',
        'green' => 'bg-white border border-gray-200',
        'blue' => 'bg-white border border-gray-200',
        'red' => 'bg-white border border-gray-200',
        'white' => 'bg-white border border-gray-200',
    ];

    $iconBgClasses = [
        'violet' => 'bg-white/20',
        'green' => 'bg-green-100',
        'blue' => 'bg-blue-100',
        'red' => 'bg-red-100',
        'white' => 'bg-violet-100',
    ];

    $iconColorClasses = [
        'violet' => 'text-white',
        'green' => 'text-green-600',
        'blue' => 'text-blue-600',
        'red' => 'text-red-600',
        'white' => 'text-violet-600',
    ];

    $titleColorClasses = [
        'violet' => 'text-violet-100',
        'green' => 'text-gray-600',
        'blue' => 'text-gray-600',
        'red' => 'text-gray-600',
        'white' => 'text-gray-600',
    ];

    $valueColorClasses = [
        'violet' => 'text-white',
        'green' => 'text-gray-900',
        'blue' => 'text-gray-900',
        'red' => 'text-gray-900',
        'white' => 'text-gray-900',
    ];

    $cardClass = $size === 'compact'
        ? 'rounded-lg shadow-md p-4'
        : 'rounded-xl shadow-lg p-6';

    $iconSize = $size === 'compact' ? 'w-5 h-5' : 'w-8 h-8';
    $iconPadding = $size === 'compact' ? 'p-2' : 'p-3';
    $titleSize = $size === 'compact' ? 'text-xs' : 'text-sm';
    $valueSize = $size === 'compact' ? 'text-2xl' : 'text-3xl';
@endphp

<div {{ $attributes->merge(['class' => $cardClass . ' ' . $colorClasses[$color] . ' hover:shadow-lg transition-all duration-200']) }}>
    <div class="flex items-center {{ $size === 'compact' ? 'space-x-3' : 'justify-between' }}">
        @if($size === 'compact')
            <div class="{{ $iconBgClasses[$color] }} {{ $iconPadding }} rounded-lg flex-shrink-0">
                @if($icon)
                    <div class="{{ $iconSize }} {{ $iconColorClasses[$color] }}">
                        {!! $icon !!}
                    </div>
                @else
                    {{ $iconSlot ?? '' }}
                @endif
            </div>
            <div>
                <p class="{{ $titleColorClasses[$color] }} {{ $titleSize }} font-medium">{{ $title }}</p>
                <p class="{{ $valueColorClasses[$color] }} {{ $valueSize }} font-bold mt-1">{{ $value }}</p>
            </div>
        @else
            <div>
                <p class="{{ $titleColorClasses[$color] }} {{ $titleSize }} font-medium mb-1">{{ $title }}</p>
                <p class="{{ $valueColorClasses[$color] }} {{ $valueSize }} font-bold">{{ $value }}</p>
            </div>
            <div class="{{ $iconBgClasses[$color] }} {{ $iconPadding }} rounded-full">
                @if($icon)
                    <div class="{{ $iconSize }} {{ $iconColorClasses[$color] }}">
                        {!! $icon !!}
                    </div>
                @else
                    {{ $iconSlot ?? '' }}
                @endif
            </div>
        @endif
    </div>
</div>
