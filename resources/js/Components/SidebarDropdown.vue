<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    /** Display label for the dropdown trigger */
    label: {
        type: String,
        required: true,
    },
    /** SVG path(s) for the icon */
    icon: {
        type: [String, Array],
        default: '',
    },
    /** Whether the sidebar is collapsed */
    collapsed: {
        type: Boolean,
        default: false,
    },
    /** Start expanded */
    defaultOpen: {
        type: Boolean,
        default: false,
    },
    /** Badge text/count (shown on trigger) */
    badge: {
        type: [String, Number],
        default: null,
    },
    /** Badge color class */
    badgeColor: {
        type: String,
        default: 'bg-red-500 text-white',
    },
    /** Unique key for localStorage persistence */
    storageKey: {
        type: String,
        default: '',
    },
});

const isOpen = ref(props.defaultOpen);

// Persist open/close state
onMounted(() => {
    if (props.storageKey) {
        const saved = localStorage.getItem(`sidebar-dropdown-${props.storageKey}`);
        if (saved !== null) {
            isOpen.value = saved === 'true';
        }
    }
});

const toggle = () => {
    isOpen.value = !isOpen.value;
    if (props.storageKey) {
        localStorage.setItem(`sidebar-dropdown-${props.storageKey}`, isOpen.value);
    }
};

const iconPaths = computed(() => {
    if (Array.isArray(props.icon)) return props.icon;
    return props.icon ? [props.icon] : [];
});

// Auto-expand when any child is active
const hasActiveChild = computed(() => {
    // Checked by the parent via slot, but we provide a sensible default
    return false;
});
</script>

<template>
    <div>
        <!-- Dropdown trigger -->
        <button
            @click="toggle"
            class="relative w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group/dropdown"
            :class="[
                isOpen
                    ? 'text-gray-800 bg-gray-50'
                    : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800',
                collapsed ? 'justify-center' : '',
            ]"
            :title="collapsed ? label : ''"
        >
            <!-- Icon container -->
            <div
                class="flex items-center justify-center w-8 h-8 rounded-lg flex-shrink-0"
                :class="isOpen ? 'bg-primary/10 text-primary' : 'group-hover/dropdown:bg-gray-200/60'"
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
                <span v-show="!collapsed" class="truncate flex-1 text-left">{{ label }}</span>
            </Transition>

            <!-- Badge -->
            <span
                v-if="badge != null && !collapsed"
                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold"
                :class="badgeColor"
            >
                {{ badge }}
            </span>

            <!-- Chevron -->
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-100"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <svg
                    v-show="!collapsed"
                    class="w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0"
                    :class="isOpen ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </Transition>

            <!-- Collapsed badge dot -->
            <span
                v-if="badge != null && collapsed"
                class="absolute top-1 right-1 w-2.5 h-2.5 rounded-full ring-2 ring-white"
                :class="badgeColor.split(' ')[0]"
            ></span>
        </button>

        <!-- Dropdown content (children) -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1 max-h-0"
            enter-to-class="opacity-100 translate-y-0 max-h-96"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0 max-h-96"
            leave-to-class="opacity-0 -translate-y-1 max-h-0"
        >
            <div v-show="isOpen && !collapsed" class="mt-1 ml-4 pl-4 border-l-2 border-gray-100 space-y-0.5 overflow-hidden">
                <slot />
            </div>
        </Transition>

        <!-- Collapsed: show children as tooltip/popover on hover (optional slot) -->
        <div v-if="collapsed" class="relative group/collapsed">
            <!-- Popover on hover when collapsed -->
            <div class="hidden group-hover/collapsed:block absolute left-full top-0 ml-2 py-2 px-1 bg-white rounded-xl shadow-xl border border-gray-200/80 min-w-[200px] z-50">
                <p class="px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ label }}</p>
                <slot name="collapsed-items" />
            </div>
        </div>
    </div>
</template>
