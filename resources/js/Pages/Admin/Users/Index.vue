<script setup>
import { ref, watch, onMounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const { t } = useI18n();

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');
const pageLoading = ref(true);
const tableLoading = ref(false);

onMounted(() => {
    setTimeout(() => { pageLoading.value = false; }, 350);
});

const buildParams = () => ({
    search: search.value || undefined,
    sort_by: sortBy.value || undefined,
    sort_direction: sortDirection.value || undefined,
});

const navigate = () => {
    tableLoading.value = true;
    router.get('/admin/users', buildParams(), {
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

const handleSort = ({ key, direction }) => {
    sortBy.value = key;
    sortDirection.value = direction;
    navigate();
};

const columns = [
    { key: 'name', label: t('users.user'), sortable: true },
    { key: 'role', label: t('users.role'), sortable: true },
    { key: 'phone', label: t('users.phone'), hidden: 'hidden md:table-cell', sortable: true },
    { key: 'email_verified', label: t('users.emailVerified'), hidden: 'hidden sm:table-cell', sortable: true },
    { key: 'created_at', label: t('users.registered'), hidden: 'hidden lg:table-cell', sortable: true },
];

const roleColors = {
    admin: 'bg-red-50 text-red-700 ring-1 ring-red-600/10',
    moderator: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/10',
    church_admin: 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/10',
    independent_preacher: 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/10',
    user: 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/10',
    Utilisateur: 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/10',
};

const getRoleColor = (role) => {
    return roleColors[role] || 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/10';
};
</script>

<template>
    <AdminLayout :title="t('users.title')">
        <div class="space-y-6">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: t('users.title') },
            ]" />

            <!-- Skeleton: Header + Search + Table -->
            <template v-if="pageLoading">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="h-7 w-44 bg-gray-200 rounded-lg animate-pulse"></div>
                        <div class="h-4 w-64 bg-gray-100 rounded animate-pulse mt-2"></div>
                    </div>
                    <div class="h-9 w-36 bg-primary/10 rounded-xl animate-pulse"></div>
                </div>
                <div class="h-11 bg-white rounded-xl border border-gray-200 animate-pulse"></div>
                <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden">
                    <div class="border-b border-gray-100 px-5 py-3 flex gap-6">
                        <div v-for="n in 5" :key="'sh-'+n" class="h-3.5 rounded animate-pulse bg-gray-100" :style="{ width: (50 + n * 18) + 'px' }"></div>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-for="n in 6" :key="'sr-'+n" class="flex items-center gap-4 px-5 py-3.5">
                            <div class="w-10 h-10 rounded-full bg-gray-100 animate-pulse shrink-0"></div>
                            <div class="flex-1 space-y-1.5">
                                <div class="h-3.5 w-32 bg-gray-100 rounded animate-pulse"></div>
                                <div class="h-2.5 w-44 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                            <div class="hidden md:block h-5 w-16 bg-gray-100 rounded-lg animate-pulse"></div>
                            <div class="hidden sm:block h-5 w-14 bg-gray-100 rounded-lg animate-pulse"></div>
                            <div class="hidden lg:block space-y-1">
                                <div class="h-3 w-20 bg-gray-100 rounded animate-pulse"></div>
                                <div class="h-2.5 w-16 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Actual content -->
            <template v-else>
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">{{ t('users.title') }}</h1>
                    <p class="text-gray-500 mt-1 text-sm">{{ t('users.subtitle') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-xl text-sm font-bold ring-1 ring-primary/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        {{ users.total }} {{ t('users.userCount', users.total) }}
                    </span>
                </div>
            </div>

            <!-- Search -->
            <div class="flex flex-col sm:flex-row gap-3">
                <SearchInput
                    v-model="search"
                    :placeholder="t('users.searchPlaceholder')"
                />
            </div>

            <!-- Table -->
            <DataTable
                :loading="tableLoading"
                :columns="columns"
                :rows="users.data"
                :pagination="users"
                :sort-by="sortBy"
                :sort-direction="sortDirection"
                :empty-title="t('users.noUserFound')"
                :empty-subtitle="search ? t('common.noResults') : ''"
                empty-icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                @sort="handleSort"
            >
                <!-- User cell -->
                <template #cell-name="{ row }">
                    <div class="flex items-center gap-3.5">
                        <div class="flex items-center justify-center w-10 h-10 bg-linear-to-br from-primary/15 to-primary/5 rounded-full shrink-0 ring-2 ring-primary/10 group-hover:ring-primary/20 transition-all">
                            <span class="text-sm font-bold text-primary">
                                {{ row.name?.charAt(0)?.toUpperCase() }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ row.name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ row.email }}</p>
                        </div>
                    </div>
                </template>

                <!-- Role cell -->
                <template #cell-role="{ row }">
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold capitalize"
                        :class="getRoleColor(row.role)"
                    >
                        {{ row.role }}
                    </span>
                </template>

                <!-- Phone cell -->
                <template #cell-phone="{ row }">
                    <span class="text-sm text-gray-600 font-medium">{{ row.phone || '—' }}</span>
                </template>

                <!-- Email verified cell -->
                <template #cell-email_verified="{ row }">
                    <span v-if="row.email_verified" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-xs font-semibold">{{ t('common.verified') }}</span>
                    </span>
                    <span v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-50 text-gray-400 ring-1 ring-gray-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium">{{ t('common.notVerified') }}</span>
                    </span>
                </template>

                <!-- Created at cell -->
                <template #cell-created_at="{ row }">
                    <div>
                        <p class="text-sm text-gray-700 font-medium">{{ row.created_at }}</p>
                        <p class="text-xs text-gray-400">{{ row.created_at_human }}</p>
                    </div>
                </template>
            </DataTable>
            </template>
        </div>
    </AdminLayout>
</template>
