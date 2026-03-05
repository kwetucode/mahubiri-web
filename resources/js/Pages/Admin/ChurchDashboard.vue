<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Card from '@/Components/Card.vue';
import LineChart from '@/Components/LineChart.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();
const church = computed(() => page.props.church);
const stats = computed(() => page.props.stats);
const diskUsage = computed(() => page.props.diskUsage);
const latestSermons = computed(() => page.props.latestSermons ?? []);
const topSermons = computed(() => page.props.topSermons ?? []);
const pageLoading = ref(true);

onMounted(() => {
    setTimeout(() => { pageLoading.value = false; }, 400);
});

const icons = {
    mic: 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z',
    published: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    draft: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    eye: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    plus: 'M12 4v16m8-8H4',
    music: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3',
    trophy: 'M5 3l14 0M5 3v4a7.012 7.012 0 004 6.32V17H9a2 2 0 00-2 2v2h10v-2a2 2 0 00-2-2h-1v-3.68A7.012 7.012 0 0019 7V3',
    disk: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
};

const statCards = computed(() => [
    { label: t('churchDashboard.totalSermons'), value: stats.value.totalSermons, icon: icons.mic, color: 'primary' },
    { label: t('churchDashboard.publishedSermons'), value: stats.value.publishedSermons, icon: icons.published, color: 'emerald' },
    { label: t('churchDashboard.draftSermons'), value: stats.value.draftSermons, icon: icons.draft, color: 'amber' },
    { label: t('churchDashboard.totalListens'), value: stats.value.totalViews, icon: icons.eye, color: 'blue' },
]);

// Sermons chart
const sermonsChart = ref({ labels: [], data: [], total: 0, trend: '', trendUp: true });
const sermonsFilter = ref('this_month');
const sermonsLoading = ref(false);
const sermonsStartDate = ref('');
const sermonsEndDate = ref('');

// Views chart
const viewsChart = ref({ labels: [], data: [], total: 0, trend: '', trendUp: true });
const viewsFilter = ref('this_month');
const viewsLoading = ref(false);
const viewsStartDate = ref('');
const viewsEndDate = ref('');

const diskStatusColor = computed(() => {
    if (!diskUsage.value) return { bg: 'bg-emerald-500', text: 'text-emerald-600', light: 'bg-emerald-50', hex: '#10b981', label: t('churchDashboard.good') };
    const s = diskUsage.value.status;
    if (s === 'critical') return { bg: 'bg-red-500', text: 'text-red-600', light: 'bg-red-50', hex: '#ef4444', label: t('churchDashboard.critical') };
    if (s === 'warning') return { bg: 'bg-amber-500', text: 'text-amber-600', light: 'bg-amber-50', hex: '#f59e0b', label: t('churchDashboard.warning') };
    return { bg: 'bg-emerald-500', text: 'text-emerald-600', light: 'bg-emerald-50', hex: '#10b981', label: t('churchDashboard.good') };
});

const diskArc = computed(() => {
    const pct = diskUsage.value?.usedPercentage ?? 0;
    const circumference = 2 * Math.PI * 15.915; // ≈ 100
    const used = (pct / 100) * circumference;
    return { used: used.toFixed(2), gap: (circumference - used).toFixed(2) };
});

const filterOptions = computed(() => [
    { label: t('churchDashboard.thisWeek'), value: 'this_week' },
    { label: t('churchDashboard.thisMonth'), value: 'this_month' },
    { label: t('churchDashboard.lastMonth'), value: 'last_month' },
    { label: t('churchDashboard.last3months'), value: 'last_3_months' },
]);

const fetchChartData = async (type = 'sermons', startDate = '', endDate = '') => {
    const isViews = type === 'views';
    const filterRef = isViews ? viewsFilter : sermonsFilter;
    const loadingRef = isViews ? viewsLoading : sermonsLoading;
    const chartRef = isViews ? viewsChart : sermonsChart;

    loadingRef.value = true;
    try {
        const params = { type, filter: filterRef.value };
        if (filterRef.value === 'custom' && startDate && endDate) {
            params.start_date = startDate;
            params.end_date = endDate;
        }
        const { data } = await axios.get('/admin/dashboard/chart-data', { params });
        chartRef.value = data;
    } catch (e) {
        console.error('Chart fetch error:', e);
    } finally {
        loadingRef.value = false;
    }
};

const onSermonsFilterChange = ({ filter, startDate, endDate }) => {
    fetchChartData('sermons', startDate, endDate);
};

const onViewsFilterChange = ({ filter, startDate, endDate }) => {
    fetchChartData('views', startDate, endDate);
};

onMounted(() => {
    fetchChartData('sermons');
    fetchChartData('views');
});
</script>

<template>
    <AdminLayout title="Dashboard">
        <div class="space-y-6">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[{ label: 'Dashboard' }]" />

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
                <template v-if="pageLoading">
                    <div v-for="n in 4" :key="'skel-stat-'+n" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 px-3 py-2.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-gray-100 animate-pulse shrink-0"></div>
                            <div class="flex-1">
                                <div class="h-5 w-12 bg-gray-100 rounded-lg animate-pulse mb-1"></div>
                                <div class="h-2.5 w-20 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <StatCard
                        v-for="stat in statCards"
                        :key="stat.label"
                        :value="stat.value"
                        :label="stat.label"
                        :icon="stat.icon"
                        :color="stat.color"
                    />
                </template>
            </div>

            <!-- Charts side by side -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Sermons Evolution Chart -->
                <LineChart
                    :title="t('churchDashboard.sermonsOverTime')"
                    :subtitle="t('churchDashboard.publicationsPerDay')"
                    :labels="sermonsChart.labels"
                    :data="sermonsChart.data"
                    color="#6B4EAF"
                    :total="sermonsChart.total"
                    :trend="sermonsChart.trend"
                    :trend-up="sermonsChart.trendUp"
                    :loading="sermonsLoading"
                    :icon="icons.mic"
                    :filters="filterOptions"
                    v-model:active-filter="sermonsFilter"
                    v-model:start-date="sermonsStartDate"
                    v-model:end-date="sermonsEndDate"
                    show-custom-period
                    @filter-change="onSermonsFilterChange"
                    height="250px"
                />

                <!-- Views Evolution Chart -->
                <LineChart
                    :title="t('churchDashboard.listensOverTime')"
                    :subtitle="t('churchDashboard.listensPerDay')"
                    :labels="viewsChart.labels"
                    :data="viewsChart.data"
                    color="#3B82F6"
                    :total="viewsChart.total"
                    :trend="viewsChart.trend"
                    :trend-up="viewsChart.trendUp"
                    :loading="viewsLoading"
                    :icon="icons.eye"
                    :filters="filterOptions"
                    v-model:active-filter="viewsFilter"
                    v-model:start-date="viewsStartDate"
                    v-model:end-date="viewsEndDate"
                    show-custom-period
                    @filter-change="onViewsFilterChange"
                    height="250px"
                />
            </div>

            <!-- Disk Usage Card with Donut Chart -->
            <div v-if="diskUsage" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between border-b border-gray-50 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-primary/10 text-primary">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="icons.disk" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ t('churchDashboard.storage') }}</h3>
                            <p class="text-[11px] text-gray-400">{{ t('churchDashboard.diskQuota', { size: diskUsage.quotaGB }) }}</p>
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold"
                        :class="[diskStatusColor.light, diskStatusColor.text]"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="diskStatusColor.bg"></span>
                        {{ diskStatusColor.label }}
                    </span>
                </div>
                <div class="px-5 py-5">
                    <div class="flex items-center gap-6">
                        <!-- Donut Chart SVG -->
                        <div class="relative shrink-0" style="width: 140px; height: 140px;">
                            <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                                <!-- Background circle -->
                                <circle
                                    cx="18" cy="18" r="15.915"
                                    fill="none"
                                    class="stroke-gray-100 dark:stroke-gray-700"
                                    stroke-width="3"
                                />
                                <!-- Used segment -->
                                <circle
                                    cx="18" cy="18" r="15.915"
                                    fill="none"
                                    :stroke="diskStatusColor.hex"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    :stroke-dasharray="diskArc.used + ' ' + diskArc.gap"
                                    stroke-dashoffset="0"
                                    class="transition-all duration-700 ease-out"
                                />
                            </svg>
                            <!-- Center text -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-xl font-bold text-gray-900 dark:text-white leading-none">{{ diskUsage.usedPercentage }}%</span>
                                <span class="text-[10px] text-gray-400 mt-0.5">{{ t('churchDashboard.used') }}</span>
                            </div>
                        </div>

                        <!-- Legend & details -->
                        <div class="flex-1 space-y-3">
                            <!-- Used -->
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full shrink-0" :class="diskStatusColor.bg"></span>
                                <div class="flex-1">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ t('churchDashboard.used') }}</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ diskUsage.usedMB >= 1024 ? diskUsage.usedGB + ' GB' : diskUsage.usedMB + ' MB' }}
                                        </span>
                                    </div>
                                    <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500" :class="diskStatusColor.bg" :style="{ width: Math.max(diskUsage.usedPercentage, 1) + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Remaining -->
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full bg-gray-200 dark:bg-gray-600 shrink-0"></span>
                                <div class="flex-1">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ t('churchDashboard.available') }}</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ diskUsage.remainingGB }} GB</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full mt-1 overflow-hidden">
                                        <div class="h-full rounded-full bg-gray-300 dark:bg-gray-500 transition-all duration-500" :style="{ width: diskUsage.remainingPercentage + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Divider + stats -->
                            <div class="flex items-center gap-4 pt-2 border-t border-gray-50 dark:border-gray-700 text-center">
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ diskUsage.totalSermons }}</p>
                                    <p class="text-[10px] text-gray-400">{{ t('churchDashboard.files') }}</p>
                                </div>
                                <div class="w-px h-6 bg-gray-100 dark:bg-gray-700"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ diskUsage.avgSizeMB }}</p>
                                    <p class="text-[10px] text-gray-400">MB/sermon</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
                <!-- Latest sermons -->
                <Card
                    class="lg:col-span-3"
                    :title="t('churchDashboard.recentSermons')"
                    :subtitle="t('churchDashboard.recentSermonsSubtitle')"
                    no-padding
                >
                    <template #header-actions>
                        <Link href="/admin/sermons" class="text-[11px] font-semibold text-primary hover:text-primary-dark transition-colors">
                            {{ t('churchDashboard.viewAll') }}
                        </Link>
                    </template>

                    <div v-if="pageLoading" class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        <div v-for="n in 5" :key="'skel-s-'+n" class="flex items-center gap-3 px-5 py-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 animate-pulse shrink-0"></div>
                            <div class="flex-1">
                                <div class="h-3.5 w-40 bg-gray-100 rounded animate-pulse mb-1"></div>
                                <div class="h-2.5 w-24 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="latestSermons.length === 0" class="px-5 py-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="icons.music" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('churchDashboard.noSermons') }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ t('churchDashboard.publishFirstSermon') }}</p>
                        <Link
                            href="/admin/sermons/create"
                            class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 text-xs font-semibold text-primary bg-primary/5 rounded-lg hover:bg-primary/10 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.plus" />
                            </svg>
                            {{ t('churchDashboard.create') }}
                        </Link>
                    </div>

                    <div v-else class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        <Link
                            v-for="sermon in latestSermons"
                            :key="sermon.id"
                            :href="`/admin/sermons/${sermon.id}/edit`"
                            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors group"
                        >
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0"
                                :class="sermon.is_published ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sermon.is_published ? icons.published : icons.draft" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate group-hover:text-primary transition-colors">{{ sermon.title }}</p>
                                <div class="flex items-center gap-2 text-[11px] text-gray-400 dark:text-gray-500">
                                    <span>{{ sermon.preacher_name }}</span>
                                    <span class="text-gray-200 dark:text-gray-600">•</span>
                                    <span>{{ sermon.created_at_human }}</span>
                                    <span v-if="sermon.duration_formatted" class="text-gray-200 dark:text-gray-600">•</span>
                                    <span v-if="sermon.duration_formatted">{{ sermon.duration_formatted }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-gray-400 shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ sermon.views_count }}
                            </div>
                        </Link>
                    </div>
                </Card>

                <!-- Right column -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Quick Actions -->
                    <Card :title="t('churchDashboard.quickActions')">
                        <div class="space-y-2">
                            <Link
                                href="/admin/sermons/create"
                                class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-primary/5 dark:hover:bg-primary/10 border border-gray-100 dark:border-gray-600 hover:border-primary/20 transition-all duration-200 group"
                            >
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.plus" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ t('churchDashboard.newSermon') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('churchDashboard.publishNewSermon') }}</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-500 group-hover:text-primary/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                            <Link
                                href="/admin/sermons"
                                class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-primary/5 dark:hover:bg-primary/10 border border-gray-100 dark:border-gray-600 hover:border-primary/20 transition-all duration-200 group"
                            >
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.music" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ t('churchDashboard.manageSermons') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('churchDashboard.manageYourSermons') }}</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-500 group-hover:text-primary/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </Card>

                    <!-- Top sermons (most viewed) -->
                    <Card :title="t('churchDashboard.topSermons')" :subtitle="t('churchDashboard.mostListened')" no-padding>
                        <div v-if="topSermons.length === 0" class="px-5 py-6 text-center">
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('churchDashboard.noDataAvailable') }}</p>
                        </div>
                        <div v-else class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <div
                                v-for="(sermon, i) in topSermons"
                                :key="sermon.id"
                                class="flex items-center gap-3 px-5 py-2.5"
                            >
                                <span
                                    class="flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold shrink-0"
                                    :class="i === 0 ? 'bg-amber-100 text-amber-700' : i === 1 ? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' : i === 2 ? 'bg-orange-50 text-orange-600' : 'bg-gray-50 dark:bg-gray-700/50 text-gray-400'"
                                >
                                    {{ i + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ sermon.title }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ sermon.preacher_name }}</p>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 shrink-0">{{ sermon.views_count }} {{ t('churchDashboard.listens') }}</span>
                            </div>
                        </div>
                    </Card>

                    <!-- Summary card -->
                    <Card dark no-padding>
                        <template #header>
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                                <h3 class="text-xs font-semibold text-white/90">{{ t('churchDashboard.summary') }}</h3>
                            </div>
                        </template>
                        <div class="px-5 py-3.5 space-y-3">
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('churchDashboard.thisWeek') }}</span>
                                <span class="text-blue-400 font-medium">{{ t('churchDashboard.sermonsCount', { count: stats.sermonsThisWeek }) }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('churchDashboard.thisMonth') }}</span>
                                <span class="text-emerald-400 font-medium">{{ t('churchDashboard.sermonsCount', { count: stats.sermonsThisMonth }) }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('churchDashboard.publicationRate') }}</span>
                                <span class="text-accent-warm font-medium">
                                    {{ stats.totalSermons > 0 ? Math.round((stats.publishedSermons / stats.totalSermons) * 100) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
