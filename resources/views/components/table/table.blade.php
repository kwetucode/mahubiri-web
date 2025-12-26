@props(['striped' => false])

<div class="bg-white rounded-xl shadow-lg border border-gray-100">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200']) }}>
            {{ $slot }}
        </table>
    </div>
</div>
