@props(['bold' => false])

<td {{ $attributes->merge(['class' => 'px-6 py-4 text-sm ' . ($bold ? 'font-medium text-gray-900' : 'text-gray-700')]) }}>
    {{ $slot }}
</td>
