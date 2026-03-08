<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { useTheme } from '@/composables/useTheme';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePage } from '@inertiajs/vue3';
import { setLocale, availableLocales } from '@/i18n';
import axios from 'axios';

const { t, locale } = useI18n();
const { theme, setTheme } = useTheme();
const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

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

const sections = computed(() => {
    const items = [
        { id: 'appearance', label: t('settings.appearance'), icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01' },
        { id: 'notifications', label: t('settings.notificationsSection'), icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
        { id: 'language', label: t('settings.languageSection'), icon: 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129' },
    ];
    if (isAdmin.value) {
        items.push({ id: 'storage', label: t('settings.storageSection'), icon: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4' });
    }
    return items;
});

const activeSection = ref('appearance');

// Storage cleanup (super admin only)
const storageAnalysis = ref(null);
const storageLoading = ref(false);
const storageError = ref('');
const selectedOrphans = ref([]);
const cleaning = ref(false);
const cleanupResult = ref(null);
const scanProgress = ref({ percent: 0, step: '', scanned: 0, total_files: 0, orphans_found: 0, duplicates_found: 0 });

const formatBytes = (bytes) => {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const progressStepLabel = computed(() => {
    const s = scanProgress.value.step;
    if (s === 'db') return t('settings.progressDb');
    if (s === 'listing') return t('settings.progressListing');
    if (s === 'scanning') return t('settings.progressScanning', { scanned: scanProgress.value.scanned, total: scanProgress.value.total_files });
    if (s === 'duplicates') return t('settings.progressDuplicates');
    if (s === 'finalizing') return t('settings.progressFinalizing');
    if (s === 'done') return t('settings.progressDone');
    return '';
});

const analyzeStorage = (resetCleanup = true) => {
    storageLoading.value = true;
    storageError.value = '';
    if (resetCleanup) cleanupResult.value = null;
    if (resetCleanup) cleanupFolderResult.value = null;
    storageAnalysis.value = null;
    browseData.value = null;
    browsePath.value = '';
    selectedEmptyFolders.value = [];
    scanProgress.value = { percent: 0, step: '', scanned: 0, total_files: 0, orphans_found: 0, duplicates_found: 0 };

    const evtSource = new EventSource('/admin/storage-cleanup/analyze');

    evtSource.addEventListener('progress', (e) => {
        const data = JSON.parse(e.data);
        scanProgress.value = { ...scanProgress.value, ...data };
    });

    evtSource.addEventListener('result', (e) => {
        storageAnalysis.value = JSON.parse(e.data);
        selectedOrphans.value = [];
    });

    evtSource.addEventListener('error', () => {
        evtSource.close();
        if (!storageAnalysis.value) {
            storageError.value = t('settings.analyzeError');
        }
        storageLoading.value = false;
    });

    // EventSource auto-reconnects, close once done
    const checkDone = setInterval(() => {
        if (scanProgress.value.step === 'done') {
            evtSource.close();
            storageLoading.value = false;
            clearInterval(checkDone);
        }
    }, 200);
};

const toggleAllOrphans = () => {
    if (!storageAnalysis.value) return;
    if (selectedOrphans.value.length === storageAnalysis.value.orphan_files.length) {
        selectedOrphans.value = [];
    } else {
        selectedOrphans.value = storageAnalysis.value.orphan_files.map(f => f.path);
    }
};

const selectedOrphanSize = computed(() => {
    if (!storageAnalysis.value) return 0;
    return storageAnalysis.value.orphan_files
        .filter(f => selectedOrphans.value.includes(f.path))
        .reduce((sum, f) => sum + f.size, 0);
});

const cleanupFiles = async () => {
    if (!selectedOrphans.value.length) return;
    cleaning.value = true;
    cleanupResult.value = null;
    try {
        const { data } = await axios.post('/admin/storage-cleanup', {
            files: selectedOrphans.value,
        });
        cleanupResult.value = data;
        // Refresh analysis (keep cleanup result visible)
        await analyzeStorage(false);
    } catch (e) {
        storageError.value = e.response?.data?.message || e.message;
    } finally {
        cleaning.value = false;
    }
};

// Folder browsing
const browsePath = ref('');
const browseData = ref(null);
const browseLoading = ref(false);

const breadcrumbParts = computed(() => {
    if (!browsePath.value) return [];
    return browsePath.value.split('/');
});

const browseFolder = async (path = '') => {
    browseLoading.value = true;
    try {
        const { data } = await axios.get('/admin/storage-cleanup/browse', { params: { path } });
        browsePath.value = path;
        browseData.value = data;
    } catch (e) {
        storageError.value = e.response?.data?.message || e.message;
    } finally {
        browseLoading.value = false;
    }
};

const navigateToBreadcrumb = (index) => {
    if (index < 0) {
        browseData.value = null;
        browsePath.value = '';
        return;
    }
    const parts = browsePath.value.split('/');
    const newPath = parts.slice(0, index + 1).join('/');
    browseFolder(newPath);
};

// Empty folders cleanup
const selectedEmptyFolders = ref([]);
const cleaningFolders = ref(false);
const cleanupFolderResult = ref(null);

const toggleAllEmptyFolders = () => {
    if (!storageAnalysis.value) return;
    if (selectedEmptyFolders.value.length === storageAnalysis.value.empty_folders.length) {
        selectedEmptyFolders.value = [];
    } else {
        selectedEmptyFolders.value = [...storageAnalysis.value.empty_folders];
    }
};

const cleanupEmptyFolders = async () => {
    if (!selectedEmptyFolders.value.length) return;
    cleaningFolders.value = true;
    cleanupFolderResult.value = null;
    try {
        const { data } = await axios.post('/admin/storage-cleanup/folders', {
            folders: selectedEmptyFolders.value,
        });
        cleanupFolderResult.value = data;
        await analyzeStorage(false);
    } catch (e) {
        storageError.value = e.response?.data?.message || e.message;
    } finally {
        cleaningFolders.value = false;
    }
};
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

                        <!-- Storage Cleanup (super admin only) -->
                        <div v-else-if="activeSection === 'storage' && isAdmin" key="storage" class="space-y-5">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                                <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ t('settings.storageTitle') }}</h3>
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ t('settings.storageSubtitle') }}</p>
                                    </div>
                                    <button
                                        @click="analyzeStorage"
                                        :disabled="storageLoading"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg text-white bg-primary hover:bg-primary/90 transition disabled:opacity-50"
                                    >
                                        <svg v-if="storageLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        {{ storageLoading ? t('settings.analyzing') : t('settings.analyzeBtn') }}
                                    </button>
                                </div>

                                <!-- No analysis yet -->
                                <div v-if="!storageAnalysis && !storageLoading && !storageError" class="p-8 text-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('settings.analyzePrompt') }}</p>
                                </div>

                                <!-- Scan progress -->
                                <div v-if="storageLoading" class="p-6 space-y-4">
                                    <!-- Progress bar -->
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ progressStepLabel }}</span>
                                            <span class="text-sm font-bold text-primary">{{ scanProgress.percent }}%</span>
                                        </div>
                                        <div class="w-full h-2.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div
                                                class="h-full rounded-full transition-all duration-500 ease-out"
                                                :class="scanProgress.percent >= 100 ? 'bg-emerald-500' : 'bg-primary'"
                                                :style="{ width: scanProgress.percent + '%' }"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Step indicators -->
                                    <div class="grid grid-cols-5 gap-2">
                                        <div v-for="(step, idx) in [
                                            { key: 'db', icon: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7', label: t('settings.stepDb'), threshold: 5 },
                                            { key: 'listing', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', label: t('settings.stepListing'), threshold: 22 },
                                            { key: 'scanning', icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', label: t('settings.stepScanning'), threshold: 25 },
                                            { key: 'duplicates', icon: 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z', label: t('settings.stepDuplicates'), threshold: 72 },
                                            { key: 'done', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', label: t('settings.stepDone'), threshold: 98 },
                                        ]" :key="step.key"
                                            class="flex flex-col items-center gap-1.5"
                                        >
                                            <div
                                                class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300"
                                                :class="scanProgress.percent >= step.threshold
                                                    ? (scanProgress.step === step.key && scanProgress.step !== 'done'
                                                        ? 'bg-primary/20 text-primary ring-2 ring-primary/30 animate-pulse'
                                                        : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400')
                                                    : 'bg-gray-100 text-gray-300 dark:bg-gray-700 dark:text-gray-600'"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="step.icon"/>
                                                </svg>
                                            </div>
                                            <span class="text-[10px] font-medium text-center leading-tight"
                                                :class="scanProgress.percent >= step.threshold
                                                    ? 'text-gray-700 dark:text-gray-300'
                                                    : 'text-gray-400 dark:text-gray-600'"
                                            >{{ step.label }}</span>
                                        </div>
                                    </div>

                                    <!-- Live counters -->
                                    <div v-if="scanProgress.total_files > 0" class="grid grid-cols-3 gap-3">
                                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-2.5 text-center">
                                            <p class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ scanProgress.scanned || 0 }} / {{ scanProgress.total_files }}</p>
                                            <p class="text-[10px] text-blue-500/70">{{ t('settings.filesScanned') }}</p>
                                        </div>
                                        <div class="rounded-lg bg-orange-50 dark:bg-orange-900/20 p-2.5 text-center">
                                            <p class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ scanProgress.orphans_found || 0 }}</p>
                                            <p class="text-[10px] text-orange-500/70">{{ t('settings.orphanFiles') }}</p>
                                        </div>
                                        <div class="rounded-lg bg-purple-50 dark:bg-purple-900/20 p-2.5 text-center">
                                            <p class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ scanProgress.duplicates_found || 0 }}</p>
                                            <p class="text-[10px] text-purple-500/70">{{ t('settings.duplicateGroups') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Error -->
                                <div v-if="storageError" class="p-4 m-4 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-sm">
                                    {{ storageError }}
                                </div>

                                <!-- Cleanup result -->
                                <div v-if="cleanupResult" class="p-4 mx-4 mt-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-2">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ t('settings.cleanupSuccess', { count: cleanupResult.deleted, size: formatBytes(cleanupResult.freed_size) }) }}
                                </div>

                                <!-- Folder cleanup result -->
                                <div v-if="cleanupFolderResult" class="p-4 mx-4 mt-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-2">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ t('settings.folderCleanupSuccess', { count: cleanupFolderResult.deleted }) }}
                                </div>

                                <!-- Analysis results -->
                                <div v-if="storageAnalysis" class="p-5 space-y-5">
                                    <!-- Summary cards -->
                                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                                        <div class="rounded-lg border border-gray-100 dark:border-gray-700 p-3 text-center">
                                            <p class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ storageAnalysis.total_files }}</p>
                                            <p class="text-[11px] text-gray-400">{{ t('settings.totalFiles') }}</p>
                                        </div>
                                        <div class="rounded-lg border border-gray-100 dark:border-gray-700 p-3 text-center">
                                            <p class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ formatBytes(storageAnalysis.total_size) }}</p>
                                            <p class="text-[11px] text-gray-400">{{ t('settings.totalSize') }}</p>
                                        </div>
                                        <div class="rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 p-3 text-center">
                                            <p class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ storageAnalysis.orphan_count }}</p>
                                            <p class="text-[11px] text-orange-500 dark:text-orange-400">{{ t('settings.orphanFiles') }}</p>
                                        </div>
                                        <div class="rounded-lg border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 p-3 text-center">
                                            <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ storageAnalysis.duplicate_groups }}</p>
                                            <p class="text-[11px] text-purple-500 dark:text-purple-400">{{ t('settings.duplicateGroups') }}</p>
                                        </div>
                                        <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/20 p-3 text-center">
                                            <p class="text-lg font-bold text-slate-600 dark:text-slate-400">{{ storageAnalysis.empty_folder_count }}</p>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ t('settings.emptyFolders') }}</p>
                                        </div>
                                    </div>

                                    <!-- Folder breakdown -->
                                    <div class="rounded-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                                        <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ t('settings.folderBreakdown') }}</p>
                                        </div>

                                        <!-- Browse mode (drill-down active) -->
                                        <div v-if="browseData">
                                            <!-- Breadcrumb -->
                                            <div class="px-4 py-2 bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700 flex items-center gap-1 flex-wrap text-xs">
                                                <button @click="navigateToBreadcrumb(-1)" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                                    storage
                                                </button>
                                                <template v-for="(part, i) in breadcrumbParts" :key="i">
                                                    <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                    <button v-if="i < breadcrumbParts.length - 1" @click="navigateToBreadcrumb(i)" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                                        {{ part }}
                                                    </button>
                                                    <span v-else class="text-gray-700 dark:text-gray-300 font-semibold">{{ part }}</span>
                                                </template>
                                            </div>

                                            <!-- Loading -->
                                            <div v-if="browseLoading" class="px-4 py-6 text-center">
                                                <svg class="w-5 h-5 animate-spin mx-auto text-indigo-500" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                </svg>
                                            </div>

                                            <div v-else class="divide-y divide-gray-50 dark:divide-gray-700">
                                                <!-- Subfolders -->
                                                <button v-for="folder in browseData.folders" :key="folder.path" @click="browseFolder(folder.path)"
                                                    class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors cursor-pointer">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-4 h-4" :class="folder.empty ? 'text-slate-400' : 'text-yellow-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                        </svg>
                                                        <span class="text-sm font-medium" :class="folder.empty ? 'text-slate-400 dark:text-slate-500' : 'text-gray-700 dark:text-gray-300'">{{ folder.name }}</span>
                                                        <span v-if="folder.empty" class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">{{ t('settings.emptyFolder') }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-right">
                                                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ formatBytes(folder.size) }}</span>
                                                            <span class="text-[11px] text-gray-400 ml-1">({{ folder.count }} {{ t('settings.filesLabel') }})</span>
                                                        </div>
                                                        <svg class="w-4 h-4 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </div>
                                                </button>

                                                <!-- Files -->
                                                <div v-for="file in browseData.files" :key="file.path" class="flex items-center justify-between px-4 py-2.5">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <svg class="w-4 h-4 shrink-0" :class="file.referenced ? 'text-green-400' : 'text-gray-300 dark:text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span class="text-sm text-gray-600 dark:text-gray-400 truncate">{{ file.name }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2 shrink-0 ml-3">
                                                        <span v-if="file.referenced" class="text-[10px] px-1.5 py-0.5 rounded bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">{{ t('settings.referenced') }}</span>
                                                        <span v-else class="text-[10px] px-1.5 py-0.5 rounded bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">{{ t('settings.unreferenced') }}</span>
                                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ formatBytes(file.size) }}</span>
                                                    </div>
                                                </div>

                                                <!-- Empty folder -->
                                                <div v-if="!browseData.folders.length && !browseData.files.length" class="px-4 py-6 text-center text-sm text-gray-400">
                                                    {{ t('settings.emptyFolder') }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Default view (top-level overview) -->
                                        <div v-else class="divide-y divide-gray-50 dark:divide-gray-700">
                                            <button v-for="(stat, folder) in storageAnalysis.folder_stats" :key="folder" @click="browseFolder(folder)"
                                                class="w-full flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors cursor-pointer">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                    </svg>
                                                    <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ folder }}</span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <div class="text-right">
                                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ formatBytes(stat.size) }}</span>
                                                        <span class="text-[11px] text-gray-400 ml-1">({{ stat.count }} {{ t('settings.filesLabel') }})</span>
                                                    </div>
                                                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Orphan files -->
                                    <div v-if="storageAnalysis.orphan_count > 0" class="rounded-lg border border-orange-200 dark:border-orange-800 overflow-hidden">
                                        <div class="px-4 py-2.5 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-200 dark:border-orange-800 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                                </svg>
                                                <p class="text-xs font-semibold text-orange-700 dark:text-orange-300">
                                                    {{ t('settings.orphanTitle', { count: storageAnalysis.orphan_count, size: formatBytes(storageAnalysis.orphan_size) }) }}
                                                </p>
                                            </div>
                                            <label class="flex items-center gap-1.5 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    :checked="selectedOrphans.length === storageAnalysis.orphan_files.length"
                                                    @change="toggleAllOrphans"
                                                    class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                />
                                                <span class="text-[11px] text-orange-600 dark:text-orange-400">{{ t('settings.selectAll') }}</span>
                                            </label>
                                        </div>
                                        <div class="max-h-72 overflow-y-auto divide-y divide-orange-100 dark:divide-orange-900/30">
                                            <label v-for="file in storageAnalysis.orphan_files" :key="file.path" class="flex items-center gap-3 px-4 py-2.5 hover:bg-orange-50/50 dark:hover:bg-orange-900/10 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    :value="file.path"
                                                    v-model="selectedOrphans"
                                                    class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary shrink-0"
                                                />
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ file.path }}</p>
                                                </div>
                                                <span class="text-xs text-gray-400 shrink-0">{{ formatBytes(file.size) }}</span>
                                            </label>
                                        </div>
                                        <div v-if="selectedOrphans.length > 0" class="px-4 py-3 bg-orange-50 dark:bg-orange-900/20 border-t border-orange-200 dark:border-orange-800 flex items-center justify-between">
                                            <p class="text-xs text-orange-600 dark:text-orange-400">
                                                {{ t('settings.selectedCount', { count: selectedOrphans.length, size: formatBytes(selectedOrphanSize) }) }}
                                            </p>
                                            <button
                                                @click="cleanupFiles"
                                                :disabled="cleaning"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-white bg-red-500 hover:bg-red-600 transition disabled:opacity-50"
                                            >
                                                <svg v-if="cleaning" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                </svg>
                                                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                {{ cleaning ? t('settings.cleaning') : t('settings.cleanBtn') }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- No orphans message -->
                                    <div v-else class="rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-4 flex items-center gap-3">
                                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-sm text-emerald-700 dark:text-emerald-400">{{ t('settings.noOrphans') }}</p>
                                    </div>

                                    <!-- Empty folders -->
                                    <div v-if="storageAnalysis.empty_folder_count > 0" class="rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                                        <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                </svg>
                                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                    {{ t('settings.emptyFoldersTitle', { count: storageAnalysis.empty_folder_count }) }}
                                                </p>
                                            </div>
                                            <label class="flex items-center gap-1.5 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    :checked="selectedEmptyFolders.length === storageAnalysis.empty_folders.length"
                                                    @change="toggleAllEmptyFolders"
                                                    class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                                                />
                                                <span class="text-[11px] text-slate-600 dark:text-slate-400">{{ t('settings.selectAll') }}</span>
                                            </label>
                                        </div>
                                        <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                                            <label v-for="folder in storageAnalysis.empty_folders" :key="folder" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    :value="folder"
                                                    v-model="selectedEmptyFolders"
                                                    class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary shrink-0"
                                                />
                                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                    </svg>
                                                    <p class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ folder }}</p>
                                                </div>
                                            </label>
                                        </div>
                                        <div v-if="selectedEmptyFolders.length > 0" class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                            <p class="text-xs text-slate-600 dark:text-slate-400">
                                                {{ t('settings.selectedFolderCount', { count: selectedEmptyFolders.length }) }}
                                            </p>
                                            <button
                                                @click="cleanupEmptyFolders"
                                                :disabled="cleaningFolders"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-white bg-red-500 hover:bg-red-600 transition disabled:opacity-50"
                                            >
                                                <svg v-if="cleaningFolders" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                </svg>
                                                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                {{ cleaningFolders ? t('settings.cleaning') : t('settings.cleanBtn') }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Duplicates -->
                                    <div v-if="storageAnalysis.duplicate_groups > 0" class="rounded-lg border border-purple-200 dark:border-purple-800 overflow-hidden">
                                        <div class="px-4 py-2.5 bg-purple-50 dark:bg-purple-900/20 border-b border-purple-200 dark:border-purple-800">
                                            <p class="text-xs font-semibold text-purple-700 dark:text-purple-300 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                {{ t('settings.duplicatesTitle', { count: storageAnalysis.duplicate_groups }) }}
                                            </p>
                                        </div>
                                        <div class="divide-y divide-purple-100 dark:divide-purple-900/30">
                                            <div v-for="(dup, i) in storageAnalysis.duplicates" :key="i" class="px-4 py-3">
                                                <div class="flex items-center justify-between mb-1.5">
                                                    <span class="text-[11px] font-mono text-purple-500">{{ dup.hash.substring(0, 12) }}...</span>
                                                    <span class="text-[11px] text-purple-500">{{ t('settings.wastedSpace') }}: {{ formatBytes(dup.wasted_size) }}</span>
                                                </div>
                                                <div class="space-y-1">
                                                    <div v-for="file in dup.files" :key="file.path" class="flex items-center gap-2 text-sm">
                                                        <span v-if="file.referenced" class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" :title="t('settings.referenced')"></span>
                                                        <span v-else class="w-1.5 h-1.5 rounded-full bg-orange-500 shrink-0" :title="t('settings.unreferenced')"></span>
                                                        <span class="text-gray-600 dark:text-gray-400 truncate">{{ file.path }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
