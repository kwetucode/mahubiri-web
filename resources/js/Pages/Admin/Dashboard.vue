<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Card from '@/Components/Card.vue';
import LineChart from '@/Components/LineChart.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';

const page = usePage();
const stats = computed(() => page.props.stats);
const pageLoading = ref(true);

onMounted(() => {
    // Simulate initial page load skeleton
    setTimeout(() => { pageLoading.value = false; }, 400);
});

const icons = {
    users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    clock: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    userAdd: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
    mic: 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z',
    chat: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    refresh: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
    church: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
};

const statCards = computed(() => [
    { label: 'Total utilisateurs', value: stats.value.totalUsers, icon: icons.users, color: 'primary', trend: '+12%', trendUp: true },
    { label: "Aujourd'hui", value: stats.value.usersToday, icon: icons.clock, color: 'emerald', trend: '+3', trendUp: true },
    { label: 'Cette semaine', value: stats.value.usersThisWeek, icon: icons.calendar, color: 'blue', trend: '+8%', trendUp: true },
    { label: 'Ce mois', value: stats.value.usersThisMonth, icon: icons.chart, color: 'amber', trend: '+15%', trendUp: true },
]);

const quickActions = [
    { label: 'Voir les utilisateurs', href: '/admin/users', icon: icons.users, desc: 'Gérer les comptes' },
];

const recentActivity = [
    { action: 'Nouvel utilisateur inscrit', time: 'Il y a 5 min', type: 'user', icon: icons.userAdd },
    { action: 'Prédication publiée', time: 'Il y a 30 min', type: 'sermon', icon: icons.mic },
    { action: 'Nouveau commentaire', time: 'Il y a 1h', type: 'comment', icon: icons.chat },
    { action: 'Mise à jour du profil', time: 'Il y a 2h', type: 'update', icon: icons.refresh },
];

const activityColors = {
    user: 'bg-primary/10 text-primary',
    sermon: 'bg-blue-100 text-blue-600',
    comment: 'bg-emerald-100 text-emerald-600',
    update: 'bg-amber-100 text-amber-600',
};

// Chart filter options
const filterOptions = [
    { label: 'Semaine', value: 'this_week' },
    { label: 'Ce mois', value: 'this_month' },
    { label: 'Mois dernier', value: 'last_month' },
    { label: '3 mois', value: 'last_3_months' },
];

// --- Users chart ---
const usersChart = ref({ labels: [], data: [], total: 0, trend: '', trendUp: true });
const usersFilter = ref('this_month');
const usersLoading = ref(false);
const usersStartDate = ref('');
const usersEndDate = ref('');

// --- Churches chart ---
const churchesChart = ref({ labels: [], data: [], total: 0, trend: '', trendUp: true });
const churchesFilter = ref('this_month');
const churchesLoading = ref(false);
const churchesStartDate = ref('');
const churchesEndDate = ref('');

// --- Sermons chart ---
const sermonsChart = ref({ labels: [], data: [], total: 0, trend: '', trendUp: true });
const sermonsFilter = ref('this_month');
const sermonsLoading = ref(false);
const sermonsStartDate = ref('');
const sermonsEndDate = ref('');

const fetchChartData = async (type, filterRef, loadingRef, chartRef, startDate = '', endDate = '') => {
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
        console.error(`Chart fetch error (${type}):`, e);
    } finally {
        loadingRef.value = false;
    }
};

const onUsersFilterChange = ({ filter, startDate, endDate }) => {
    fetchChartData('users', usersFilter, usersLoading, usersChart, startDate, endDate);
};
const onChurchesFilterChange = ({ filter, startDate, endDate }) => {
    fetchChartData('churches', churchesFilter, churchesLoading, churchesChart, startDate, endDate);
};
const onSermonsFilterChange = ({ filter, startDate, endDate }) => {
    fetchChartData('sermons', sermonsFilter, sermonsLoading, sermonsChart, startDate, endDate);
};

onMounted(() => {
    fetchChartData('users', usersFilter, usersLoading, usersChart);
    fetchChartData('churches', churchesFilter, churchesLoading, churchesChart);
    fetchChartData('sermons', sermonsFilter, sermonsLoading, sermonsChart);
});
</script>

<template>
    <AdminLayout title="Dashboard">
        <div class="space-y-6">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[{ label: 'Dashboard' }]" />

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <!-- Skeleton -->
                <template v-if="pageLoading">
                    <div v-for="n in 4" :key="'skel-stat-'+n" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 px-4 py-3.5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-9 h-9 rounded-xl bg-gray-100 animate-pulse"></div>
                            <div class="w-12 h-4 rounded-full bg-gray-100 animate-pulse"></div>
                        </div>
                        <div class="h-7 w-20 bg-gray-100 rounded-lg animate-pulse mb-1.5"></div>
                        <div class="h-3 w-28 bg-gray-50 rounded animate-pulse"></div>
                    </div>
                </template>
                <!-- Actual cards -->
                <template v-else>
                    <StatCard
                        v-for="stat in statCards"
                        :key="stat.label"
                        :value="stat.value"
                        :label="stat.label"
                        :icon="stat.icon"
                        :color="stat.color"
                        :trend="stat.trend"
                        :trend-up="stat.trendUp"
                    />
                </template>
            </div>

            <!-- Bottom Grid: Activity + Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Skeleton Activity -->
                <template v-if="pageLoading">
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                            <div class="h-4 w-32 bg-gray-100 rounded animate-pulse mb-1.5"></div>
                            <div class="h-3 w-52 bg-gray-50 rounded animate-pulse"></div>
                        </div>
                        <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <div v-for="n in 4" :key="'skel-act-'+n" class="flex items-center gap-3 px-5 py-3">
                                <div class="w-8 h-8 rounded-lg bg-gray-100 animate-pulse shrink-0"></div>
                                <div class="flex-1">
                                    <div class="h-3.5 w-40 bg-gray-100 rounded animate-pulse mb-1"></div>
                                    <div class="h-2.5 w-20 bg-gray-50 rounded animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-5">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/80 dark:border-gray-700 p-5">
                            <div class="h-4 w-28 bg-gray-100 rounded animate-pulse mb-3"></div>
                            <div class="space-y-2">
                                <div class="h-12 bg-gray-50 rounded-xl animate-pulse"></div>
                            </div>
                        </div>
                        <div class="bg-gray-900 rounded-2xl p-5">
                            <div class="h-3 w-16 bg-gray-700 rounded animate-pulse mb-4"></div>
                            <div class="space-y-3">
                                <div v-for="n in 3" :key="'skel-sys-'+n">
                                    <div class="flex justify-between mb-1">
                                        <div class="h-2.5 w-14 bg-gray-700 rounded animate-pulse"></div>
                                        <div class="h-2.5 w-10 bg-gray-700 rounded animate-pulse"></div>
                                    </div>
                                    <div class="h-1 bg-gray-800 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else>
                <!-- Recent Activity -->
                <Card
                    class="lg:col-span-2"
                    title="Activité récente"
                    subtitle="Les dernières actions sur la plateforme"
                    no-padding
                >
                    <template #header-actions>
                        <button class="text-[11px] font-semibold text-primary hover:text-primary-dark transition-colors">
                            Tout voir
                        </button>
                    </template>

                    <div class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        <div
                            v-for="(item, index) in recentActivity"
                            :key="index"
                            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors"
                        >
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-lg shrink-0"
                                :class="activityColors[item.type]"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ item.action }}</p>
                                <p class="text-[11px] text-gray-400">{{ item.time }}</p>
                            </div>
                            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </Card>

                <!-- Right column -->
                <div class="space-y-5">
                    <!-- Quick Actions -->
                    <Card title="Actions rapides">
                        <div class="space-y-2">
                            <Link
                                v-for="action in quickActions"
                                :key="action.label"
                                :href="action.href"
                                class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-primary/5 dark:hover:bg-primary/10 border border-gray-100 dark:border-gray-600 hover:border-primary/20 transition-all duration-200 group"
                            >
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ action.label }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ action.desc }}</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-500 group-hover:text-primary/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </Card>

                    <!-- System Overview -->
                    <Card dark no-padding>
                        <template #header>
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                                <h3 class="text-xs font-semibold text-white/90">Système</h3>
                            </div>
                        </template>

                        <div class="px-5 py-3.5 space-y-3">
                            <div>
                                <div class="flex justify-between text-[11px] mb-1">
                                    <span class="text-white/60">Serveur</span>
                                    <span class="text-green-400 font-medium">Actif</span>
                                </div>
                                <div class="h-1 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-linear-to-r from-green-400 to-emerald-500 rounded-full" style="width: 28%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-[11px] mb-1">
                                    <span class="text-white/60">Stockage</span>
                                    <span class="text-accent-warm font-medium">45%</span>
                                </div>
                                <div class="h-1 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-linear-to-r from-accent-warm to-amber-400 rounded-full" style="width: 45%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-[11px] mb-1">
                                    <span class="text-white/60">API</span>
                                    <span class="text-blue-400 font-medium">99.9%</span>
                                </div>
                                <div class="h-1 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-linear-to-r from-blue-400 to-cyan-400 rounded-full" style="width: 99%"></div>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
                </template>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                <!-- Users Evolution -->
                <LineChart
                    title="Évolution des utilisateurs"
                    subtitle="Inscriptions par jour"
                    :labels="usersChart.labels"
                    :data="usersChart.data"
                    color="#6B4EAF"
                    :total="usersChart.total"
                    :trend="usersChart.trend"
                    :trend-up="usersChart.trendUp"
                    :loading="usersLoading"
                    :icon="icons.users"
                    :filters="filterOptions"
                    v-model:active-filter="usersFilter"
                    v-model:start-date="usersStartDate"
                    v-model:end-date="usersEndDate"
                    show-custom-period
                    @filter-change="onUsersFilterChange"
                />

                <!-- Churches Evolution -->
                <LineChart
                    title="Évolution des églises"
                    subtitle="Nouvelles églises par jour"
                    :labels="churchesChart.labels"
                    :data="churchesChart.data"
                    color="#10B981"
                    :total="churchesChart.total"
                    :trend="churchesChart.trend"
                    :trend-up="churchesChart.trendUp"
                    :loading="churchesLoading"
                    :icon="icons.church"
                    :filters="filterOptions"
                    v-model:active-filter="churchesFilter"
                    v-model:start-date="churchesStartDate"
                    v-model:end-date="churchesEndDate"
                    show-custom-period
                    @filter-change="onChurchesFilterChange"
                />

                <!-- Sermons Evolution (full width) -->
                <LineChart
                    class="xl:col-span-2"
                    title="Évolution des prédications"
                    subtitle="Publications par jour"
                    :labels="sermonsChart.labels"
                    :data="sermonsChart.data"
                    color="#3B82F6"
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
                    height="280px"
                />
            </div>
        </div>
    </AdminLayout>
</template>
