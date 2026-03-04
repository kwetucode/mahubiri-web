<script setup>
defineProps({
    /** Card title */
    title: {
        type: String,
        default: '',
    },
    /** Card subtitle */
    subtitle: {
        type: String,
        default: '',
    },
    /** Remove default padding */
    noPadding: {
        type: Boolean,
        default: false,
    },
    /** Add border to card */
    bordered: {
        type: Boolean,
        default: true,
    },
    /** Dark variant (gradient dark bg) */
    dark: {
        type: Boolean,
        default: false,
    },
    /** Custom class for overrides */
    bodyClass: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div
        class="rounded-xl overflow-hidden"
        :class="[
            dark
                ? 'bg-linear-to-br from-gray-900 to-gray-800 text-white'
                : 'bg-white dark:bg-gray-800',
            bordered && !dark ? 'border border-gray-200/80 dark:border-gray-700' : '',
        ]"
    >
        <!-- Header (title/subtitle + actions slot) -->
        <div
            v-if="title || $slots.header"
            class="flex items-center justify-between px-5 py-3.5"
            :class="dark ? 'border-b border-white/10' : 'border-b border-gray-100 dark:border-gray-700'"
        >
            <slot name="header">
                <div>
                    <h3
                        class="text-sm font-bold"
                        :class="dark ? 'text-white/90' : 'text-gray-900 dark:text-gray-100'"
                    >
                        {{ title }}
                    </h3>
                    <p
                        v-if="subtitle"
                        class="text-[11px] mt-0.5"
                        :class="dark ? 'text-white/40' : 'text-gray-400 dark:text-gray-500'"
                    >
                        {{ subtitle }}
                    </p>
                </div>
            </slot>
            <slot name="header-actions" />
        </div>

        <!-- Body -->
        <div
            :class="[
                noPadding ? '' : 'px-5 py-4',
                bodyClass,
            ]"
        >
            <slot />
        </div>

        <!-- Footer -->
        <div v-if="$slots.footer">
            <slot name="footer" />
        </div>
    </div>
</template>
