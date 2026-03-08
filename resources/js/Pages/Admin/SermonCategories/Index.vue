<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    categories: Object,
    filters: Object,
});

const { t } = useI18n();

const search = ref(props.filters?.search || '');
const sortBy = ref(props.filters?.sort_by || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');
const tableLoading = ref(false);
const formLoading = ref(false);
const saveError = ref('');

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

const createForm = ref({ name: '' });
const editForm = ref({ id: null, name: '' });
const deleteTarget = ref(null);

const columns = computed(() => [
    { key: 'name', label: t('categories.name'), sortable: true },
    { key: 'sermons_count', label: t('categories.sermonsCount'), sortable: true, hidden: 'hidden sm:table-cell' },
    { key: 'created_at', label: t('categories.createdAt'), sortable: true, hidden: 'hidden lg:table-cell' },
]);

const buildParams = () => ({
    search: search.value || undefined,
    sort_by: sortBy.value || undefined,
    sort_direction: sortDirection.value || undefined,
});

const navigate = () => {
    tableLoading.value = true;
    router.get('/admin/sermon-categories', buildParams(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['categories', 'filters'],
        onFinish: () => {
            tableLoading.value = false;
        },
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

const openCreateModal = () => {
    saveError.value = '';
    createForm.value = { name: '' };
    showCreateModal.value = true;
};

const openEditModal = (row) => {
    saveError.value = '';
    editForm.value = { id: row.id, name: row.name };
    showEditModal.value = true;
};

const openDeleteModal = (row) => {
    deleteTarget.value = row;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteTarget.value = null;
};

const saveCreate = async () => {
    formLoading.value = true;
    saveError.value = '';

    try {
        await axios.post('/admin/sermon-categories', { name: createForm.value.name });
        showCreateModal.value = false;
        navigate();
    } catch (error) {
        saveError.value = error.response?.data?.message || t('common.errorCreate');
    } finally {
        formLoading.value = false;
    }
};

const saveEdit = async () => {
    formLoading.value = true;
    saveError.value = '';

    try {
        await axios.patch(`/admin/sermon-categories/${editForm.value.id}`, { name: editForm.value.name });
        showEditModal.value = false;
        navigate();
    } catch (error) {
        saveError.value = error.response?.data?.message || t('common.errorUpdate');
    } finally {
        formLoading.value = false;
    }
};

const deleteCategory = async () => {
    if (!deleteTarget.value) return;

    formLoading.value = true;
    saveError.value = '';

    try {
        await axios.delete(`/admin/sermon-categories/${deleteTarget.value.id}`);
        closeDeleteModal();
        navigate();
    } catch (error) {
        saveError.value = error.response?.data?.message || t('common.errorDelete');
    } finally {
        formLoading.value = false;
    }
};

const totalLabel = computed(() => {
    const total = props.categories?.total ?? 0;
    return `${total} ${t('categories.title')}`;
});
</script>

<template>
    <AdminLayout :title="t('categories.title')">
        <div class="space-y-6">
            <Breadcrumb :items="[{ label: t('categories.title') }]" />

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900">{{ t('categories.title') }}</h1>
                    <p class="text-gray-500 mt-1 text-sm">{{ t('categories.subtitle') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-4 py-2 bg-primary/10 text-primary rounded-xl text-sm font-bold ring-1 ring-primary/20">
                        {{ totalLabel }}
                    </span>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-semibold hover:bg-primary/90 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ t('categories.addCategory') }}
                    </button>
                </div>
            </div>

            <SearchInput
                v-model="search"
                :placeholder="t('categories.searchPlaceholder')"
            />

            <DataTable
                :loading="tableLoading"
                :columns="columns"
                :rows="categories.data"
                :pagination="categories"
                :sort-by="sortBy"
                :sort-direction="sortDirection"
                :empty-title="t('categories.noCategory')"
                :empty-subtitle="search ? t('common.tryOtherSearch') : t('categories.noCategorySubtitle')"
                empty-icon="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                @sort="handleSort"
            >
                <template #cell-name="{ row }">
                    <span class="text-sm font-semibold text-gray-900">{{ row.name }}</span>
                </template>

                <template #cell-sermons_count="{ row }">
                    <span class="inline-flex items-center gap-1 text-sm font-medium" :class="row.sermons_count > 0 ? 'text-blue-600' : 'text-gray-400'">
                        {{ row.sermons_count }}
                    </span>
                </template>

                <template #cell-created_at="{ row }">
                    <div>
                        <p class="text-sm text-gray-700">{{ row.created_at }}</p>
                        <p class="text-xs text-gray-400">{{ row.created_at_human }}</p>
                    </div>
                </template>

                <template #actions="{ row }">
                    <div class="inline-flex items-center gap-2">
                        <button
                            @click="openEditModal(row)"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors"
                        >
                            {{ t('common.edit') }}
                        </button>
                        <button
                            @click="openDeleteModal(row)"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition-colors"
                        >
                            {{ t('common.delete') }}
                        </button>
                    </div>
                </template>
            </DataTable>

            <!-- Create Modal -->
            <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white rounded-2xl border border-gray-200 shadow-xl p-6 space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ t('categories.addCategory') }}</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('categories.name') }}</label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary"
                            :placeholder="t('categories.categoryNamePlaceholder')"
                        />
                    </div>
                    <p v-if="saveError" class="text-sm text-red-600">{{ saveError }}</p>
                    <div class="flex justify-end gap-2">
                        <button @click="showCreateModal = false" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">{{ t('common.cancel') }}</button>
                        <button @click="saveCreate" :disabled="formLoading" class="px-4 py-2 rounded-xl text-sm font-semibold bg-primary text-white hover:bg-primary/90 disabled:opacity-60">
                            {{ formLoading ? t('common.saving') : t('common.save') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div v-if="showEditModal" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white rounded-2xl border border-gray-200 shadow-xl p-6 space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ t('categories.editCategory') }}</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('categories.name') }}</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary"
                            :placeholder="t('categories.categoryNamePlaceholder')"
                        />
                    </div>
                    <p v-if="saveError" class="text-sm text-red-600">{{ saveError }}</p>
                    <div class="flex justify-end gap-2">
                        <button @click="showEditModal = false" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">{{ t('common.cancel') }}</button>
                        <button @click="saveEdit" :disabled="formLoading" class="px-4 py-2 rounded-xl text-sm font-semibold bg-primary text-white hover:bg-primary/90 disabled:opacity-60">
                            {{ formLoading ? t('common.saving') : t('common.save') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Delete Confirm -->
            <ConfirmModal
                v-model:show="showDeleteModal"
                :title="t('categories.deleteConfirm')"
                :message="t('categories.deleteMessage', { name: deleteTarget?.name || '' })"
                :confirm-text="t('common.delete')"
                :cancel-text="t('common.cancel')"
                variant="danger"
                :loading="formLoading"
                @confirm="deleteCategory"
                @cancel="closeDeleteModal"
            />

            <p v-if="saveError && !showCreateModal && !showEditModal" class="text-sm text-red-600">
                {{ saveError }}
            </p>
        </div>
    </AdminLayout>
</template>
