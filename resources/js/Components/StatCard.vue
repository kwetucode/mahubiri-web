<script setup>
defineProps({
    /** Value to display prominently */
    value: {
        type: [String, Number],
        default: 0,
    },
    /** Label / description */
    label: {
        type: String,
        default: '',
    },
    /** SVG path for icon */
    icon: {
        type: String,
        default: '',
    },
    /** Trend text (e.g. '+12%') */
    trend: {
        type: String,
        default: '',
    },
    /** Trend direction */
    trendUp: {
        type: Boolean,
        default: true,
    },
    /** Color variant */
    color: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'emerald', 'blue', 'amber', 'red', 'gray'].includes(v),
    },
});

const colors = {
    primary: {
        icon: 'bg-linear-to-br from-primary to-primary-dark text-white shadow-md shadow-primary/25',
        trend: 'text-primary bg-primary/10',
        border: 'border-primary/10',
    },
    emerald: {
        icon: 'bg-linear-to-br from-emerald-500 to-emerald-600 text-white shadow-md shadow-emerald-500/25',
        trend: 'text-emerald-700 bg-emerald-100',
        border: 'border-emerald-100',
    },
    blue: {
        icon: 'bg-linear-to-br from-blue-500 to-blue-600 text-white shadow-md shadow-blue-500/25',
        trend: 'text-blue-700 bg-blue-100',
        border: 'border-blue-100',
    },
    amber: {
        icon: 'bg-linear-to-br from-amber-500 to-amber-600 text-white shadow-md shadow-amber-500/25',
        trend: 'text-amber-700 bg-amber-100',
        border: 'border-amber-100',
    },
    red: {
        icon: 'bg-linear-to-br from-red-500 to-red-600 text-white shadow-md shadow-red-500/25',
        trend: 'text-red-700 bg-red-100',
        border: 'border-red-100',
    },
    gray: {
        icon: 'bg-linear-to-br from-gray-500 to-gray-600 text-white shadow-md shadow-gray-500/25',
        trend: 'text-gray-700 bg-gray-100',
        border: 'border-gray-100',
    },
};
</script>

<template>
    <div
        class="group bg-white dark:bg-gray-800 rounded-xl border px-3 py-2.5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-default"
        :class="colors[color].border"
    >
        <div class="flex items-center gap-2.5">
            <div
                class="flex items-center justify-center w-7 h-7 rounded-lg shrink-0"
                :class="colors[color].icon"
            >
                <slot name="icon">
                    <svg v-if="icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
                    </svg>
                </slot>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-lg font-extrabold text-gray-900 dark:text-white tabular-nums leading-none">
                    {{ typeof value === 'number' ? value.toLocaleString() : value }}
                </p>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-0.5 truncate">{{ label }}</p>
            </div>
            <span
                v-if="trend"
                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-semibold shrink-0"
                :class="colors[color].trend"
            >
                <svg v-if="trendUp" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                <svg v-else class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
                {{ trend }}
            </span>
        </div>
    </div>
</template>
