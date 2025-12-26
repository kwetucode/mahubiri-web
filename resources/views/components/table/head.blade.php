@props(['sortable' => false])

<th {{ $attributes->merge(['class' => 'px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider']) }}>
    <div class="flex items-center space-x-1">
        <span>{{ $slot }}</span>
        @if($sortable)
            <svg class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
            </svg>
        @endif
    </div>
</th>
