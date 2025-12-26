@props(['hover' => true])

<tr {{ $attributes->merge(['class' => ($hover ? 'hover:bg-[#E6E3F5]/30 transition-colors duration-150' : '')]) }}>
    {{ $slot }}
</tr>
