<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    /** v-model value */
    modelValue: {
        type: String,
        default: '',
    },
    /** Placeholder text */
    placeholder: {
        type: String,
        default: '',
    },
    /** Max width class */
    maxWidth: {
        type: String,
        default: 'max-w-lg',
    },
    /** Show clear button */
    clearable: {
        type: Boolean,
        default: true,
    },
    /** SVG icon path (default: magnifying glass) */
    icon: {
        type: String,
        default: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
    },
    /** Disabled state */
    disabled: {
        type: Boolean,
        default: false,
    },
    /** Size variant */
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
});

const emit = defineEmits(['update:modelValue', 'clear']);

const inputValue = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const clear = () => {
    emit('update:modelValue', '');
    emit('clear');
};

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return { input: 'pl-10 pr-8 py-2 text-xs rounded-xl', icon: 'pl-3 w-4 h-4', clear: 'pr-3 w-4 h-4' };
        case 'lg': return { input: 'pl-14 pr-12 py-4 text-base rounded-2xl', icon: 'pl-5 w-6 h-6', clear: 'pr-5 w-6 h-6' };
        default:   return { input: 'pl-12 pr-10 py-3 text-sm rounded-2xl', icon: 'pl-4 w-5 h-5', clear: 'pr-4 w-5 h-5' };
    }
});
</script>

<template>
    <div class="relative flex-1 group" :class="maxWidth">
        <!-- Search icon -->
        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none" :class="sizeClasses.icon.split(' ')[0]">
            <svg
                class="text-gray-400 group-focus-within:text-primary transition-colors"
                :class="sizeClasses.icon.split(' ').slice(1).join(' ')"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
            </svg>
        </div>

        <!-- Input -->
        <input
            v-model="inputValue"
            type="text"
            :placeholder="placeholder || t('common.search')"
            :disabled="disabled"
            class="block w-full border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-0 focus:border-primary transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="sizeClasses.input"
        />

        <!-- Clear button -->
        <button
            v-if="clearable && modelValue"
            @click="clear"
            class="absolute inset-y-0 right-0 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
            :class="sizeClasses.clear.split(' ')[0]"
        >
            <svg
                class="text-current"
                :class="sizeClasses.clear.split(' ').slice(1).join(' ')"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>
