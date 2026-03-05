<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import SidebarItem from '@/Components/SidebarItem.vue';
import SidebarDropdown from '@/Components/SidebarDropdown.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import PageLoader from '@/Components/PageLoader.vue';

const { t, locale } = useI18n();

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');
const isChurchAdmin = computed(() => user.value?.role === 'church_admin');

// Notifications
const unreadCount = computed(() => page.props.notifications?.unread_count ?? 0);
const notifOpen = ref(false);
const notifLoading = ref(false);
const notifications = ref([]);

const toggleNotifications = async () => {
    notifOpen.value = !notifOpen.value;
    if (notifOpen.value && notifications.value.length === 0) {
        await fetchNotifications();
    }
};

const fetchNotifications = async () => {
    notifLoading.value = true;
    try {
        const res = await fetch('/admin/notifications', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        notifications.value = data.notifications ?? [];
    } catch (e) {
        console.error('Failed to fetch notifications', e);
    } finally {
        notifLoading.value = false;
    }
};

const markAsRead = async (notif) => {
    if (notif.read_at) return;
    try {
        await fetch(`/admin/notifications/${notif.id}/read`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? ''),
            },
        });
        notif.read_at = new Date().toISOString();
        router.reload({ only: ['notifications'] });
    } catch (e) {
        console.error('Failed to mark notification as read', e);
    }
};

const markAllAsRead = async () => {
    try {
        await fetch('/admin/notifications/read-all', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? ''),
            },
        });
        notifications.value.forEach(n => n.read_at = n.read_at || new Date().toISOString());
        router.reload({ only: ['notifications'] });
    } catch (e) {
        console.error('Failed to mark all as read', e);
    }
};

const notifIcon = (type) => {
    const icons = {
        new_church: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        new_donation: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        new_user: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    };
    return icons[type] || 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9';
};

const notifColor = (type) => {
    const colors = {
        new_church: 'bg-blue-50 text-blue-600',
        new_donation: 'bg-emerald-50 text-emerald-600',
        new_user: 'bg-purple-50 text-purple-600',
    };
    return colors[type] || 'bg-gray-50 text-gray-600';
};

// Close dropdown on outside click
const notifDropdownRef = ref(null);
const handleClickOutside = (e) => {
    if (notifOpen.value && notifDropdownRef.value && !notifDropdownRef.value.contains(e.target)) {
        notifOpen.value = false;
    }
};

// Icônes SVG paths réutilisables
const icons = {
    dashboard: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
    users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    settings: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
    settingsInner: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    church: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    sermon: 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z',
    person: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    shield: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    donation: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
};

const currentPath = computed(() => {
    return window.location.pathname;
});

const showLogoutModal = ref(false);

const confirmLogout = () => {
    showLogoutModal.value = true;
};

const cancelLogout = () => {
    showLogoutModal.value = false;
};

const logout = () => {
    showLogoutModal.value = false;
    router.post('/admin/logout');
};

defineProps({
    title: {
        type: String,
        default: '',
    },
});

// Sidebar collapse state
const sidebarCollapsed = ref(false);
const mobileMenuOpen = ref(false);

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value);
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

// Restore sidebar state
onMounted(() => {
    const saved = localStorage.getItem('sidebar-collapsed');
    if (saved === 'true') {
        sidebarCollapsed.value = true;
    }
});

// Close mobile menu on resize
const handleResize = () => {
    if (window.innerWidth >= 1024) {
        mobileMenuOpen.value = false;
    }
};

onMounted(() => window.addEventListener('resize', handleResize));
onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    document.removeEventListener('mousedown', handleClickOutside);
});

// Current date for header — reacts to locale changes
const localeMap = { fr: 'fr-FR', en: 'en-GB', sw: 'sw-TZ' };
const currentDate = computed(() => {
    return new Date().toLocaleDateString(localeMap[locale.value] || 'fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});
</script>

<template>
    <div class="min-h-screen bg-gray-50/50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Page transition loader -->
        <PageLoader />

        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40 lg:hidden"
                @click="mobileMenuOpen = false"
            ></div>
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200/80 dark:border-gray-700/80 transition-all duration-300 ease-in-out"
            :class="[
                sidebarCollapsed ? 'w-[78px]' : 'w-[260px]',
                mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-3 px-5 py-5 border-b border-gray-100 dark:border-gray-700/50 min-h-[72px] no-underline">
                <img src="/logo.png" alt="Mahubiri" class="w-10 h-10 rounded-xl shadow-md shadow-primary/20 flex-shrink-0 object-contain" />
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 -translate-x-2"
                    enter-to-class="opacity-100 translate-x-0"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="!sidebarCollapsed" class="min-w-0">
                        <h1 class="text-lg font-bold text-gray-900 dark:text-white truncate">Mahubiri</h1>
                        <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">{{ t('layout.administration') }}</p>
                    </div>
                </Transition>
            </Link>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto overflow-x-hidden">
                <p v-show="!sidebarCollapsed" class="px-3 mb-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                    {{ t('layout.mainMenu') }}
                </p>

                <SidebarItem
                    href="/admin/dashboard"
                    :label="t('nav.dashboard')"
                    :icon="icons.dashboard"
                    :collapsed="sidebarCollapsed"
                />

                <SidebarItem
                    v-if="isAdmin"
                    href="/admin/users"
                    :label="t('nav.users')"
                    :icon="icons.users"
                    :collapsed="sidebarCollapsed"
                />

                <SidebarItem
                    v-if="isAdmin"
                    href="/admin/churches"
                    :label="t('nav.churches')"
                    :icon="icons.church"
                    :collapsed="sidebarCollapsed"
                />

                <SidebarItem
                    v-if="isAdmin"
                    href="/admin/sermon-categories"
                    :label="t('nav.sermonCategories')"
                    :icon="icons.sermon"
                    :collapsed="sidebarCollapsed"
                />

                <SidebarItem
                    v-if="isChurchAdmin"
                    href="/admin/church-profile"
                    :label="t('nav.myChurch')"
                    :icon="icons.church"
                    :collapsed="sidebarCollapsed"
                />

                <SidebarItem
                    v-if="isChurchAdmin"
                    href="/admin/sermons"
                    :label="t('nav.sermons')"
                    icon="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                    :collapsed="sidebarCollapsed"
                />

                <SidebarItem
                    v-if="isAdmin"
                    href="/admin/donations"
                    :label="t('nav.donations')"
                    :icon="icons.donation"
                    :collapsed="sidebarCollapsed"
                />

                <SidebarItem
                    href="/admin/donations/create"
                    :label="t('nav.makeDonation')"
                    icon="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    :collapsed="sidebarCollapsed"
                />

                <SidebarItem
                    v-if="isChurchAdmin"
                    href="/admin/storage-upgrade"
                    :label="t('nav.storageUpgrade')"
                    icon="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"
                    :collapsed="sidebarCollapsed"
                />


            </nav>

            <!-- Settings + Collapse (Desktop only) -->
            <div class="hidden lg:block px-3 py-2 border-t border-gray-100 dark:border-gray-700/50 space-y-1">
                <Link
                    href="/admin/settings"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200"
                    :class="[
                        currentPath.startsWith('/admin/settings')
                            ? 'bg-primary/10 text-primary dark:bg-primary/20'
                            : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700/50 dark:hover:text-gray-300',
                        sidebarCollapsed ? 'justify-center' : ''
                    ]"
                >
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.settings" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.settingsInner" />
                    </svg>
                    <span v-show="!sidebarCollapsed" class="truncate">{{ t('nav.settings') }}</span>
                </Link>
                <button
                    @click="toggleSidebar"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700/50 dark:hover:text-gray-300 transition-all duration-200"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <svg
                        class="w-5 h-5 transition-transform duration-300 flex-shrink-0"
                        :class="sidebarCollapsed ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                    <span v-show="!sidebarCollapsed" class="truncate">{{ t('layout.collapse') }}</span>
                </button>
            </div>

            <!-- User -->
            <div class="border-t border-gray-100 dark:border-gray-700/50 p-3">
                <div
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    :class="sidebarCollapsed ? 'justify-center' : ''"
                >
                    <div class="flex items-center justify-center w-9 h-9 bg-gradient-to-br from-primary/20 to-primary/10 rounded-full flex-shrink-0 ring-2 ring-primary/10">
                        <span class="text-sm font-bold text-primary">
                            {{ user?.name?.charAt(0)?.toUpperCase() }}
                        </span>
                    </div>
                    <template v-if="!sidebarCollapsed">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ user?.name }}</p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ user?.email }}</p>
                        </div>
                        <button
                            @click="confirmLogout"
                            class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all duration-200"
                            title="Déconnexion"
                        >
                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </template>
                    <!-- Logout button when sidebar is collapsed -->
                    <button
                        v-if="sidebarCollapsed"
                        @click="confirmLogout"
                        class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-all duration-200"
                        title="Déconnexion"
                    >
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div
            class="transition-all duration-300 ease-in-out"
            :class="sidebarCollapsed ? 'lg:pl-[78px]' : 'lg:pl-[260px]'"
        >
            <!-- Top bar -->
            <header class="sticky top-0 z-30 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl border-b border-gray-200/60 dark:border-gray-700/60">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-[72px]">
                    <div class="flex items-center gap-4">
                        <!-- Mobile menu button -->
                        <button
                            @click="toggleMobileMenu"
                            class="lg:hidden p-2 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700/50 dark:hover:text-gray-200 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ title }}</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block capitalize">{{ currentDate }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Global Search -->
                        <GlobalSearch />

                        <!-- Notification bell -->
                        <div class="relative" ref="notifDropdownRef">
                            <button
                                @click="toggleNotifications"
                                class="relative p-2.5 rounded-xl text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700/50 dark:hover:text-gray-200 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold ring-2 ring-white"
                                >
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
                            </button>

                            <!-- Dropdown -->
                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1 scale-95"
                                enter-to-class="opacity-100 translate-y-0 scale-100"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0 scale-100"
                                leave-to-class="opacity-0 translate-y-1 scale-95"
                            >
                                <div
                                    v-if="notifOpen"
                                    class="absolute right-0 mt-2 w-96 max-h-112 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200/60 dark:border-gray-700 ring-1 ring-black/5 overflow-hidden z-50 flex flex-col"
                                >
                                    <!-- Header -->
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ t('layout.notifications') }}</h3>
                                        <button
                                            v-if="unreadCount > 0"
                                            @click="markAllAsRead"
                                            class="text-xs font-medium text-primary hover:text-primary-dark transition-colors"
                                        >
                                            {{ t('layout.markAllRead') }}
                                        </button>
                                    </div>

                                    <!-- List -->
                                    <div class="flex-1 overflow-y-auto">
                                        <!-- Loading -->
                                        <div v-if="notifLoading" class="p-4 space-y-3">
                                            <div v-for="n in 4" :key="'ns-'+n" class="flex gap-3 animate-pulse">
                                                <div class="w-9 h-9 rounded-lg bg-gray-100 shrink-0"></div>
                                                <div class="flex-1 space-y-1.5">
                                                    <div class="h-3 bg-gray-100 rounded w-3/4"></div>
                                                    <div class="h-2.5 bg-gray-50 rounded w-1/2"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Empty -->
                                        <div v-else-if="notifications.length === 0" class="flex flex-col items-center justify-center py-10 text-gray-400">
                                            <svg class="w-10 h-10 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                            <p class="text-xs">{{ t('layout.noNotifications') }}</p>
                                        </div>

                                        <!-- Items -->
                                        <div v-else class="divide-y divide-gray-50">
                                            <button
                                                v-for="notif in notifications"
                                                :key="notif.id"
                                                @click="markAsRead(notif); notif.action_url && router.visit(notif.action_url);"
                                                class="w-full flex items-start gap-3 px-4 py-3 text-left hover:bg-gray-50/80 transition-colors"
                                                :class="{ 'bg-primary/3': !notif.read_at }"
                                            >
                                                <div class="flex items-center justify-center w-9 h-9 rounded-lg shrink-0" :class="notifColor(notif.type)">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="notifIcon(notif.type)" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[13px] font-semibold text-gray-900 truncate leading-tight">{{ notif.title }}</p>
                                                    <p class="text-[12px] text-gray-500 mt-0.5 line-clamp-2">{{ notif.message }}</p>
                                                    <p class="text-[11px] text-gray-400 mt-1">{{ notif.created_at }}</p>
                                                </div>
                                                <span v-if="!notif.read_at" class="w-2 h-2 rounded-full bg-primary shrink-0 mt-1.5"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Locale switcher -->
                        <LocaleSwitcher />

                        <!-- Admin badge -->
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-primary/10 text-primary ring-1 ring-primary/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            {{ t('layout.admin') }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>

        <!-- Logout Confirmation Modal -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showLogoutModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="cancelLogout"></div>

                <!-- Modal -->
                <Transition
                    appear
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 scale-90 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-90 translate-y-4"
                >
                    <div v-if="showLogoutModal" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center">
                        <!-- Icon -->
                        <div class="mx-auto w-14 h-14 rounded-full bg-red-50 dark:bg-red-900/30 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ t('auth.logoutConfirm') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ t('auth.logoutMessage') }}</p>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button
                                @click="cancelLogout"
                                class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                            >
                                {{ t('common.cancel') }}
                            </button>
                            <button
                                @click="logout"
                                class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-500 hover:bg-red-600 shadow-lg shadow-red-500/25 transition-colors"
                            >
                                {{ t('auth.logout') }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
/* Smooth scrollbar for sidebar */
nav::-webkit-scrollbar {
    width: 3px;
}
nav::-webkit-scrollbar-track {
    background: transparent;
}
nav::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
nav:hover::-webkit-scrollbar-thumb {
    background: #d1d5db;
}
</style>
