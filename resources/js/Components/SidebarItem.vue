<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    /** Route path */
    href: {
        type: String,
        required: true,
    },
    /** Display label */
    label: {
        type: String,
        required: true,
    },
    /** SVG path(s) for the icon — string or array of strings */
    icon: {
        type: [String, Array],
        default: '',
    },
    /** Whether the sidebar is collapsed */
    collapsed: {
        type: Boolean,
        default: false,
    },
    /** Badge text/count */
    badge: {
        type: [String, Number],
        default: null,
    },
    /** Badge color class */
    badgeColor: {
        type: String,
        default: 'bg-red-500 text-white',
    },
});

const currentPath = computed(() => window.location.pathname);

const isActive = computed(() => {
    return currentPath.value === props.href || currentPath.value.startsWith(props.href + '/');
});

const iconPaths = computed(() => {
    if (Array.isArray(props.icon)) return props.icon;
    return props.icon ? [props.icon] : [];
});
</script>

<template>
    <Link
        :href="href"
        class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group/item"
        :class="[
            isActive
                ? 'bg-gradient-to-r from-primary/10 to-primary/5 text-primary shadow-sm'
                : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800',
            collapsed ? 'justify-center' : ''
        ]"
        :title="collapsed ? label : ''"
    >
        <!-- Active indicator -->
        <div
            v-if="isActive"
            class="absolute -left-3 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary rounded-r-full"
        ></div>

        <!-- Icon container -->
        <div
            class="flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0"
            :class="isActive ? 'bg-primary/10' : 'group-hover/item:bg-gray-200/60'"
        >
            <slot name="icon">
                <svg
                    v-if="iconPaths.length"
                    class="w-[18px] h-[18px]"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                    <path
                        v-for="(d, i) in iconPaths"
                        :key="i"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="d"
                    />
                </svg>
            </slot>
        </div>

        <!-- Label -->
        <Transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <span v-show="!collapsed" class="truncate flex-1">{{ label }}</span>
        </Transition>

        <!-- Badge -->
        <Transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0 scale-75"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 scale-75"
        >
            <span
                v-if="badge != null && !collapsed"
                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold"
                :class="badgeColor"
            >
                {{ badge }}
            </span>
        </Transition>

        <!-- Collapsed badge dot -->
        <span
            v-if="badge != null && collapsed"
            class="absolute top-1 right-1 w-2.5 h-2.5 rounded-full ring-2 ring-white"
            :class="badgeColor.split(' ')[0]"
        ></span>
    </Link>
</template>
