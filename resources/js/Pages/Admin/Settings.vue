<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { useTheme } from '@/composables/useTheme';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { setLocale, availableLocales } from '@/i18n';

const { t, locale } = useI18n();
const { theme, setTheme } = useTheme();

const themes = computed(() => [
    {
        value: 'light',
        label: t('settings.light'),
        description: t('settings.lightDesc'),
        icon: 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
        preview: 'bg-white border-gray-200',
        previewBar: 'bg-gray-100',
        previewText: 'bg-gray-300',
    },
    {
        value: 'dark',
        label: t('settings.dark'),
        description: t('settings.darkDesc'),
        icon: 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z',
        preview: 'bg-gray-900 border-gray-700',
        previewBar: 'bg-gray-800',
        previewText: 'bg-gray-600',
    },
]);

// Notification preferences (local)
const notifSound = ref(localStorage.getItem('mahubiri-notif-sound') !== 'false');
const notifDesktop = ref(localStorage.getItem('mahubiri-notif-desktop') === 'true');

const toggleNotifSound = () => {
    notifSound.value = !notifSound.value;
    localStorage.setItem('mahubiri-notif-sound', notifSound.value);
};

const toggleNotifDesktop = () => {
    notifDesktop.value = !notifDesktop.value;
    localStorage.setItem('mahubiri-notif-desktop', notifDesktop.value);
    if (notifDesktop.value && Notification.permission === 'default') {
        Notification.requestPermission();
    }
};

// Language
const languages = availableLocales.map(l => ({ value: l.code, label: l.label, flag: l.flag }));
const changeLanguage = (lang) => {
    setLocale(lang);
};

const sections = computed(() => [
    { id: 'appearance', label: t('settings.appearance'), icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01' },
    { id: 'notifications', label: t('settings.notificationsSection'), icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
    { id: 'language', label: t('settings.languageSection'), icon: 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129' },
]);

const activeSection = ref('appearance');
</script>

<template>
    <AdminLayout :title="t('settings.title')">
        <div class="space-y-6">
            <Breadcrumb :items="[{ label: t('nav.dashboard'), href: '/admin/dashboard' }, { label: t('settings.title') }]" />

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar navigation -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-4 border-b border-gray-50 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ t('settings.title') }}</h3>
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ t('settings.subtitle') }}</p>
                        </div>
                        <nav class="p-2 space-y-0.5">
                            <button
                                v-for="section in sections"
                                :key="section.id"
                                @click="activeSection = section.id"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                                :class="activeSection === section.id
                                    ? 'bg-primary/10 text-primary dark:bg-primary/20'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700/50 dark:hover:text-gray-300'"
                            >
                                <svg class="w-4.5 h-4.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="section.icon" />
                                </svg>
                                {{ section.label }}
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Content -->
                <div class="lg:col-span-3 space-y-5">
                    <!-- Appearance -->
                    <Transition
                        enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-150"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                        mode="out-in"
                    >
                        <div v-if="activeSection === 'appearance'" key="appearance" class="space-y-5">
                            <!-- Theme selector -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ t('settings.theme') }}</h3>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ t('settings.themeSubtitle') }}</p>
                                </div>
                                <div class="p-5">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <button
                                            v-for="themeOption in themes"
                                            :key="themeOption.value"
                                            @click="setTheme(themeOption.value)"
                                            class="relative flex flex-col rounded-xl border-2 p-4 transition-all duration-200 hover:shadow-md"
                                            :class="theme === themeOption.value
                                                ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-sm'
                                                : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                        >
                                            <!-- Check badge -->
                                            <div
                                                v-if="theme === themeOption.value"
                                                class="absolute top-3 right-3 w-5 h-5 rounded-full bg-primary flex items-center justify-center"
                                            >
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>

                                            <!-- Preview mockup -->
                                            <div class="rounded-lg border overflow-hidden mb-3" :class="themeOption.preview">
                                                <div class="h-5 flex items-center gap-1 px-2" :class="themeOption.previewBar">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                                                </div>
                                                <div class="flex h-16">
                                                    <div class="w-10 border-r" :class="themeOption.previewBar">
                                                        <div class="space-y-1 p-1.5">
                                                            <div class="h-1.5 rounded-full" :class="themeOption.previewText"></div>
                                                            <div class="h-1.5 w-4/5 rounded-full" :class="themeOption.previewText"></div>
                                                            <div class="h-1.5 w-3/5 rounded-full" :class="themeOption.previewText"></div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 p-2 space-y-1.5">
                                                        <div class="h-2 w-3/4 rounded" :class="themeOption.previewText"></div>
                                                        <div class="flex gap-1">
                                                            <div class="h-6 flex-1 rounded" :class="themeOption.previewBar"></div>
                                                            <div class="h-6 flex-1 rounded" :class="themeOption.previewBar"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Label -->
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg" :class="theme === themeOption.value ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="themeOption.icon" />
                                                    </svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="text-sm font-semibold" :class="theme === themeOption.value ? 'text-primary' : 'text-gray-700 dark:text-gray-300'">{{ themeOption.label }}</p>
                                                    <p class="text-[11px] text-gray-400">{{ themeOption.description }}</p>
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div v-else-if="activeSection === 'notifications'" key="notifications" class="space-y-5">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ t('settings.notifications') }}</h3>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ t('settings.notificationsSubtitle') }}</p>
                                </div>
                                <div class="divide-y divide-gray-50 dark:divide-gray-700">
                                    <!-- Sound -->
                                    <div class="flex items-center justify-between px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('settings.notificationSound') }}</p>
                                                <p class="text-[11px] text-gray-400">{{ t('settings.notificationSoundDesc') }}</p>
                                            </div>
                                        </div>
                                        <button
                                            @click="toggleNotifSound"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                                            :class="notifSound ? 'bg-primary' : 'bg-gray-300 dark:bg-gray-600'"
                                        >
                                            <span
                                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm"
                                                :class="notifSound ? 'translate-x-6' : 'translate-x-1'"
                                            />
                                        </button>
                                    </div>

                                    <!-- Desktop notifications -->
                                    <div class="flex items-center justify-between px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('settings.desktopNotifications') }}</p>
                                                <p class="text-[11px] text-gray-400">{{ t('settings.desktopNotificationsDesc') }}</p>
                                            </div>
                                        </div>
                                        <button
                                            @click="toggleNotifDesktop"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                                            :class="notifDesktop ? 'bg-primary' : 'bg-gray-300 dark:bg-gray-600'"
                                        >
                                            <span
                                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 shadow-sm"
                                                :class="notifDesktop ? 'translate-x-6' : 'translate-x-1'"
                                            />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Language -->
                        <div v-else-if="activeSection === 'language'" key="language" class="space-y-5">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700">
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ t('settings.language') }}</h3>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ t('settings.languageSubtitle') }}</p>
                                </div>
                                <div class="p-5 space-y-2">
                                    <button
                                        v-for="lang in languages"
                                        :key="lang.value"
                                        @click="changeLanguage(lang.value)"
                                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-all duration-200"
                                        :class="locale === lang.value
                                            ? 'border-primary bg-primary/5 dark:bg-primary/10'
                                            : 'border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    >
                                        <span class="text-xl">{{ lang.flag }}</span>
                                        <span class="text-sm font-medium" :class="locale === lang.value ? 'text-primary' : 'text-gray-700 dark:text-gray-300'">{{ lang.label }}</span>
                                        <svg
                                            v-if="locale === lang.value"
                                            class="w-4 h-4 text-primary ml-auto"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>

                                    <p class="text-[11px] text-gray-400 mt-3 px-1">
                                        <svg class="w-3.5 h-3.5 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ t('settings.multilingualNote') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
