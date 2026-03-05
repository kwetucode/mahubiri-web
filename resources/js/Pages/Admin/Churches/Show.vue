<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import DataTable from '@/Components/DataTable.vue';

const { t } = useI18n();

const props = defineProps({
    church: {
        type: Object,
        required: true,
    },
    sermons: {
        type: Object,
        required: true,
    },
});

const sermonColumns = computed(() => [
    { key: 'title', label: t('churches.sermon') },
    { key: 'preacher_name', label: t('churches.preacher') },
    { key: 'duration_formatted', label: t('common.duration'), hidden: 'hidden md:table-cell' },
    { key: 'created_at', label: t('common.publishedAt'), hidden: 'hidden sm:table-cell' },
]);

const sermonsRows = computed(() => props.sermons?.data ?? []);
</script>

<template>
    <AdminLayout :title="`${t('churches.show.title')} - ${church.name}`">
        <div class="space-y-5">
            <Breadcrumb :items="[
                { label: t('churches.churches'), href: '/admin/churches' },
                { label: church.name },
            ]" />

            <div class="bg-white border border-gray-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 flex flex-col md:flex-row gap-5 md:items-start">
                    <div class="w-full md:w-72 shrink-0">
                        <div class="aspect-video w-full rounded-xl overflow-hidden bg-gray-100 ring-1 ring-gray-200/80">
                            <img
                                v-if="church.logo_url"
                                :src="church.logo_url"
                                :alt="church.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-sm font-medium">
                                {{ t('churches.show.noLogo') }}
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0 flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold text-gray-900">{{ church.name }}</h1>
                            <span
                                v-if="church.abbreviation"
                                class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-xs font-semibold"
                            >
                                {{ church.abbreviation }}
                            </span>
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold"
                                :class="church.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
                            >
                                {{ church.is_active ? t('common.active') : t('common.inactive') }}
                            </span>
                        </div>

                        <p v-if="church.description" class="text-sm text-gray-600 leading-relaxed">
                            {{ church.description }}
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div class="rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                                <p class="text-xs text-gray-400 mb-1">{{ t('churches.show.visionary') }}</p>
                                <p class="font-medium text-gray-800">{{ church.visionary_name || '—' }}</p>
                            </div>
                            <div class="rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                                <p class="text-xs text-gray-400 mb-1">{{ t('common.location') }}</p>
                                <p class="font-medium text-gray-800">{{ church.city || church.country_name || '—' }}</p>
                            </div>
                            <div class="rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                                <p class="text-xs text-gray-400 mb-1">{{ t('churches.show.address') }}</p>
                                <p class="font-medium text-gray-800">{{ church.address || '—' }}</p>
                            </div>
                            <div class="rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                                <p class="text-xs text-gray-400 mb-1">{{ t('churches.show.publishedSermons') }}</p>
                                <p class="font-medium text-gray-800">{{ church.published_sermons_count }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-gray-900">{{ t('churches.show.publishedSermons') }}</h2>
                <Link
                    href="/admin/churches"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                >
                    {{ t('common.backToList') }}
                </Link>
            </div>

            <DataTable
                :columns="sermonColumns"
                :rows="sermonsRows"
                :pagination="sermons"
                :empty-title="t('churches.show.noSermons')"
                :empty-subtitle="t('churches.show.noSermonsSubtitle')"
                empty-icon="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
            >
                <template #cell-title="{ row }">
                    <p class="text-sm font-semibold text-gray-900">{{ row.title || '—' }}</p>
                </template>

                <template #cell-preacher_name="{ row }">
                    <p class="text-sm text-gray-700">{{ row.preacher_name || '—' }}</p>
                </template>

                <template #cell-duration_formatted="{ row }">
                    <p class="text-sm text-gray-600">{{ row.duration_formatted || '—' }}</p>
                </template>

                <template #cell-created_at="{ row }">
                    <p class="text-sm text-gray-600">{{ row.created_at_human || row.created_at || '—' }}</p>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
