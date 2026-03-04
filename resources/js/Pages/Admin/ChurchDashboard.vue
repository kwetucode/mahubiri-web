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
const church = computed(() => page.props.church);
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
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    plus: 'M12 4v16m8-8H4',
    music: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3',
    trophy: 'M5 3l14 0M5 3v4a7.012 7.012 0 004 6.32V17H9a2 2 0 00-2 2v2h10v-2a2 2 0 00-2-2h-1v-3.68A7.012 7.012 0 0019 7V3',
};

const statCards = computed(() => [
    { label: 'Total prédications', value: stats.value.totalSermons, icon: icons.mic, color: 'primary' },
    { label: 'Publiées', value: stats.value.publishedSermons, icon: icons.published, color: 'emerald' },
    { label: 'Brouillons', value: stats.value.draftSermons, icon: icons.draft, color: 'amber' },
    { label: 'Vues totales', value: stats.value.totalViews, icon: icons.eye, color: 'blue' },
]);

// Sermons chart
const sermonsChart = ref({ labels: [], data: [], total: 0, trend: '', trendUp: true });
const sermonsFilter = ref('this_month');
const sermonsLoading = ref(false);
const sermonsStartDate = ref('');
const sermonsEndDate = ref('');

const filterOptions = [
    { label: 'Semaine', value: 'this_week' },
    { label: 'Ce mois', value: 'this_month' },
    { label: 'Mois dernier', value: 'last_month' },
    { label: '3 mois', value: 'last_3_months' },
];

const fetchChartData = async (startDate = '', endDate = '') => {
    sermonsLoading.value = true;
    try {
        const params = { type: 'sermons', filter: sermonsFilter.value };
        if (sermonsFilter.value === 'custom' && startDate && endDate) {
            params.start_date = startDate;
            params.end_date = endDate;
        }
        const { data } = await axios.get('/admin/dashboard/chart-data', { params });
        sermonsChart.value = data;
    } catch (e) {
        console.error('Chart fetch error:', e);
    } finally {
        sermonsLoading.value = false;
    }
};

const onSermonsFilterChange = ({ filter, startDate, endDate }) => {
    fetchChartData(startDate, endDate);
};

onMounted(() => {
    fetchChartData();
});
</script>

<template>
    <AdminLayout title="Dashboard">
        <div class="space-y-6">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[{ label: 'Dashboard' }]" />

            <!-- Welcome banner -->
            <div class="relative overflow-hidden bg-linear-to-r from-primary via-primary-dark to-[#3a2570] rounded-2xl p-6 lg:p-7">
                <div class="absolute top-0 right-0 w-48 h-48 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4 blur-2xl"></div>
                <div class="absolute bottom-0 left-1/3 w-36 h-36 bg-accent-warm/10 rounded-full translate-y-1/2 blur-2xl"></div>
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 24px 24px;"></div>

                <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/15 backdrop-blur-sm text-white/90 text-[11px] font-medium border border-white/10">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                                {{ church.name }}
                            </span>
                        </div>
                        <h1 class="text-xl lg:text-2xl font-extrabold text-white mb-1">
                            Bienvenue, {{ $page.props.auth.user?.name }}
                        </h1>
                        <p class="text-white/60 text-sm max-w-lg">
                            Gérez les prédications de votre église. Publiez, modifiez et suivez vos contenus.
                        </p>
                    </div>
                    <Link
                        href="/admin/sermons/create"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/15 backdrop-blur-sm text-white rounded-xl text-xs font-semibold border border-white/20 hover:bg-white/25 transition-all duration-200 shadow-lg shadow-black/10 shrink-0"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.plus" />
                        </svg>
                        Nouvelle prédication
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <template v-if="pageLoading">
                    <div v-for="n in 4" :key="'skel-stat-'+n" class="bg-white rounded-xl border border-gray-100 px-4 py-3.5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-9 h-9 rounded-xl bg-gray-100 animate-pulse"></div>
                            <div class="w-12 h-4 rounded-full bg-gray-100 animate-pulse"></div>
                        </div>
                        <div class="h-7 w-20 bg-gray-100 rounded-lg animate-pulse mb-1.5"></div>
                        <div class="h-3 w-28 bg-gray-50 rounded animate-pulse"></div>
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

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
                <!-- Latest sermons -->
                <Card
                    class="lg:col-span-3"
                    title="Dernières prédications"
                    subtitle="Les 5 prédications les plus récentes"
                    no-padding
                >
                    <template #header-actions>
                        <Link href="/admin/sermons" class="text-[11px] font-semibold text-primary hover:text-primary-dark transition-colors">
                            Tout voir
                        </Link>
                    </template>

                    <div v-if="pageLoading" class="divide-y divide-gray-50">
                        <div v-for="n in 5" :key="'skel-s-'+n" class="flex items-center gap-3 px-5 py-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 animate-pulse shrink-0"></div>
                            <div class="flex-1">
                                <div class="h-3.5 w-40 bg-gray-100 rounded animate-pulse mb-1"></div>
                                <div class="h-2.5 w-24 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="latestSermons.length === 0" class="px-5 py-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="icons.music" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Aucune prédication</p>
                        <p class="text-xs text-gray-400 mt-1">Publiez votre première prédication</p>
                        <Link
                            href="/admin/sermons/create"
                            class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 text-xs font-semibold text-primary bg-primary/5 rounded-lg hover:bg-primary/10 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.plus" />
                            </svg>
                            Créer
                        </Link>
                    </div>

                    <div v-else class="divide-y divide-gray-50">
                        <Link
                            v-for="sermon in latestSermons"
                            :key="sermon.id"
                            :href="`/admin/sermons/${sermon.id}/edit`"
                            class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/50 transition-colors group"
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
                                <p class="text-sm font-medium text-gray-800 truncate group-hover:text-primary transition-colors">{{ sermon.title }}</p>
                                <div class="flex items-center gap-2 text-[11px] text-gray-400">
                                    <span>{{ sermon.preacher_name }}</span>
                                    <span class="text-gray-200">•</span>
                                    <span>{{ sermon.created_at_human }}</span>
                                    <span v-if="sermon.duration_formatted" class="text-gray-200">•</span>
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
                    <Card title="Actions rapides">
                        <div class="space-y-2">
                            <Link
                                href="/admin/sermons/create"
                                class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 hover:bg-primary/5 border border-gray-100 hover:border-primary/20 transition-all duration-200 group"
                            >
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.plus" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-800 group-hover:text-primary transition-colors">Nouvelle prédication</p>
                                    <p class="text-[11px] text-gray-400">Publier un nouveau sermon</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-primary/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                            <Link
                                href="/admin/sermons"
                                class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 hover:bg-primary/5 border border-gray-100 hover:border-primary/20 transition-all duration-200 group"
                            >
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icons.music" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-800 group-hover:text-primary transition-colors">Toutes les prédications</p>
                                    <p class="text-[11px] text-gray-400">Gérer vos sermons</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-primary/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </Card>

                    <!-- Top sermons (most viewed) -->
                    <Card title="Top prédications" subtitle="Les plus écoutées" no-padding>
                        <div v-if="topSermons.length === 0" class="px-5 py-6 text-center">
                            <p class="text-xs text-gray-400">Aucune donnée disponible</p>
                        </div>
                        <div v-else class="divide-y divide-gray-50">
                            <div
                                v-for="(sermon, i) in topSermons"
                                :key="sermon.id"
                                class="flex items-center gap-3 px-5 py-2.5"
                            >
                                <span
                                    class="flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold shrink-0"
                                    :class="i === 0 ? 'bg-amber-100 text-amber-700' : i === 1 ? 'bg-gray-100 text-gray-600' : i === 2 ? 'bg-orange-50 text-orange-600' : 'bg-gray-50 text-gray-400'"
                                >
                                    {{ i + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-800 truncate">{{ sermon.title }}</p>
                                    <p class="text-[10px] text-gray-400">{{ sermon.preacher_name }}</p>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-500 shrink-0">{{ sermon.views_count }} vues</span>
                            </div>
                        </div>
                    </Card>

                    <!-- Summary card -->
                    <Card dark no-padding>
                        <template #header>
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                                <h3 class="text-xs font-semibold text-white/90">Résumé</h3>
                            </div>
                        </template>
                        <div class="px-5 py-3.5 space-y-3">
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">Cette semaine</span>
                                <span class="text-blue-400 font-medium">{{ stats.sermonsThisWeek }} prédication{{ stats.sermonsThisWeek > 1 ? 's' : '' }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">Ce mois</span>
                                <span class="text-emerald-400 font-medium">{{ stats.sermonsThisMonth }} prédication{{ stats.sermonsThisMonth > 1 ? 's' : '' }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">Taux de publication</span>
                                <span class="text-accent-warm font-medium">
                                    {{ stats.totalSermons > 0 ? Math.round((stats.publishedSermons / stats.totalSermons) * 100) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>

            <!-- Sermons Evolution Chart -->
            <LineChart
                title="Évolution des prédications"
                subtitle="Publications par jour"
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
                height="280px"
            />
        </div>
    </AdminLayout>
</template>
