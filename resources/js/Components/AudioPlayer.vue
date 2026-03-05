<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    src: { type: String, default: null },
    title: { type: String, default: 'Prédication' },
    preacher: { type: String, default: '' },
    coverUrl: { type: String, default: null },
});

const emit = defineEmits(['close']);

const audio = ref(null);
const isPlaying = ref(false);
const isLoading = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const volume = ref(1);
const isMuted = ref(false);
const hasError = ref(false);
const errorMessage = ref('');
const isSeeking = ref(false);
const seekBar = ref(null);

// Format time (seconds → mm:ss)
const formatTime = (secs) => {
    if (!secs || !isFinite(secs)) return '0:00';
    const m = Math.floor(secs / 60);
    const s = Math.floor(secs % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
};

const formattedCurrent = computed(() => formatTime(currentTime.value));
const formattedDuration = computed(() => formatTime(duration.value));
const progress = computed(() => (duration.value > 0 ? (currentTime.value / duration.value) * 100 : 0));

// Audio controls
const togglePlay = () => {
    if (!audio.value) return;
    if (isPlaying.value) {
        audio.value.pause();
    } else {
        audio.value.play().catch(() => {});
    }
};

const seekFromEvent = (e, el) => {
    if (!audio.value || !duration.value) return;
    const rect = el.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const pct = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
    const newTime = pct * duration.value;
    currentTime.value = newTime;
    audio.value.currentTime = newTime;
};

const onSeekStart = (e) => {
    isSeeking.value = true;
    seekFromEvent(e, seekBar.value);
    const onMove = (ev) => seekFromEvent(ev, seekBar.value);
    const onEnd = (ev) => {
        if (ev.type === 'mouseup') seekFromEvent(ev, seekBar.value);
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', onEnd);
        document.removeEventListener('touchmove', onMove);
        document.removeEventListener('touchend', onEnd);
        // Wait for the audio element to finish seeking before unlocking timeupdate
        if (audio.value && audio.value.seeking) {
            const onSeeked = () => {
                isSeeking.value = false;
                audio.value.removeEventListener('seeked', onSeeked);
            };
            audio.value.addEventListener('seeked', onSeeked);
        } else {
            isSeeking.value = false;
        }
    };
    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup', onEnd);
    document.addEventListener('touchmove', onMove, { passive: true });
    document.addEventListener('touchend', onEnd);
};

const skip = (seconds) => {
    if (!audio.value) return;
    audio.value.currentTime = Math.max(0, Math.min(duration.value, audio.value.currentTime + seconds));
};

const toggleMute = () => {
    if (!audio.value) return;
    isMuted.value = !isMuted.value;
    audio.value.muted = isMuted.value;
};

const onPlay = () => { isPlaying.value = true; isLoading.value = false; };
const onPause = () => { isPlaying.value = false; };
const onTimeUpdate = () => { if (audio.value && !isSeeking.value && !audio.value.seeking) currentTime.value = audio.value.currentTime; };
const onLoadedMetadata = () => { if (audio.value) { duration.value = audio.value.duration; isLoading.value = false; } };
const onWaiting = () => { isLoading.value = true; };
const onCanPlay = () => { isLoading.value = false; };
const onEnded = () => { isPlaying.value = false; currentTime.value = 0; };
const onError = () => {
    isLoading.value = false;
    isPlaying.value = false;
    hasError.value = true;
    errorMessage.value = 'Impossible de lire ce fichier audio.';
};

const close = () => {
    if (audio.value) {
        audio.value.pause();
        audio.value.src = '';
    }
    emit('close');
};

// Watch src change → reset & load
watch(() => props.src, (newSrc) => {
    hasError.value = false;
    errorMessage.value = '';
    currentTime.value = 0;
    duration.value = 0;
    isPlaying.value = false;
    if (newSrc && audio.value) {
        isLoading.value = true;
        audio.value.load();
        audio.value.play().catch(() => {});
    }
}, { immediate: false });

onBeforeUnmount(() => {
    if (audio.value) {
        audio.value.pause();
    }
});
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-if="src"
            class="fixed bottom-0 inset-x-0 z-50"
        >
            <!-- Backdrop blur layer -->
            <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border-t border-gray-200/80 dark:border-gray-700/80 shadow-[0_-4px_20px_rgba(0,0,0,0.08)]">
                <!-- Seekbar row -->
                <div class="max-w-7xl mx-auto px-4 pt-3 pb-0.5">
                    <div class="flex items-center gap-3">
                        <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400 tabular-nums w-10 text-right shrink-0">{{ formattedCurrent }}</span>
                        <div
                            ref="seekBar"
                            class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full cursor-pointer group relative select-none touch-none"
                            :class="{ 'h-2.5': isSeeking }"
                            @mousedown.prevent="onSeekStart"
                            @touchstart.prevent="onSeekStart"
                        >
                            <div
                                class="h-full bg-linear-to-r from-primary to-primary/80 rounded-full relative"
                                :class="isSeeking ? '' : 'transition-[width] duration-150'"
                                :style="{ width: progress + '%' }"
                            >
                                <!-- Knob -->
                                <div
                                    class="absolute right-0 top-1/2 -translate-y-1/2 rounded-full bg-primary ring-2 ring-white dark:ring-gray-900 shadow-md transition-all"
                                    :class="isSeeking ? 'w-4 h-4 scale-110' : 'w-3.5 h-3.5 group-hover:scale-110'"
                                ></div>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500 tabular-nums w-10 shrink-0">{{ formattedDuration }}</span>
                    </div>
                </div>

                <div class="max-w-7xl mx-auto px-4 py-2 flex items-center gap-4">
                    <!-- Cover / Icon -->
                    <div class="shrink-0">
                        <div
                            v-if="coverUrl"
                            class="w-11 h-11 rounded-xl overflow-hidden ring-1 ring-gray-200/60 dark:ring-gray-700/60 shadow-sm"
                        >
                            <img :src="coverUrl" :alt="title" class="w-full h-full object-cover" />
                        </div>
                        <div
                            v-else
                            class="w-11 h-11 rounded-xl bg-linear-to-br from-primary/15 to-primary/5 flex items-center justify-center shadow-sm"
                        >
                            <svg class="w-5 h-5 text-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                    </div>

                    <!-- Title + preacher -->
                    <div class="min-w-0 flex-1 hidden sm:block">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate leading-tight">{{ title }}</p>
                        <p v-if="preacher" class="text-[11px] text-gray-400 dark:text-gray-500 truncate mt-0.5">{{ preacher }}</p>
                    </div>

                    <!-- Controls -->
                    <div class="flex items-center gap-1.5">
                        <!-- Rewind 10s -->
                        <button
                            @click="skip(-10)"
                            class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            title="Reculer 10s"
                        >
                            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z" />
                            </svg>
                        </button>

                        <!-- Play / Pause / Loading -->
                        <button
                            @click="togglePlay"
                            :disabled="hasError"
                            class="relative w-10 h-10 rounded-full flex items-center justify-center transition-all"
                            :class="hasError
                                ? 'bg-red-100 dark:bg-red-900/30 text-red-500 cursor-not-allowed'
                                : 'bg-primary text-white shadow-lg shadow-primary/30 hover:shadow-primary/40 hover:scale-105 active:scale-95'"
                        >
                            <!-- Loading spinner -->
                            <svg v-if="isLoading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <!-- Error icon -->
                            <svg v-else-if="hasError" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <!-- Pause -->
                            <svg v-else-if="isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                            </svg>
                            <!-- Play -->
                            <svg v-else class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </button>

                        <!-- Forward 10s -->
                        <button
                            @click="skip(10)"
                            class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            title="Avancer 10s"
                        >
                            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.933 11.2a1 1 0 010 1.6L.6 16.8A1 1 0 010 16V8a1 1 0 011.6-.8l5.333 4zm8 0a1 1 0 010 1.6l-5.333 4A1 1 0 017 16V8a1 1 0 011.6-.8l5.333 4z" transform="translate(3)" />
                            </svg>
                        </button>
                    </div>

                    <!-- Volume (desktop) -->
                    <div class="hidden lg:flex items-center gap-1.5">
                        <button
                            @click="toggleMute"
                            class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                        >
                            <svg v-if="isMuted || volume === 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            </svg>
                        </button>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.05"
                            v-model.number="volume"
                            @input="() => { if (audio) audio.volume = volume; isMuted = volume === 0; }"
                            class="w-20 h-1 accent-primary cursor-pointer"
                        />
                    </div>

                    <!-- Close -->
                    <button
                        @click="close"
                        class="p-2 rounded-lg text-gray-400 dark:text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors ml-1"
                        title="Fermer le lecteur"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Error message -->
                <div v-if="hasError" class="px-4 pb-2 max-w-7xl mx-auto">
                    <p class="text-xs text-red-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ errorMessage }}
                    </p>
                </div>
            </div>

            <!-- Hidden audio element -->
            <audio
                ref="audio"
                :src="src"
                preload="metadata"
                @play="onPlay"
                @pause="onPause"
                @timeupdate="onTimeUpdate"
                @loadedmetadata="onLoadedMetadata"
                @waiting="onWaiting"
                @canplay="onCanPlay"
                @ended="onEnded"
                @error="onError"
            ></audio>
        </div>
    </Transition>
</template>
