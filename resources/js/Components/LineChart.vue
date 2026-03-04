<script setup>
import { ref, computed, watch, onMounted, shallowRef } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Filler,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Filler);

const isDarkMode = ref(false);
const updateDarkMode = () => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
};
onMounted(() => {
    updateDarkMode();
    const observer = new MutationObserver(updateDarkMode);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

const props = defineProps({
    /** Chart title */
    title: {
        type: String,
        default: '',
    },
    /** Subtitle */
    subtitle: {
        type: String,
        default: '',
    },
    /** Labels (x-axis) */
    labels: {
        type: Array,
        default: () => [],
    },
    /** Data points (y-axis) */
    data: {
        type: Array,
        default: () => [],
    },
    /** Line/fill color (hex) */
    color: {
        type: String,
        default: '#6B4EAF',
    },
    /** Summary value shown top-right */
    total: {
        type: [String, Number],
        default: null,
    },
    /** Trend text */
    trend: {
        type: String,
        default: '',
    },
    /** Trend direction */
    trendUp: {
        type: Boolean,
        default: true,
    },
    /** Loading state */
    loading: {
        type: Boolean,
        default: false,
    },
    /** SVG icon path */
    icon: {
        type: String,
        default: '',
    },
    /** Height of the chart area */
    height: {
        type: Number,
        default: 220,
    },
    /** Available filter options: [{ label, value }] */
    filters: {
        type: Array,
        default: () => [],
    },
    /** Currently selected filter value */
    activeFilter: {
        type: String,
        default: '',
    },
    /** Custom period support */
    showCustomPeriod: {
        type: Boolean,
        default: true,
    },
    /** Custom start date */
    startDate: {
        type: String,
        default: '',
    },
    /** Custom end date */
    endDate: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:activeFilter', 'update:startDate', 'update:endDate', 'filter-change']);

const showCustom = ref(false);
const localStart = ref(props.startDate);
const localEnd = ref(props.endDate);

watch(() => props.startDate, (v) => localStart.value = v);
watch(() => props.endDate, (v) => localEnd.value = v);

const hexToRgba = (hex, alpha) => {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.data,
            borderColor: props.color,
            backgroundColor: (ctx) => {
                if (!ctx.chart?.chartArea) return hexToRgba(props.color, 0.1);
                const gradient = ctx.chart.ctx.createLinearGradient(0, ctx.chart.chartArea.top, 0, ctx.chart.chartArea.bottom);
                gradient.addColorStop(0, hexToRgba(props.color, 0.15));
                gradient.addColorStop(1, hexToRgba(props.color, 0.01));
                return gradient;
            },
            fill: true,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: props.color,
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2,
        },
    ],
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: isDarkMode.value ? '#374151' : '#1f2937',
            titleColor: '#f9fafb',
            bodyColor: '#d1d5db',
            titleFont: { size: 12, weight: '600' },
            bodyFont: { size: 11 },
            padding: { x: 12, y: 8 },
            cornerRadius: 8,
            displayColors: false,
        },
    },
    scales: {
        x: {
            grid: { display: false },
            border: { display: false },
            ticks: {
                font: { size: 11 },
                color: isDarkMode.value ? '#6b7280' : '#9ca3af',
                maxRotation: 0,
            },
        },
        y: {
            grid: {
                color: isDarkMode.value ? '#374151' : '#f3f4f6',
                drawBorder: false,
            },
            border: { display: false },
            ticks: {
                font: { size: 11 },
                color: isDarkMode.value ? '#6b7280' : '#9ca3af',
                precision: 0,
            },
            beginAtZero: true,
        },
    },
}));

const selectFilter = (value) => {
    showCustom.value = false;
    emit('update:activeFilter', value);
    emit('filter-change', { filter: value });
};

const toggleCustom = () => {
    showCustom.value = !showCustom.value;
};

const applyCustomPeriod = () => {
    if (localStart.value && localEnd.value) {
        emit('update:startDate', localStart.value);
        emit('update:endDate', localEnd.value);
        emit('update:activeFilter', 'custom');
        emit('filter-change', { filter: 'custom', startDate: localStart.value, endDate: localEnd.value });
        showCustom.value = false;
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div
                    v-if="icon"
                    class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0"
                    :style="{ backgroundColor: hexToRgba(color, 0.1), color: color }"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ title }}</h3>
                    <p v-if="subtitle" class="text-[11px] text-gray-400 dark:text-gray-500">{{ subtitle }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Total + trend -->
                <div v-if="total != null" class="text-right mr-2 hidden sm:block">
                    <p class="text-lg font-extrabold text-gray-900 dark:text-white leading-tight tabular-nums">
                        {{ typeof total === 'number' ? total.toLocaleString() : total }}
                    </p>
                    <span
                        v-if="trend"
                        class="inline-flex items-center gap-0.5 text-[10px] font-semibold"
                        :class="trendUp ? 'text-emerald-600' : 'text-red-500'"
                    >
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                :d="trendUp ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'"
                            />
                        </svg>
                        {{ trend }}
                    </span>
                </div>

                <!-- Filter buttons -->
                <div v-if="filters.length" class="flex items-center gap-1 bg-gray-100/80 dark:bg-gray-700/60 rounded-xl p-0.5">
                    <button
                        v-for="f in filters"
                        :key="f.value"
                        @click="selectFilter(f.value)"
                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all duration-200"
                        :class="activeFilter === f.value
                            ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    >
                        {{ f.label }}
                    </button>
                    <button
                        v-if="showCustomPeriod"
                        @click="toggleCustom"
                        class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all duration-200"
                        :class="activeFilter === 'custom'
                            ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Custom period picker -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1 max-h-0"
            enter-to-class="opacity-100 translate-y-0 max-h-24"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 max-h-24"
            leave-to-class="opacity-0 max-h-0"
        >
            <div v-if="showCustom" class="flex items-center gap-2 px-5 py-2.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 overflow-hidden">
                <label class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Du</label>
                <input
                    v-model="localStart"
                    type="date"
                    class="px-2 py-1 text-xs border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary bg-white dark:bg-gray-700 dark:text-gray-200"
                />
                <label class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">au</label>
                <input
                    v-model="localEnd"
                    type="date"
                    class="px-2 py-1 text-xs border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary bg-white dark:bg-gray-700 dark:text-gray-200"
                />
                <button
                    @click="applyCustomPeriod"
                    :disabled="!localStart || !localEnd"
                    class="px-3 py-1 text-[11px] font-semibold text-white bg-primary rounded-lg hover:bg-primary-dark transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Appliquer
                </button>
            </div>
        </Transition>

        <!-- Chart -->
        <div class="px-5 py-4 relative" :style="{ height: height + 'px' }">
            <!-- Loading overlay -->
            <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-gray-800/80 z-10">
                <div class="flex flex-col items-center gap-2">
                    <div class="w-7 h-7 border-2 border-gray-200 border-t-primary rounded-full animate-spin"></div>
                    <span class="text-[11px] text-gray-400 font-medium">Chargement...</span>
                </div>
            </div>

            <Line
                v-if="labels.length && data.length"
                :data="chartData"
                :options="chartOptions"
            />

            <!-- Empty state -->
            <div v-else-if="!loading" class="flex flex-col items-center justify-center h-full text-center">
                <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon || 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'" />
                    </svg>
                </div>
                <p class="text-xs text-gray-500 font-medium">Aucune donnée disponible</p>
            </div>
        </div>
    </div>
</template>
