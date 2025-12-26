@props(['variant' => 'primary', 'type' => 'button'])

@php
$classes = [
    'primary' => 'bg-gradient-to-r from-[#6B4EAF] to-[#9C7DC7] hover:from-[#5A3D94] hover:to-[#6B4EAF] text-white shadow-lg shadow-violet-300/50',
    'secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-300/50',
][$variant];
@endphp

<button 
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => $classes . ' px-6 py-2.5 rounded-lg font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 disabled:opacity-50 disabled:cursor-not-allowed'
    ]) }}
>
    {{ $slot }}
</button>
