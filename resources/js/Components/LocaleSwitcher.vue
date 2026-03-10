<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { setLocale, availableLocales, flagUrl } from '@/i18n';

const { locale } = useI18n();
const open = ref(false);
const dropdownRef = ref(null);

const currentLocale = () => availableLocales.find(l => l.code === locale.value) || availableLocales[0];

const switchLocale = (code) => {
    setLocale(code);
    open.value = false;
};

const handleClickOutside = (e) => {
    if (open.value && dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <!-- Trigger -->
        <button
            @click="open = !open"
            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            :title="currentLocale().label"
        >
            <img :src="flagUrl(currentLocale().country)" :alt="currentLocale().label" class="w-5 h-3.5 rounded-sm object-cover">
            <span class="hidden sm:inline">{{ currentLocale().code.toUpperCase() }}</span>
            <svg class="w-3 h-3 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95 -translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                class="absolute right-0 mt-1.5 w-44 bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10 overflow-hidden z-50"
            >
                <div class="py-1">
                    <button
                        v-for="loc in availableLocales"
                        :key="loc.code"
                        @click="switchLocale(loc.code)"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-medium transition-colors"
                        :class="locale === loc.code
                            ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light'
                            : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                    >
                        <img :src="flagUrl(loc.country)" :alt="loc.label" class="w-5 h-3.5 rounded-sm object-cover">
                        <span>{{ loc.label }}</span>
                        <svg v-if="locale === loc.code" class="w-3.5 h-3.5 ml-auto text-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
