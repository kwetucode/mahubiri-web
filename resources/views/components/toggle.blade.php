@props(['checked' => false, 'disabled' => false])

<button
    type="button"
    {{ $attributes->merge(['class' => 'relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#6B4EAF] focus:ring-offset-2 ' . ($checked ? 'bg-[#6B4EAF]' : 'bg-gray-300')]) }}
    role="switch"
    aria-checked="{{ $checked ? 'true' : 'false' }}"
    @if($disabled) disabled @endif
>
    <span class="sr-only">Toggle</span>
    <span
        class="inline-block h-4 w-4 transform rounded-full bg-white shadow-lg transition-transform duration-200 ease-in-out {{ $checked ? 'translate-x-6' : 'translate-x-1' }}"
    ></span>
</button>
