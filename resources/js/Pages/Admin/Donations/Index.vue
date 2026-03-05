<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    donations: Object,
    stats: Object,
    filters: Object,
});

const { t } = useI18n();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');
const pageLoading = ref(true);
const tableLoading = ref(false);

onMounted(() => {
    setTimeout(() => { pageLoading.value = false; }, 350);
});

const buildParams = () => ({
    search: search.value || undefined,
    status: statusFilter.value || undefined,
    sort_by: sortBy.value || undefined,
    sort_direction: sortDirection.value || undefined,
});

const navigate = () => {
    tableLoading.value = true;
    router.get('/admin/donations', buildParams(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onFinish: () => { tableLoading.value = false; },
    });
};

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(navigate, 300);
});

watch(statusFilter, () => {
    navigate();
});

const handleSort = ({ key, direction }) => {
    sortBy.value = key;
    sortDirection.value = direction;
    navigate();
};

const columns = computed(() => [
    { key: 'donor', label: t('donations.donor') },
    { key: 'recipient', label: t('donations.recipient'), hidden: 'hidden md:table-cell' },
    { key: 'amount', label: t('donations.amount'), sortable: true },
    { key: 'status', label: t('donations.status'), sortable: true },
    { key: 'created_at', label: t('donations.date'), sortable: true, hidden: 'hidden lg:table-cell' },
]);

const statusFilters = computed(() => [
    { key: '', label: t('common.all') },
    { key: 'completed', label: t('donations.completed') },
    { key: 'pending', label: t('donations.pending') },
    { key: 'failed', label: t('donations.failed') },
]);

const statusStyles = computed(() => ({
    completed: { bg: 'bg-emerald-50 text-emerald-700 ring-emerald-600/10', dot: 'bg-emerald-500', label: t('donations.completed') },
    pending: { bg: 'bg-amber-50 text-amber-700 ring-amber-600/10', dot: 'bg-amber-500', label: t('donations.pending') },
    failed: { bg: 'bg-red-50 text-red-700 ring-red-600/10', dot: 'bg-red-500', label: t('donations.failed') },
}));

const getStatus = (status) => statusStyles.value[status] || statusStyles.value.pending;

const recipientIcon = (type) => {
    return type === 'church'
        ? 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        : 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z';
};

const statCards = computed(() => [
    {
        label: t('donations.total'),
        value: props.stats?.total ?? 0,
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        color: 'primary',
    },
    {
        label: t('donations.completed'),
        value: props.stats?.completed ?? 0,
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'emerald',
    },
    {
        label: t('donations.pending'),
        value: props.stats?.pending ?? 0,
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'amber',
    },
    {
        label: t('donations.totalAmount'),
        value: props.stats?.total_amount ?? '0 CDF',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'blue',
        isText: true,
    },
]);

const statColorMap = {
    primary: {
        bg: 'bg-primary/10',
        text: 'text-primary',
    },
    emerald: {
        bg: 'bg-emerald-50',
        text: 'text-emerald-600',
    },
    amber: {
        bg: 'bg-amber-50',
        text: 'text-amber-600',
    },
    blue: {
        bg: 'bg-blue-50',
        text: 'text-blue-600',
    },
};
</script>

<template>
    <AdminLayout :title="t('donations.title')">
        <div class="space-y-5">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[{ label: t('donations.title') }]" />

            <!-- Skeleton -->
            <template v-if="pageLoading">
                <!-- Skeleton banner -->
                <div class="relative overflow-hidden rounded-2xl bg-linear-to-r from-emerald-500/6 via-emerald-500/3 to-transparent border border-emerald-500/10 p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl bg-emerald-100/60 animate-pulse"></div>
                            <div class="space-y-1.5">
                                <div class="h-5 w-36 bg-gray-200/80 rounded-lg animate-pulse"></div>
                                <div class="h-3 w-56 bg-gray-100 rounded animate-pulse"></div>
                            </div>
                        </div>
                        <div class="h-8 w-28 bg-emerald-100/40 rounded-full animate-pulse hidden sm:block"></div>
                    </div>
                </div>
                <!-- Skeleton stat cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div v-for="n in 4" :key="'ss-'+n" class="bg-white rounded-xl border border-gray-100 p-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 animate-pulse shrink-0"></div>
                            <div class="flex-1 space-y-1.5">
                                <div class="h-5 w-12 bg-gray-200/80 rounded animate-pulse"></div>
                                <div class="h-2.5 w-20 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Skeleton toolbar -->
                <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm p-2 flex flex-col sm:flex-row gap-2">
                    <div class="flex gap-1 p-0.5">
                        <div v-for="n in 4" :key="'sf-'+n" class="h-8 rounded-lg bg-gray-100 animate-pulse" :style="{ width: (50 + n * 14) + 'px' }"></div>
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
                            <div class="w-8 h-8 rounded-full bg-gray-100/80 animate-pulse shrink-0"></div>
                            <div class="flex-1 space-y-1.5">
                                <div class="h-3.5 bg-gray-100/80 rounded animate-pulse" :style="{ width: (90 + n * 18) + 'px' }"></div>
                                <div class="h-2.5 w-24 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                            <div class="hidden md:block h-3 w-20 bg-gray-50 rounded animate-pulse"></div>
                            <div class="h-4 w-14 bg-gray-100/60 rounded-full animate-pulse"></div>
                            <div class="hidden lg:block h-3 w-16 bg-gray-50 rounded animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Actual content -->
            <template v-else>

            <!-- Hero banner -->
            <div class="relative overflow-hidden rounded-2xl bg-linear-to-r from-emerald-500/6 via-emerald-500/3 to-transparent border border-emerald-500/10">
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-emerald-500/5 rounded-full blur-2xl"></div>
                <div class="relative px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900 leading-tight">{{ t('donations.title') }}</h1>
                            <p class="text-xs text-gray-500 mt-0.5">{{ t('donations.subtitle') }}</p>
                        </div>
                    </div>
                    <span
                        v-if="donations"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500/10 text-emerald-700 rounded-full text-xs font-bold ring-1 ring-emerald-500/15 shrink-0 self-start sm:self-auto"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500/60"></span>
                        {{ t('donations.donationCount', { count: donations.total }) }}
                    </span>
                </div>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div
                    v-for="stat in statCards"
                    :key="stat.label"
                    class="bg-white rounded-xl border border-gray-100 p-3.5 hover:shadow-sm transition-shadow"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex items-center justify-center w-9 h-9 rounded-lg shrink-0"
                            :class="[statColorMap[stat.color].bg, statColorMap[stat.color].text]"
                        >
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="stat.icon" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-lg font-bold text-gray-900 leading-tight truncate" :class="{ 'text-sm': stat.isText }">
                                {{ stat.value }}
                            </p>
                            <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar: Filters + Search -->
            <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 p-2">
                    <!-- Status filters -->
                    <div class="bg-gray-100/80 rounded-xl p-1 inline-flex gap-1 shrink-0">
                        <button
                            v-for="f in statusFilters"
                            :key="f.key"
                            @click="statusFilter = f.key"
                            class="relative flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all duration-200"
                            :class="statusFilter === f.key
                                ? 'bg-primary text-white shadow-md shadow-primary/30'
                                : 'text-gray-500 hover:text-gray-700 hover:bg-white/60'"
                        >
                            {{ f.label }}
                            <span
                                v-if="statusFilter === f.key && donations"
                                class="ml-0.5 inline-flex items-center justify-center min-w-4.5 h-4.5 px-1 rounded-full bg-white/20 text-[10px] font-bold leading-none"
                            >
                                {{ donations.total }}
                            </span>
                        </button>
                    </div>
                    <!-- Search -->
                    <div class="flex-1">
                        <SearchInput
                            v-model="search"
                            :placeholder="t('donations.searchPlaceholder')"
                        />
                    </div>
                </div>
            </div>

            <!-- Donations Table -->
            <DataTable
                :loading="tableLoading"
                skeleton-on-load
                :columns="columns"
                :rows="donations?.data ?? []"
                :pagination="donations"
                :sort-by="sortBy"
                :sort-direction="sortDirection"
                :empty-title="t('donations.noDonation')"
                :empty-subtitle="search || statusFilter ? t('common.tryOtherSearch') : t('donations.noDonationSubtitle')"
                empty-icon="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                @sort="handleSort"
            >
                <!-- Donor cell -->
                <template #cell-donor="{ row }">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-8 h-8 bg-linear-to-br from-primary/12 to-primary/4 rounded-full shrink-0">
                            <span class="text-[11px] font-bold text-primary">
                                {{ row.donor_name?.charAt(0)?.toUpperCase() }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-gray-900 truncate leading-tight">{{ row.donor_name }}</p>
                            <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ row.phone_number }}</p>
                        </div>
                    </div>
                </template>

                <!-- Recipient cell -->
                <template #cell-recipient="{ row }">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="recipientIcon(row.recipient_type)" />
                        </svg>
                        <span class="text-[13px] text-gray-600 truncate">{{ row.recipient_name }}</span>
                    </div>
                </template>

                <!-- Amount cell -->
                <template #cell-amount="{ row }">
                    <div class="text-right">
                        <p class="text-[13px] font-bold" :class="row.status === 'completed' ? 'text-emerald-700' : 'text-gray-700'">
                            {{ row.formatted_amount }}
                        </p>
                        <span
                            v-if="row.is_sandbox"
                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-orange-100 text-orange-600 mt-0.5"
                        >
                            TEST
                        </span>
                    </div>
                </template>

                <!-- Status cell -->
                <template #cell-status="{ row }">
                    <span
                        class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-[11px] font-semibold ring-1"
                        :class="getStatus(row.status).bg"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="getStatus(row.status).dot"></span>
                        {{ getStatus(row.status).label }}
                    </span>
                </template>

                <!-- Created at cell -->
                <template #cell-created_at="{ row }">
                    <span class="text-[13px] text-gray-500">{{ row.created_at_human || row.created_at }}</span>
                </template>
            </DataTable>
            </template>
        </div>
    </AdminLayout>
</template>
