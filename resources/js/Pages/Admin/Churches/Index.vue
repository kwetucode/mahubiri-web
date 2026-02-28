<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Toggle from '@/Components/Toggle.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import axios from 'axios';

const props = defineProps({
    churches: Object,
    preachers: Object,
    filters: Object,
});

const activeTab = ref(props.filters?.tab || 'churches');
const search = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');
const pageLoading = ref(true);
const tableLoading = ref(false);

onMounted(() => {
    setTimeout(() => { pageLoading.value = false; }, 350);
});

const buildParams = () => ({
    tab: activeTab.value,
    search: search.value || undefined,
    sort_by: sortBy.value || undefined,
    sort_direction: sortDirection.value || undefined,
});

const navigate = () => {
    tableLoading.value = true;
    router.get('/admin/churches', buildParams(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => { tableLoading.value = false; },
    });
};

const switchTab = (tab) => {
    activeTab.value = tab;
    search.value = '';
    sortBy.value = 'created_at';
    sortDirection.value = 'desc';
    navigate();
};

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(navigate, 300);
});

const handleSort = ({ key, direction }) => {
    sortBy.value = key;
    sortDirection.value = direction;
    navigate();
};

// --- Churches columns ---
const churchColumns = [
    { key: 'name', label: 'Église', sortable: true },
    { key: 'city', label: 'Localisation', sortable: true, hidden: 'hidden md:table-cell' },
    { key: 'sermons_count', label: 'Prédications' },
    { key: 'is_active', label: 'Statut', sortable: true, hidden: 'hidden sm:table-cell' },
    { key: 'created_at', label: 'Inscrit', sortable: true, hidden: 'hidden lg:table-cell' },
];

// --- Preachers columns ---
const preacherColumns = [
    { key: 'user_name', label: 'Prédicateur' },
    { key: 'ministry_type', label: 'Ministère', hidden: 'hidden md:table-cell' },
    { key: 'city', label: 'Localisation', sortable: true, hidden: 'hidden md:table-cell' },
    { key: 'sermons_count', label: 'Prédications' },
    { key: 'is_active', label: 'Statut', sortable: true, hidden: 'hidden sm:table-cell' },
    { key: 'created_at', label: 'Inscrit', sortable: true, hidden: 'hidden lg:table-cell' },
];

const currentData = computed(() => {
    return activeTab.value === 'churches' ? props.churches : props.preachers;
});

const currentRows = computed(() => {
    return currentData.value?.data ?? [];
});

const isLoading = computed(() => {
    return tableLoading.value || !currentData.value;
});

const currentColumns = computed(() => {
    return activeTab.value === 'churches' ? churchColumns : preacherColumns;
});

const totalLabel = computed(() => {
    if (!currentData.value) return '';
    const total = currentData.value.total;
    return activeTab.value === 'churches'
        ? `${total} église${total > 1 ? 's' : ''}`
        : `${total} prédicateur${total > 1 ? 's' : ''}`;
});

const ministryColors = {
    pasteur: 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/10',
    apotre: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/10',
    evangeliste: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10',
    prophete: 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/10',
    enseignant: 'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-600/10',
    docteur: 'bg-red-50 text-red-700 ring-1 ring-red-600/10',
};

const getMinistryColor = (type) => {
    return ministryColors[type] || 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/10';
};

const tabs = [
    {
        key: 'churches',
        label: 'Églises',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    },
    {
        key: 'preachers',
        label: 'Prédicateurs indépendants',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    },
];

// --- Toggle active state ---
const showConfirmModal = ref(false);
const toggleTarget = ref(null);
const toggleLoading = ref(false);

const requestToggle = (row, type = null) => {
    toggleTarget.value = row;
    toggleTarget.value._type = type || activeTab.value;
    if (row.is_active) {
        showConfirmModal.value = true;
    } else {
        performToggle(row);
    }
};

const confirmToggle = () => {
    if (toggleTarget.value) {
        performToggle(toggleTarget.value);
    }
};

const cancelToggle = () => {
    showConfirmModal.value = false;
    toggleTarget.value = null;
};

const performToggle = async (row) => {
    toggleLoading.value = true;
    try {
        const type = row._type || activeTab.value;
        const url = type === 'churches'
            ? `/admin/churches/${row.id}/toggle-active`
            : `/admin/preachers/${row.id}/toggle-active`;
        const { data } = await axios.patch(url);
        row.is_active = data.is_active;
    } catch (e) {
        console.error('Toggle error:', e);
    } finally {
        toggleLoading.value = false;
        showConfirmModal.value = false;
        toggleTarget.value = null;
    }
};
</script>

<template>
    <AdminLayout title="Églises & Prédicateurs">
        <div class="space-y-5">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: activeTab === 'churches' ? 'Églises' : 'Prédicateurs indépendants' },
            ]" />

            <!-- Skeleton -->
            <template v-if="pageLoading">
                <!-- Skeleton banner -->
                <div class="relative overflow-hidden rounded-2xl bg-linear-to-r from-primary/5 via-primary/3 to-transparent border border-primary/10 p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-primary/10 animate-pulse"></div>
                            <div class="space-y-1.5">
                                <div class="h-5 w-48 bg-gray-200/80 rounded-lg animate-pulse"></div>
                                <div class="h-3 w-64 bg-gray-100 rounded animate-pulse"></div>
                            </div>
                        </div>
                        <div class="h-8 w-28 bg-primary/8 rounded-full animate-pulse hidden sm:block"></div>
                    </div>
                </div>
                <!-- Skeleton toolbar -->
                <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-2 flex flex-col sm:flex-row gap-2">
                    <div class="flex gap-1 p-0.5">
                        <div class="h-9 w-24 bg-gray-100 rounded-xl animate-pulse"></div>
                        <div class="h-9 w-44 bg-gray-50 rounded-xl animate-pulse"></div>
                    </div>
                    <div class="flex-1 h-9 bg-gray-50 rounded-xl animate-pulse"></div>
                </div>
                <!-- Skeleton table -->
                <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 px-5 py-3.5 flex gap-8">
                        <div v-for="n in 5" :key="'sh-'+n" class="h-3 rounded bg-gray-100 animate-pulse" :style="{ width: (40 + n * 16) + 'px' }"></div>
                    </div>
                    <div class="divide-y divide-gray-50/80">
                        <div v-for="n in 6" :key="'sr-'+n" class="flex items-center gap-4 px-5 py-3.5">
                            <div class="w-9 h-9 rounded-xl bg-gray-100/80 animate-pulse shrink-0"></div>
                            <div class="flex-1 space-y-1.5">
                                <div class="h-3.5 bg-gray-100/80 rounded animate-pulse" :style="{ width: (100 + n * 20) + 'px' }"></div>
                                <div class="h-2.5 w-20 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                            <div class="hidden md:block h-4 w-16 bg-gray-50 rounded animate-pulse"></div>
                            <div class="hidden sm:block h-5 w-11 bg-gray-100/60 rounded-full animate-pulse"></div>
                            <div class="hidden lg:block h-3 w-20 bg-gray-50 rounded animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Actual content -->
            <template v-else>

            <!-- Hero banner -->
            <div class="relative overflow-hidden rounded-2xl bg-linear-to-r from-primary/6 via-primary/3 to-transparent border border-primary/10">
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-primary/5 rounded-full blur-2xl"></div>
                <div class="relative px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary/10 text-primary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="activeTab === 'churches'
                                    ? 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
                                    : 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900 leading-tight">Églises & Prédicateurs</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Gérer vos communautés et ministères indépendants</p>
                        </div>
                    </div>
                    <span
                        v-if="currentData"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary rounded-full text-xs font-bold ring-1 ring-primary/15 shrink-0 self-start sm:self-auto"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-primary/60"></span>
                        {{ totalLabel }}
                    </span>
                </div>
            </div>

            <!-- Toolbar: Tabs + Search -->
            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-2">
                    <!-- Tabs -->
                    <div class="bg-gray-100/80 rounded-xl p-1 inline-flex gap-1 shrink-0">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="switchTab(tab.key)"
                            class="relative flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200"
                            :class="activeTab === tab.key
                                ? 'bg-primary text-white shadow-md shadow-primary/30'
                                : 'text-gray-500 hover:text-gray-700 hover:bg-white/60'"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                            </svg>
                            {{ tab.label }}
                            <span
                                v-if="activeTab === tab.key && currentData"
                                class="ml-0.5 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full bg-white/20 text-[10px] font-bold leading-none"
                            >
                                {{ currentData.total }}
                            </span>
                        </button>
                    </div>
                    <!-- Search -->
                    <div class="flex-1">
                        <SearchInput
                            v-model="search"
                            :placeholder="activeTab === 'churches'
                                ? 'Rechercher par nom, abréviation, visionnaire, ville...'
                                : 'Rechercher par nom, ministère, email, ville...'"
                        />
                    </div>
                </div>
            </div>

            <!-- Churches Table -->
            <DataTable
                :loading="isLoading"
                skeleton-on-load
                v-if="activeTab === 'churches'"
                :columns="churchColumns"
                :rows="currentRows"
                :pagination="churches"
                :sort-by="sortBy"
                :sort-direction="sortDirection"
                empty-title="Aucune église trouvée"
                :empty-subtitle="search ? 'Essayez un autre terme de recherche' : 'Aucune église enregistrée'"
                empty-icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                @sort="handleSort"
            >
                <!-- Church name cell -->
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3">
                        <div
                            v-if="row.logo_url"
                            class="w-9 h-9 rounded-lg overflow-hidden shrink-0 ring-1 ring-gray-200/80"
                        >
                            <img :src="row.logo_url" :alt="row.name" class="w-full h-full object-cover" />
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center w-9 h-9 bg-linear-to-br from-primary/12 to-primary/4 rounded-lg shrink-0"
                        >
                            <svg class="w-4 h-4 text-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-gray-900 truncate leading-tight">
                                {{ row.name }}
                                <span v-if="row.abbreviation" class="text-gray-400 font-normal text-xs">({{ row.abbreviation }})</span>
                            </p>
                            <p class="text-[11px] text-gray-400 truncate mt-0.5">
                                {{ row.visionary_name || row.created_by_name || '—' }}
                            </p>
                        </div>
                    </div>
                </template>

                <!-- Location cell -->
                <template #cell-city="{ row }">
                    <div v-if="row.city || row.country_name" class="flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-[13px] text-gray-600">{{ row.city || row.country_name }}</span>
                    </div>
                    <span v-else class="text-[13px] text-gray-300">—</span>
                </template>

                <!-- Sermons count cell -->
                <template #cell-sermons_count="{ row }">
                    <span class="inline-flex items-center gap-1 text-[13px] font-medium" :class="row.sermons_count > 0 ? 'text-blue-600' : 'text-gray-400'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                        {{ row.sermons_count }}
                    </span>
                </template>

                <!-- Status cell (church toggle) -->
                <template #cell-is_active="{ row }">
                    <Toggle
                        :model-value="row.is_active"
                        :loading="toggleLoading && toggleTarget?.id === row.id"
                        size="sm"
                        color="emerald"
                        @change="requestToggle(row)"
                    />
                </template>

                <!-- Created at cell -->
                <template #cell-created_at="{ row }">
                    <span class="text-[13px] text-gray-500">{{ row.created_at_human || row.created_at }}</span>
                </template>
            </DataTable>

            <!-- Preachers Table -->
            <DataTable
                :loading="isLoading"
                skeleton-on-load
                v-if="activeTab === 'preachers'"
                :columns="preacherColumns"
                :rows="currentRows"
                :pagination="preachers"
                :sort-by="sortBy"
                :sort-direction="sortDirection"
                empty-title="Aucun prédicateur trouvé"
                :empty-subtitle="search ? 'Essayez un autre terme de recherche' : 'Aucun prédicateur indépendant enregistré'"
                empty-icon="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                @sort="handleSort"
            >
                <!-- Preacher name cell -->
                <template #cell-user_name="{ row }">
                    <div class="flex items-center gap-3">
                        <div
                            v-if="row.avatar_url"
                            class="w-9 h-9 rounded-full overflow-hidden shrink-0 ring-1 ring-gray-200/80"
                        >
                            <img :src="row.avatar_url" :alt="row.user_name" class="w-full h-full object-cover" />
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center w-9 h-9 bg-linear-to-br from-amber-100/80 to-amber-50 rounded-full shrink-0"
                        >
                            <span class="text-xs font-bold text-amber-700">
                                {{ row.user_name?.charAt(0)?.toUpperCase() }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-gray-900 truncate leading-tight">{{ row.user_name }}</p>
                            <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ row.ministry_name }}</p>
                        </div>
                    </div>
                </template>

                <!-- Ministry type cell -->
                <template #cell-ministry_type="{ row }">
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold capitalize"
                        :class="getMinistryColor(row.ministry_type)"
                    >
                        {{ row.ministry_type_label }}
                    </span>
                </template>

                <!-- Location cell (preacher) -->
                <template #cell-city="{ row }">
                    <div v-if="row.city || row.country_name" class="flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-[13px] text-gray-600">{{ row.city || row.country_name }}</span>
                    </div>
                    <span v-else class="text-[13px] text-gray-300">—</span>
                </template>

                <!-- Sermons count cell -->
                <template #cell-sermons_count="{ row }">
                    <span class="inline-flex items-center gap-1 text-[13px] font-medium" :class="row.sermons_count > 0 ? 'text-blue-600' : 'text-gray-400'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                        {{ row.sermons_count }}
                    </span>
                </template>

                <!-- Status cell (preacher toggle) -->
                <template #cell-is_active="{ row }">
                    <Toggle
                        :model-value="row.is_active"
                        :loading="toggleLoading && toggleTarget?.id === row.id"
                        size="sm"
                        color="emerald"
                        @change="requestToggle(row, 'preachers')"
                    />
                </template>

                <!-- Created at cell -->
                <template #cell-created_at="{ row }">
                    <span class="text-[13px] text-gray-500">{{ row.created_at_human || row.created_at }}</span>
                </template>
            </DataTable>
            </template>
        </div>

        <!-- Confirm deactivation modal -->
        <ConfirmModal
            v-model:show="showConfirmModal"
            :title="toggleTarget?._type === 'preachers' ? 'Désactiver ce prédicateur ?' : 'Désactiver cette église ?'"
            :message="toggleTarget?._type === 'preachers'
                ? `Vous êtes sur le point de désactiver le prédicateur &laquo; ${toggleTarget?.user_name || toggleTarget?.ministry_name} &raquo;. Il ne sera plus visible pour les utilisateurs. Vous pourrez le réactiver à tout moment.`
                : `Vous êtes sur le point de désactiver l'église &laquo; ${toggleTarget?.name} &raquo;. Elle ne sera plus visible pour les utilisateurs. Vous pourrez la réactiver à tout moment.`"
            confirm-text="Désactiver"
            cancel-text="Annuler"
            variant="warning"
            :loading="toggleLoading"
            icon="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
            @confirm="confirmToggle"
            @cancel="cancelToggle"
        />
    </AdminLayout>
</template>
