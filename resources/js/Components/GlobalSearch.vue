<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const open = ref(false);
const query = ref('');
const results = ref([]);
const loading = ref(false);
const selectedIndex = ref(-1);
const searchInput = ref(null);
const resultsContainer = ref(null);

let debounceTimer = null;

// Open / close
const openSearch = () => {
    open.value = true;
    query.value = '';
    results.value = [];
    selectedIndex.value = -1;
    nextTick(() => searchInput.value?.focus());
};

const closeSearch = () => {
    open.value = false;
    query.value = '';
    results.value = [];
};

// Keyboard shortcut: Ctrl+K / Cmd+K
const handleGlobalKeydown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        open.value ? closeSearch() : openSearch();
    }
    if (e.key === 'Escape' && open.value) {
        closeSearch();
    }
};

onMounted(() => document.addEventListener('keydown', handleGlobalKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleGlobalKeydown));

// Search with debounce
watch(query, (val) => {
    clearTimeout(debounceTimer);
    selectedIndex.value = -1;

    if (val.trim().length < 2) {
        results.value = [];
        loading.value = false;
        return;
    }

    loading.value = true;
    debounceTimer = setTimeout(() => fetchResults(val.trim()), 300);
});

const fetchResults = async (q) => {
    try {
        const res = await fetch(`/admin/search?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        results.value = data.results ?? [];
    } catch (e) {
        console.error('Search failed', e);
        results.value = [];
    } finally {
        loading.value = false;
    }
};

// Navigate with keyboard
const handleKeydown = (e) => {
    if (results.value.length === 0) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = (selectedIndex.value + 1) % results.value.length;
        scrollToSelected();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = selectedIndex.value <= 0 ? results.value.length - 1 : selectedIndex.value - 1;
        scrollToSelected();
    } else if (e.key === 'Enter' && selectedIndex.value >= 0) {
        e.preventDefault();
        goTo(results.value[selectedIndex.value]);
    }
};

const scrollToSelected = () => {
    nextTick(() => {
        const el = resultsContainer.value?.querySelector(`[data-index="${selectedIndex.value}"]`);
        el?.scrollIntoView({ block: 'nearest' });
    });
};

const goTo = (item) => {
    closeSearch();
    router.visit(item.url);
};

// Type icon & colors
const typeIcon = (type) => {
    const icons = {
        sermon: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3',
        church: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        user: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    };
    return icons[type] || icons.sermon;
};

const typeLabel = (type) => {
    const labels = { sermon: t('globalSearch.sermon'), church: t('globalSearch.church'), user: t('globalSearch.user') };
    return labels[type] || type;
};

const typeBg = (type) => {
    const bgs = {
        sermon: 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400',
        church: 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        user: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
    };
    return bgs[type] || bgs.sermon;
};

const badgeColor = (color) => {
    const colors = {
        emerald: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
        amber: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
        red: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
        blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
    };
    return colors[color] || colors.blue;
};

// Group results by type
const groupedResults = () => {
    const groups = {};
    let globalIndex = 0;
    results.value.forEach((r) => {
        if (!groups[r.type]) groups[r.type] = { label: typeLabel(r.type), items: [] };
        groups[r.type].items.push({ ...r, _index: globalIndex++ });
    });
    return Object.values(groups);
};

defineExpose({ openSearch });
</script>

<template>
    <!-- Trigger button — desktop -->
    <button
        @click="openSearch"
        class="hidden md:flex items-center gap-2.5 px-4 py-2 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl text-gray-400 dark:text-gray-500 text-sm min-w-[220px] ring-1 ring-gray-200/80 dark:ring-gray-700/80 hover:ring-primary/40 dark:hover:ring-primary/40 hover:text-gray-500 dark:hover:text-gray-400 hover:shadow-sm transition-all duration-200 cursor-pointer group"
    >
        <svg class="w-4 h-4 text-gray-400 group-hover:text-primary dark:text-gray-500 dark:group-hover:text-primary-light transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <span class="text-gray-400 dark:text-gray-500">{{ t('common.search') }}</span>
        <kbd class="ml-auto inline-flex items-center px-1.5 py-0.5 bg-gray-100/80 dark:bg-gray-700/80 rounded-md text-[10px] font-mono text-gray-400 dark:text-gray-500 border border-gray-200/60 dark:border-gray-600/60 shadow-[0_1px_0_0_rgba(0,0,0,0.05)] dark:shadow-[0_1px_0_0_rgba(255,255,255,0.03)]">⌘K</kbd>
    </button>

    <!-- Trigger button — mobile -->
    <button
        @click="openSearch"
        class="md:hidden p-2.5 rounded-xl text-gray-500 hover:bg-primary/10 hover:text-primary dark:text-gray-400 dark:hover:bg-primary/10 dark:hover:text-primary-light transition-all duration-200"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </button>

    <!-- Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="open" class="fixed inset-0 z-[100] flex items-start justify-center pt-[12vh] sm:pt-[15vh] px-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-gray-950/40 dark:bg-black/70 backdrop-blur-md" @click="closeSearch"></div>

                <!-- Dialog -->
                <Transition
                    enter-active-class="transition-all duration-250 ease-[cubic-bezier(0.16,1,0.3,1)]"
                    enter-from-class="opacity-0 scale-[0.97] -translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-[0.97]"
                    appear
                >
                    <div
                        v-if="open"
                        class="relative w-full max-w-xl bg-white dark:bg-gray-800 rounded-2xl shadow-[0_25px_60px_-12px_rgba(0,0,0,0.25)] dark:shadow-[0_25px_60px_-12px_rgba(0,0,0,0.5)] ring-1 ring-black/[0.06] dark:ring-white/[0.06] overflow-hidden"
                    >
                        <!-- Top accent bar -->
                        <div class="h-[2px] bg-gradient-to-r from-primary/60 via-primary to-primary/60"></div>

                        <!-- Search input -->
                        <div class="flex items-center gap-3 px-5 border-b border-gray-100 dark:border-gray-700/70">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 dark:bg-primary/15 shrink-0">
                                <svg class="w-4 h-4 text-primary dark:text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                ref="searchInput"
                                v-model="query"
                                @keydown="handleKeydown"
                                type="text"
                                class="flex-1 py-4 bg-transparent text-[15px] text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border-none ring-0 shadow-none outline-none focus:ring-0 focus:outline-none focus:border-none focus-visible:outline-none focus-visible:ring-0 font-medium"
                                style="outline: none !important; box-shadow: none !important;"
                                :placeholder="t('globalSearch.placeholder')"
                                autocomplete="off"
                            />
                            <button
                                @click="closeSearch"
                                class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg text-[11px] font-semibold text-gray-400 dark:text-gray-500 border border-gray-200/60 dark:border-gray-600/60 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 hover:text-gray-500 dark:hover:text-gray-400 transition-colors shadow-[0_1px_0_0_rgba(0,0,0,0.05)] dark:shadow-[0_1px_0_0_rgba(255,255,255,0.03)]"
                            >ESC</button>
                        </div>

                        <!-- Results area -->
                        <div ref="resultsContainer" class="max-h-[360px] overflow-y-auto overscroll-contain scroll-smooth">
                            <!-- Loading skeleton -->
                            <div v-if="loading" class="p-3 space-y-1">
                                <div v-for="n in 4" :key="'sk-'+n" class="flex items-center gap-3 p-3 rounded-xl animate-pulse">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700/80 shrink-0"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-3.5 bg-gray-100 dark:bg-gray-700/80 rounded-lg w-3/4"></div>
                                        <div class="h-2.5 bg-gray-50 dark:bg-gray-700/40 rounded-lg w-1/2"></div>
                                    </div>
                                    <div class="h-5 w-16 bg-gray-50 dark:bg-gray-700/40 rounded-full shrink-0"></div>
                                </div>
                            </div>

                            <!-- No results -->
                            <div v-else-if="query.length >= 2 && results.length === 0 && !loading" class="flex flex-col items-center justify-center py-14 px-6">
                                <div class="w-16 h-16 rounded-2xl bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">{{ t('common.noResults') }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 text-center">{{ t('globalSearch.noResultsTip1') }}<br/>{{ t('globalSearch.noResultsTip2') }}</p>
                            </div>

                            <!-- Grouped results -->
                            <div v-else-if="results.length > 0" class="p-2">
                                <div v-for="(group, gi) in groupedResults()" :key="group.label" :class="{ 'mt-2': gi > 0 }">
                                    <p class="flex items-center gap-2 px-3 py-2 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest select-none">
                                        <span class="w-4 h-px bg-gray-200 dark:bg-gray-700"></span>
                                        {{ group.label }}
                                        <span class="flex-1 h-px bg-gray-200/60 dark:bg-gray-700/60"></span>
                                        <span class="text-[10px] font-semibold text-gray-300 dark:text-gray-600 normal-case tracking-normal">{{ group.items.length }}</span>
                                    </p>
                                    <button
                                        v-for="item in group.items"
                                        :key="item.type + '-' + item.id"
                                        :data-index="item._index"
                                        @click="goTo(item)"
                                        @mouseenter="selectedIndex = item._index"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-all duration-150"
                                        :class="selectedIndex === item._index
                                            ? 'bg-primary/[0.08] dark:bg-primary/[0.15] ring-1 ring-primary/20 dark:ring-primary/30 shadow-sm'
                                            : 'hover:bg-gray-50 dark:hover:bg-gray-700/40'"
                                    >
                                        <!-- Icon or image -->
                                        <div v-if="item.image" class="w-10 h-10 rounded-xl overflow-hidden shrink-0 ring-1 ring-black/5 dark:ring-white/10">
                                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover" />
                                        </div>
                                        <div v-else class="flex items-center justify-center w-10 h-10 rounded-xl shrink-0 ring-1 ring-black/5 dark:ring-white/5" :class="typeBg(item.type)">
                                            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="typeIcon(item.type)" />
                                            </svg>
                                        </div>

                                        <!-- Text -->
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-semibold text-gray-800 dark:text-gray-100 truncate leading-snug">{{ item.title }}</p>
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate mt-0.5">{{ item.subtitle }}</p>
                                        </div>

                                        <!-- Badge -->
                                        <span
                                            v-if="item.badge"
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 tracking-wide"
                                            :class="badgeColor(item.badge_color)"
                                        >
                                            {{ item.badge }}
                                        </span>

                                        <!-- Arrow indicator when selected -->
                                        <div
                                            v-if="selectedIndex === item._index"
                                            class="flex items-center justify-center w-6 h-6 rounded-lg bg-primary/10 dark:bg-primary/20 shrink-0"
                                        >
                                            <svg class="w-3.5 h-3.5 text-primary dark:text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Initial state -->
                            <div v-else class="flex flex-col items-center justify-center py-14 text-gray-400 dark:text-gray-500">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/10 to-primary/5 dark:from-primary/15 dark:to-primary/5 flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-primary/60 dark:text-primary-light/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('globalSearch.quickSearch') }}</p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">{{ t('globalSearch.minChars') }}</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center gap-5 px-5 py-2.5 border-t border-gray-100 dark:border-gray-700/70 bg-gray-50/50 dark:bg-gray-800/50 text-[11px] text-gray-400 dark:text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <kbd class="inline-flex items-center justify-center min-w-[22px] h-[20px] px-1 bg-white dark:bg-gray-700 rounded-md text-[10px] font-mono border border-gray-200/80 dark:border-gray-600/80 shadow-[0_1px_0_0_rgba(0,0,0,0.05)] dark:shadow-[0_1px_0_0_rgba(255,255,255,0.03)]">↑↓</kbd>
                                <span class="text-gray-400 dark:text-gray-500">{{ t('globalSearch.navigate') }}</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <kbd class="inline-flex items-center justify-center min-w-[22px] h-[20px] px-1 bg-white dark:bg-gray-700 rounded-md text-[10px] font-mono border border-gray-200/80 dark:border-gray-600/80 shadow-[0_1px_0_0_rgba(0,0,0,0.05)] dark:shadow-[0_1px_0_0_rgba(255,255,255,0.03)]">↵</kbd>
                                <span class="text-gray-400 dark:text-gray-500">{{ t('globalSearch.open') }}</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <kbd class="inline-flex items-center justify-center min-w-[22px] h-[20px] px-1 bg-white dark:bg-gray-700 rounded-md text-[10px] font-mono border border-gray-200/80 dark:border-gray-600/80 shadow-[0_1px_0_0_rgba(0,0,0,0.05)] dark:shadow-[0_1px_0_0_rgba(255,255,255,0.03)]">esc</kbd>
                                <span class="text-gray-400 dark:text-gray-500">{{ t('globalSearch.close') }}</span>
                            </span>
                            <span class="ml-auto text-[10px] text-gray-300 dark:text-gray-600 font-medium">Mahubiri Search</span>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
