<script setup>
import { inject } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    items: {
        type: Array,
        default: () => [],
        // Each item: { label: string, href?: string, icon?: string }
    },
});

const currentDate = inject('currentDate', '');
</script>

<template>
    <div class="flex items-center justify-between">
        <nav class="flex items-center gap-1.5 text-sm" aria-label="Breadcrumb">
        <!-- Home -->
        <Link
            href="/admin/dashboard"
            class="flex items-center gap-1 text-gray-400 hover:text-primary transition-colors duration-200"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        </Link>

        <template v-for="(item, index) in items" :key="index">
            <!-- Separator -->
            <svg class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>

            <!-- Link item -->
            <Link
                v-if="item.href && index < items.length - 1"
                :href="item.href"
                class="flex items-center gap-1.5 text-gray-400 hover:text-primary font-medium transition-colors duration-200 whitespace-nowrap dark:text-gray-500 dark:hover:text-primary"
            >
                <svg v-if="item.icon" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                </svg>
                {{ item.label }}
            </Link>

            <!-- Current page (last item) -->
            <span
                v-else
                class="flex items-center gap-1.5 text-gray-700 dark:text-gray-200 font-semibold whitespace-nowrap"
                aria-current="page"
            >
                <svg v-if="item.icon" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                </svg>
                {{ item.label }}
            </span>
        </template>
    </nav>
    <span v-if="currentDate" class="text-xs text-gray-400 dark:text-gray-500 capitalize hidden sm:block">{{ currentDate }}</span>
    </div>
</template>
