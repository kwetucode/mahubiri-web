<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const toasts = ref([]);
let idCounter = 0;

const addToast = (message, type = 'success') => {
    if (!message) return;
    const id = ++idCounter;
    toasts.value.push({ id, message, type, visible: false });
    // Trigger enter animation on next tick
    setTimeout(() => {
        const t = toasts.value.find(t => t.id === id);
        if (t) t.visible = true;
    }, 10);
    // Auto dismiss after 5s
    setTimeout(() => removeToast(id), 5000);
};

const removeToast = (id) => {
    const t = toasts.value.find(t => t.id === id);
    if (t) {
        t.visible = false;
        setTimeout(() => {
            toasts.value = toasts.value.filter(t => t.id !== id);
        }, 300);
    }
};

// Watch for flash messages from Inertia shared props
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) addToast(flash.success, 'success');
        if (flash?.error) addToast(flash.error, 'error');
    },
    { immediate: true, deep: true }
);

const icons = {
    success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    error: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};

const styles = {
    success: {
        bg: 'bg-emerald-50 border-emerald-200',
        icon: 'text-emerald-500',
        text: 'text-emerald-800',
        bar: 'bg-emerald-500',
    },
    error: {
        bg: 'bg-red-50 border-red-200',
        icon: 'text-red-500',
        text: 'text-red-800',
        bar: 'bg-red-500',
    },
};

// Expose addToast for programmatic use
defineExpose({ addToast });
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-3 max-w-sm w-full pointer-events-none">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto transform transition-all duration-300 ease-out"
                :class="toast.visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'"
            >
                <div
                    class="relative overflow-hidden rounded-xl border shadow-lg"
                    :class="styles[toast.type].bg"
                >
                    <div class="flex items-start gap-3 px-4 py-3.5">
                        <svg
                            class="w-5 h-5 shrink-0 mt-0.5"
                            :class="styles[toast.type].icon"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons[toast.type]" />
                        </svg>
                        <p class="text-sm font-medium flex-1" :class="styles[toast.type].text">
                            {{ toast.message }}
                        </p>
                        <button
                            @click="removeToast(toast.id)"
                            class="shrink-0 p-0.5 rounded-lg hover:bg-black/5 transition-colors"
                            :class="styles[toast.type].text"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <!-- Auto-dismiss progress bar -->
                    <div class="h-0.5 w-full bg-black/5">
                        <div
                            class="h-full transition-all ease-linear"
                            :class="styles[toast.type].bar"
                            :style="{ width: toast.visible ? '0%' : '100%', transitionDuration: toast.visible ? '5000ms' : '0ms' }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
