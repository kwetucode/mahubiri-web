<script setup>
import { watch, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
    message: { type: String, default: '' },
    confirmText: { type: String, default: '' },
    cancelText: { type: String, default: '' },
    variant: { type: String, default: 'danger', validator: (v) => ['danger', 'warning', 'info', 'primary'].includes(v) },
    loading: { type: Boolean, default: false },
    icon: { type: String, default: '' },
    maxWidth: { type: String, default: 'sm', validator: (v) => ['xs', 'sm', 'md', 'lg'].includes(v) },
});

const emit = defineEmits(['confirm', 'cancel', 'update:show']);

const close = () => {
    if (props.loading) return;
    emit('update:show', false);
    emit('cancel');
};

const confirm = () => {
    emit('confirm');
};

// Close on Escape
const handleKeydown = (e) => {
    if (e.key === 'Escape' && props.show) close();
};

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));

// Prevent body scroll when open
watch(() => props.show, (val) => {
    document.body.style.overflow = val ? 'hidden' : '';
});

const variantConfig = {
    danger: {
        iconBg: 'bg-red-100',
        iconColor: 'text-red-600',
        btnClass: 'bg-red-600 hover:bg-red-700 focus:ring-red-500 shadow-red-600/25',
        defaultIcon: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
    },
    warning: {
        iconBg: 'bg-amber-100',
        iconColor: 'text-amber-600',
        btnClass: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500 shadow-amber-600/25',
        defaultIcon: 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
    },
    info: {
        iconBg: 'bg-blue-100',
        iconColor: 'text-blue-600',
        btnClass: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 shadow-blue-600/25',
        defaultIcon: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
    },
    primary: {
        iconBg: 'bg-primary/10',
        iconColor: 'text-primary',
        btnClass: 'bg-primary hover:bg-primary-dark focus:ring-primary shadow-primary/25',
        defaultIcon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
};

const maxWidthClass = {
    xs: 'max-w-xs',
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-100 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
                    @click="close"
                />

                <!-- Modal -->
                <Transition
                    appear
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div
                        v-if="show"
                        class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full overflow-hidden"
                        :class="maxWidthClass[maxWidth]"
                    >
                        <!-- Top accent bar -->
                        <div
                            class="h-1 w-full"
                            :class="variantConfig[variant].btnClass.split(' ')[0]"
                        />

                        <div class="p-6">
                            <!-- Icon + Content -->
                            <div class="flex gap-4">
                                <!-- Icon -->
                                <div
                                    class="flex items-center justify-center w-12 h-12 rounded-xl shrink-0"
                                    :class="variantConfig[variant].iconBg"
                                >
                                    <svg
                                        class="w-6 h-6"
                                        :class="variantConfig[variant].iconColor"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.5"
                                            :d="icon || variantConfig[variant].defaultIcon"
                                        />
                                    </svg>
                                </div>

                                <!-- Text -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ title || t('common.confirmAction') }}</h3>
                                    <p v-if="message" class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                        {{ message }}
                                    </p>
                                    <slot />
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <button
                                    @click="close"
                                    :disabled="loading"
                                    class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 disabled:opacity-50"
                                >
                                    {{ cancelText || t('common.cancel') }}
                                </button>
                                <button
                                    @click="confirm"
                                    :disabled="loading"
                                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-lg transition-all duration-200 disabled:opacity-70 flex items-center gap-2"
                                    :class="variantConfig[variant].btnClass"
                                >
                                    <svg
                                        v-if="loading"
                                        class="animate-spin w-4 h-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ confirmText || t('common.confirm') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
