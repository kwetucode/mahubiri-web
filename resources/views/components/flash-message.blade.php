@props(['type' => 'success', 'message' => null, 'timeout' => 5000])

@php
    $types = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'text' => 'text-green-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'text' => 'text-yellow-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
        ]
    ];

    $config = $types[$type] ?? $types['info'];
@endphp

@if($message)
<div
    x-data="{
        show: true,
        timeout: {{ $timeout }},
        init() {
            if (this.timeout > 0) {
                setTimeout(() => { this.show = false }, this.timeout)
            }
        }
    }"
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="mb-4 {{ $config['bg'] }} {{ $config['border'] }} border rounded-lg shadow-sm overflow-hidden"
    role="alert"
>
    <div class="p-4 flex items-start">
        <!-- Icon -->
        <div class="shrink-0">
            <svg class="h-5 w-5 {{ $config['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $config['icon'] !!}
            </svg>
        </div>

        <!-- Message -->
        <div class="ml-3 flex-1">
            <p class="{{ $config['text'] }} text-sm font-medium">
                {{ $message }}
            </p>
        </div>

        <!-- Close button -->
        <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
                <button
                    type="button"
                    @click="show = false"
                    class="{{ $config['text'] }} rounded-lg p-1.5 inline-flex items-center justify-center hover:bg-opacity-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-transparent transition-all duration-150"
                >
                    <span class="sr-only">Fermer</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Progress bar (optional) -->
    @if($timeout > 0)
    <div class="h-1 bg-white bg-opacity-30">
        <div
            x-data="{ width: 100 }"
            x-init="
                let interval = setInterval(() => {
                    width = Math.max(0, width - (100 / (timeout / 100)));
                    if (width <= 0) clearInterval(interval);
                }, 100)
            "
            :style="'width: ' + width + '%'"
            class="h-full transition-all duration-100 ease-linear"
            :class="{
                'bg-green-500': '{{ $type }}' === 'success',
                'bg-red-500': '{{ $type }}' === 'error',
                'bg-yellow-500': '{{ $type }}' === 'warning',
                'bg-blue-500': '{{ $type }}' === 'info'
            }"
        ></div>
    </div>
    @endif
</div>
@endif
