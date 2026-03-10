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
const preacher = computed(() => page.props.preacher);
const stats = computed(() => page.props.stats);
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
    plus: 'M12 4v16m8-8H4',
    music: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3',
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
                                    <span class="text-gray-200 dark:text-gray-600">&bull;</span>
                                    <span>{{ sermon.created_at_human }}</span>
                                    <span v-if="sermon.duration_formatted" class="text-gray-200 dark:text-gray-600">&bull;</span>
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
                            <Link
                                href="/admin/donations/create"
                                class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-primary/5 dark:hover:bg-primary/10 border border-gray-100 dark:border-gray-600 hover:border-primary/20 transition-all duration-200 group"
                            >
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 group-hover:bg-amber-600 group-hover:text-white transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ t('churchDashboard.makeDonation') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('churchDashboard.supportPlatform') }}</p>
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
