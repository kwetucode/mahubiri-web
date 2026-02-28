<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    size: { type: String, default: 'md', validator: (v) => ['sm', 'md', 'lg'].includes(v) },
    color: { type: String, default: 'primary' },
    label: { type: String, default: '' },
    labelPosition: { type: String, default: 'right', validator: (v) => ['left', 'right'].includes(v) },
    activeText: { type: String, default: '' },
    inactiveText: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'change']);

const toggle = () => {
    if (props.disabled || props.loading) return;
    const newVal = !props.modelValue;
    emit('update:modelValue', newVal);
    emit('change', newVal);
};

const sizeConfig = computed(() => {
    switch (props.size) {
        case 'sm': return { track: 'w-8 h-[18px]', thumb: 'w-3.5 h-3.5', translate: 'translate-x-3.5', padding: 'p-[2px]' };
        case 'lg': return { track: 'w-14 h-8', thumb: 'w-6 h-6', translate: 'translate-x-6', padding: 'p-1' };
        default:   return { track: 'w-11 h-6', thumb: 'w-5 h-5', translate: 'translate-x-5', padding: 'p-0.5' };
    }
});

const colorClasses = computed(() => {
    const colors = {
        primary: 'bg-primary shadow-primary/30',
        emerald: 'bg-emerald-500 shadow-emerald-500/30',
        blue: 'bg-blue-500 shadow-blue-500/30',
        red: 'bg-red-500 shadow-red-500/30',
        amber: 'bg-amber-500 shadow-amber-500/30',
    };
    return colors[props.color] || colors.primary;
});

const displayText = computed(() => {
    if (props.modelValue && props.activeText) return props.activeText;
    if (!props.modelValue && props.inactiveText) return props.inactiveText;
    return props.label;
});
</script>

<template>
    <label
        class="inline-flex items-center gap-2.5 select-none"
        :class="[
            disabled || loading ? 'cursor-not-allowed opacity-60' : 'cursor-pointer',
            labelPosition === 'left' ? 'flex-row-reverse' : '',
        ]"
        @click.prevent="toggle"
    >
        <!-- Track -->
        <span
            class="relative inline-flex shrink-0 rounded-full transition-all duration-300 ease-in-out"
            :class="[
                sizeConfig.track,
                sizeConfig.padding,
                modelValue ? colorClasses + ' shadow-sm' : 'bg-gray-200',
            ]"
        >
            <!-- Thumb -->
            <span
                class="inline-flex items-center justify-center rounded-full bg-white shadow-md transition-all duration-300 ease-in-out"
                :class="[
                    sizeConfig.thumb,
                    modelValue ? sizeConfig.translate : 'translate-x-0',
                ]"
            >
                <!-- Loading spinner -->
                <svg
                    v-if="loading"
                    class="animate-spin text-gray-400"
                    :class="size === 'sm' ? 'w-2 h-2' : size === 'lg' ? 'w-3.5 h-3.5' : 'w-2.5 h-2.5'"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <!-- Check icon when active -->
                <svg
                    v-else-if="modelValue"
                    class="text-primary"
                    :class="size === 'sm' ? 'w-2 h-2' : size === 'lg' ? 'w-3.5 h-3.5' : 'w-2.5 h-2.5'"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </span>
        </span>

        <!-- Label -->
        <span
            v-if="displayText"
            class="text-sm font-medium transition-colors duration-200"
            :class="modelValue ? 'text-gray-900' : 'text-gray-500'"
        >
            {{ displayText }}
        </span>
    </label>
</template>
