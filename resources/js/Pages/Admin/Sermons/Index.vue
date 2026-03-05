<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Toggle from '@/Components/Toggle.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AudioPlayer from '@/Components/AudioPlayer.vue';
import axios from 'axios';

const props = defineProps({
    sermons: Object,
    stats: Object,
    church: Object,
    filters: Object,
    categories: Array,
});

const search = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');
const statusFilter = ref(props.filters?.status || '');
const categoryFilter = ref(props.filters?.category || '');
const pageLoading = ref(true);
const tableLoading = ref(false);

onMounted(() => {
    setTimeout(() => { pageLoading.value = false; }, 350);
});

const buildParams = () => ({
    search: search.value || undefined,
    sort_by: sortBy.value || undefined,
    sort_direction: sortDirection.value || undefined,
    status: statusFilter.value || undefined,
    category: categoryFilter.value || undefined,
});

const navigate = () => {
    tableLoading.value = true;
    router.get('/admin/sermons', buildParams(), {
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

watch(categoryFilter, () => {
    navigate();
});

const handleSort = ({ key, direction }) => {
    sortBy.value = key;
    sortDirection.value = direction;
    navigate();
};

const columns = [
    { key: 'title', label: 'Prédication', sortable: true },
    { key: 'preacher_name', label: 'Prédicateur', sortable: true, hidden: 'hidden md:table-cell' },
    { key: 'category_name', label: 'Catégorie', hidden: 'hidden lg:table-cell' },
    { key: 'is_published', label: 'Statut', sortable: true },
];

const rows = computed(() => props.sermons?.data ?? []);
const isLoading = computed(() => tableLoading.value || !props.sermons);

// Format file size
const formatSize = (bytes) => {
    if (!bytes) return '—';
    const mb = bytes / (1024 * 1024);
    return mb >= 1 ? `${mb.toFixed(1)} MB` : `${(bytes / 1024).toFixed(0)} KB`;
};

// --- Toggle publish ---
const toggleLoading = ref(false);
const toggleTarget = ref(null);

const togglePublish = async (row) => {
    toggleLoading.value = true;
    toggleTarget.value = row;
    try {
        const { data } = await axios.patch(`/admin/sermons/${row.id}/toggle-publish`);
        row.is_published = data.is_published;
    } catch (e) {
        console.error('Toggle publish error:', e);
    } finally {
        toggleLoading.value = false;
        toggleTarget.value = null;
    }
};

// --- Delete ---
const showDeleteModal = ref(false);
const deleteTarget = ref(null);
const deleteLoading = ref(false);

const requestDelete = (row) => {
    deleteTarget.value = row;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!deleteTarget.value) return;
    deleteLoading.value = true;
    try {
        await axios.delete(`/admin/sermons/${deleteTarget.value.id}`);
        navigate();
    } catch (e) {
        console.error('Delete error:', e);
    } finally {
        deleteLoading.value = false;
        showDeleteModal.value = false;
        deleteTarget.value = null;
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteTarget.value = null;
};

const statusTabs = [
    { key: '', label: 'Tout' },
    { key: 'published', label: 'Publiées' },
    { key: 'draft', label: 'Brouillons' },
];

// --- Audio Player ---
const playerSrc = ref(null);
const playerTitle = ref('');
const playerPreacher = ref('');
const playerCover = ref(null);
const playerLoading = ref(false);
const loadingSermonUrl = ref(null);

const playSermon = (row) => {
    if (!row.audio_url) return;
    loadingSermonUrl.value = row.audio_url;
    playerSrc.value = row.audio_url;
    playerTitle.value = row.title;
    playerPreacher.value = row.preacher_name;
    playerCover.value = row.cover_url || null;
};

const onPlayerLoading = (val) => {
    playerLoading.value = val;
    if (!val) loadingSermonUrl.value = null;
};

const closePlayer = () => {
    playerSrc.value = null;
    playerTitle.value = '';
    playerPreacher.value = '';
    playerCover.value = null;
};
</script>

<template>
    <AdminLayout title="Prédications">
        <div class="space-y-4">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: 'Prédications' },
            ]" />

            <!-- Skeleton -->
            <template v-if="pageLoading">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/60 dark:border-gray-700 shadow-sm overflow-hidden">
                    <!-- Skeleton header -->
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 animate-pulse"></div>
                            <div>
                                <div class="h-4 w-28 bg-gray-100 rounded animate-pulse mb-1.5"></div>
                                <div class="h-3 w-40 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                        </div>
                        <div class="h-9 w-36 bg-gray-100 rounded-xl animate-pulse"></div>
                    </div>
                    <!-- Skeleton stats -->
                    <div class="grid grid-cols-4 divide-x divide-gray-100 dark:divide-gray-700 border-b border-gray-100 dark:border-gray-700">
                        <div v-for="n in 4" :key="'ss-'+n" class="px-4 py-3 text-center">
                            <div class="h-6 w-10 bg-gray-100 rounded animate-pulse mx-auto mb-1"></div>
                            <div class="h-3 w-16 bg-gray-50 rounded animate-pulse mx-auto"></div>
                        </div>
                    </div>
                    <!-- Skeleton toolbar -->
                    <div class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 flex gap-2">
                        <div class="flex gap-1 p-0.5">
                            <div class="h-8 w-14 bg-gray-100 rounded-lg animate-pulse"></div>
                            <div class="h-8 w-18 bg-gray-50 rounded-lg animate-pulse"></div>
                            <div class="h-8 w-20 bg-gray-50 rounded-lg animate-pulse"></div>
                        </div>
                        <div class="flex-1 h-8 bg-gray-50 rounded-lg animate-pulse"></div>
                    </div>
                    <!-- Skeleton rows -->
                    <div class="divide-y divide-gray-50/80 dark:divide-gray-700/50">
                        <div v-for="n in 6" :key="'sr-'+n" class="flex items-center gap-4 px-5 py-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-100/80 animate-pulse shrink-0"></div>
                            <div class="flex-1 space-y-1">
                                <div class="h-3.5 bg-gray-100/80 rounded animate-pulse" :style="{ width: (100 + n * 20) + 'px' }"></div>
                                <div class="h-2.5 w-20 bg-gray-50 rounded animate-pulse"></div>
                            </div>
                            <div class="hidden md:block h-3 w-16 bg-gray-50 rounded animate-pulse"></div>
                            <div class="hidden sm:block h-5 w-10 bg-gray-100/60 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Actual content -->
            <template v-else>

            <!-- Combined card: Header + Stats + Toolbar + Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/60 dark:border-gray-700 shadow-sm overflow-hidden">
                <!-- Header row -->
                <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-primary/10 text-primary shrink-0">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-gray-900 dark:text-gray-100 leading-tight">Prédications</h1>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ church?.name }}</p>
                        </div>
                    </div>
                    <Link
                        href="/admin/sermons/create"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white rounded-xl text-xs font-semibold shadow-md shadow-primary/25 hover:bg-primary-dark transition-colors shrink-0 self-start sm:self-auto"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nouvelle prédication
                    </Link>
                </div>

                <!-- Inline stats strip -->
                <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-gray-100 dark:divide-gray-700 border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/50">
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100 leading-none">{{ stats?.total ?? 0 }}</p>
                        <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 mt-0.5 uppercase tracking-wider">Total</p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-lg font-bold text-emerald-600 leading-none">{{ stats?.published ?? 0 }}</p>
                        <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 mt-0.5 uppercase tracking-wider">Publiées</p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-lg font-bold text-amber-600 leading-none">{{ stats?.draft ?? 0 }}</p>
                        <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 mt-0.5 uppercase tracking-wider">Brouillons</p>
                    </div>
                    <div class="px-4 py-2.5 text-center">
                        <p class="text-lg font-bold text-blue-600 leading-none">{{ stats?.views ?? 0 }}</p>
                        <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 mt-0.5 uppercase tracking-wider">Écoutes</p>
                    </div>
                </div>

                <!-- Toolbar: Status tabs + Search -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700">
                    <div class="bg-gray-100/80 dark:bg-gray-700/80 rounded-lg p-0.5 inline-flex gap-0.5 shrink-0">
                        <button
                            v-for="tab in statusTabs"
                            :key="tab.key"
                            @click="statusFilter = tab.key"
                            class="relative flex items-center gap-1 px-3 py-1.5 rounded-md text-[11px] font-semibold transition-all duration-200"
                            :class="statusFilter === tab.key
                                ? 'bg-primary text-white shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-white/60 dark:hover:bg-gray-600/50'"
                        >
                            {{ tab.label }}
                            <span
                                v-if="statusFilter === tab.key && sermons"
                                class="ml-0.5 inline-flex items-center justify-center min-w-4 h-4 px-1 rounded-full bg-white/20 text-[9px] font-bold leading-none"
                            >
                                {{ sermons.total }}
                            </span>
                        </button>
                    </div>
                    <div class="flex-1 flex items-center gap-2">
                        <SearchInput
                            v-model="search"
                            placeholder="Rechercher par titre, prédicateur..."
                        />
                        <select
                            v-model="categoryFilter"
                            class="h-9 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-[12px] font-medium text-gray-700 dark:text-gray-300 pl-2.5 pr-7 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors shrink-0"
                        >
                            <option value="">Toutes catégories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Sermons Table -->
            <DataTable
                :loading="isLoading"
                skeleton-on-load
                :columns="columns"
                :rows="rows"
                :pagination="sermons"
                :sort-by="sortBy"
                :sort-direction="sortDirection"
                empty-title="Aucune prédication trouvée"
                :empty-subtitle="search ? 'Essayez un autre terme de recherche' : 'Publiez votre première prédication !'"
                empty-icon="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                @sort="handleSort"
            >
                <!-- Title cell -->
                <template #cell-title="{ row }">
                    <div class="flex items-center gap-2.5">
                        <!-- Cover with play overlay -->
                        <button
                            @click.stop="playSermon(row)"
                            class="relative group shrink-0"
                            :class="{ 'cursor-not-allowed opacity-50': !row.audio_url }"
                            :disabled="!row.audio_url"
                            :title="row.audio_url ? 'Écouter' : 'Aucun audio'"
                        >
                            <div
                                v-if="row.cover_url"
                                class="w-9 h-9 rounded-lg overflow-hidden ring-1 ring-gray-200/80"
                            >
                                <img :src="row.cover_url" :alt="row.title" class="w-full h-full object-cover" />
                            </div>
                            <div
                                v-else
                                class="flex items-center justify-center w-9 h-9 bg-linear-to-br from-primary/12 to-primary/4 rounded-lg"
                            >
                                <svg class="w-4 h-4 text-primary/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                            <!-- Loading / Play overlay -->
                            <div
                                v-if="row.audio_url && loadingSermonUrl === row.audio_url"
                                class="absolute inset-0 bg-black/50 rounded-lg flex items-center justify-center"
                            >
                                <svg class="w-4 h-4 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                            <div
                                v-else-if="row.audio_url"
                                class="absolute inset-0 bg-black/40 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                <svg
                                    v-if="playerSrc === row.audio_url"
                                    class="w-4 h-4 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                                </svg>
                                <svg
                                    v-else
                                    class="w-4 h-4 text-white"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </div>
                        </button>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold text-gray-900 dark:text-gray-100 truncate leading-tight">{{ row.title }}</p>
                            <p class="text-[11px] text-gray-400 truncate mt-0.5">
                                {{ row.views_count ?? 0 }} écoute{{ (row.views_count ?? 0) > 1 ? 's' : '' }}
                                <span v-if="row.size"> · {{ formatSize(row.size) }}</span>
                                <span v-if="row.duration_formatted"> · {{ row.duration_formatted }}</span>
                                <span v-if="row.created_at_human || row.created_at"> · {{ row.created_at_human || row.created_at }}</span>
                            </p>
                        </div>
                    </div>
                </template>

                <!-- Preacher cell -->
                <template #cell-preacher_name="{ row }">
                    <span class="text-[13px] text-gray-700 dark:text-gray-300">{{ row.preacher_name }}</span>
                </template>

                <!-- Category cell -->
                <template #cell-category_name="{ row }">
                    <span
                        v-if="row.category_name"
                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-600/10"
                    >
                        {{ row.category_name }}
                    </span>
                    <span v-else class="text-[13px] text-gray-300 dark:text-gray-600">—</span>
                </template>

                <!-- Status cell -->
                <template #cell-is_published="{ row }">
                    <Toggle
                        :model-value="row.is_published"
                        :loading="toggleLoading && toggleTarget?.id === row.id"
                        size="sm"
                        color="emerald"
                        @change="togglePublish(row)"
                    />
                </template>

                <!-- Actions -->
                <template #actions="{ row }">
                    <div class="flex items-center gap-1">
                        <!-- Play button -->
                        <button
                            v-if="row.audio_url"
                            @click="playSermon(row)"
                            :disabled="loadingSermonUrl === row.audio_url"
                            class="inline-flex items-center p-1.5 text-xs font-semibold rounded-lg transition-colors"
                            :class="loadingSermonUrl === row.audio_url
                                ? 'bg-amber-50 text-amber-500 cursor-wait'
                                : playerSrc === row.audio_url
                                    ? 'bg-primary/15 text-primary ring-1 ring-primary/30'
                                    : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
                            :title="loadingSermonUrl === row.audio_url ? 'Chargement...' : playerSrc === row.audio_url ? 'En lecture...' : 'Écouter'"
                        >
                            <svg v-if="loadingSermonUrl === row.audio_url" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg v-else-if="playerSrc === row.audio_url" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
                            </svg>
                            <svg v-else class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </button>
                        <!-- Edit button: icon only if oubliée -->
                        <Link
                            :href="`/admin/sermons/${row.id}/edit`"
                            class="inline-flex items-center p-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary/15 transition-colors"
                            :title="'Modifier'"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span v-if="row.audio_url" class="ml-1 text-[11px] font-semibold">Modifier</span>
                        </Link>
                        <!-- Delete button only if not oubliée -->
                        <button
                            v-if="row.audio_url"
                            @click="requestDelete(row)"
                            class="inline-flex items-center p-1.5 text-xs font-semibold rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </template>
            </DataTable>
            </template>
        </div>

        <!-- Floating Audio Player -->
        <AudioPlayer
            :src="playerSrc"
            :title="playerTitle"
            :preacher="playerPreacher"
            :cover-url="playerCover"
            @close="closePlayer"
            @loading="onPlayerLoading"
        />

        <!-- Delete confirmation modal -->
        <ConfirmModal
            v-model:show="showDeleteModal"
            title="Supprimer cette prédication ?"
            :message="`Vous êtes sur le point de supprimer la prédication &laquo; ${deleteTarget?.title} &raquo;. Cette action est irréversible. Les fichiers audio et image associés seront également supprimés.`"
            confirm-text="Supprimer"
            cancel-text="Annuler"
            variant="danger"
            :loading="deleteLoading"
            icon="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </AdminLayout>
</template>
