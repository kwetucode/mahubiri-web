@props(['placeholder' => 'Rechercher...', 'label' => 'Rechercher'])

<div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $label }}</label>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input
            type="text"
            {{ $attributes->merge(['class' => 'w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-200 focus:border-[#6B4EAF] focus:ring-2 focus:ring-[#6B4EAF]/20 transition-all duration-200 outline-none placeholder-gray-400']) }}
            placeholder="{{ $placeholder }}"
        >
    </div>
</div>
