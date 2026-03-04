<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const loading = ref(false);
const progress = ref(0);
let progressInterval = null;

const startLoading = () => {
    loading.value = true;
    progress.value = 0;

    // Animate progress bar
    progressInterval = setInterval(() => {
        if (progress.value < 90) {
            progress.value += Math.random() * 12 + 3;
            if (progress.value > 90) progress.value = 90;
        }
    }, 200);
};

const finishLoading = () => {
    if (progressInterval) {
        clearInterval(progressInterval);
        progressInterval = null;
    }
    progress.value = 100;
    setTimeout(() => {
        loading.value = false;
        progress.value = 0;
    }, 300);
};

let removeStart, removeFinish;

onMounted(() => {
    removeStart = router.on('start', startLoading);
    removeFinish = router.on('finish', finishLoading);
});

onUnmounted(() => {
    if (removeStart) removeStart();
    if (removeFinish) removeFinish();
    if (progressInterval) clearInterval(progressInterval);
});
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-150"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="loading" class="page-loader-overlay">
            <!-- Top progress bar -->
            <div class="page-loader-bar">
                <div class="page-loader-bar-fill" :style="{ width: progress + '%' }"></div>
            </div>

            <!-- Center spinner -->
            <div class="page-loader-center">
                <div class="page-loader-spinner">
                    <svg viewBox="0 0 40 40" fill="none">
                        <circle cx="20" cy="20" r="16" stroke="currentColor" stroke-width="3" opacity=".15" />
                        <path
                            d="M36 20a16 16 0 01-16 16"
                            stroke="currentColor"
                            stroke-width="3"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>
                <p class="page-loader-text">Chargement...</p>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.page-loader-overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    background: rgba(249, 250, 251, 0.55);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    pointer-events: all;
}

:root.dark .page-loader-overlay {
    background: rgba(17, 24, 39, 0.55);
}

/* Top bar */
.page-loader-bar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: rgba(107, 78, 175, 0.1);
    overflow: hidden;
}

.page-loader-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #6B4EAF, #8B6FCF, #6B4EAF);
    background-size: 200% 100%;
    border-radius: 0 2px 2px 0;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: shimmer 1.5s ease-in-out infinite;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Center */
.page-loader-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
}

.page-loader-spinner {
    width: 40px;
    height: 40px;
    color: #6B4EAF;
    animation: spin 0.8s linear infinite;
}

.page-loader-spinner svg {
    width: 100%;
    height: 100%;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.page-loader-text {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    letter-spacing: 0.3px;
}

:root.dark .page-loader-text {
    color: #9ca3af;
}
</style>
