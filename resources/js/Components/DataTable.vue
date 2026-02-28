<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    /** Colonnes du tableau: [{ key, label, hidden?, class?, sortable? }] */
    columns: {
        type: Array,
        required: true,
    },
    /** Tableau de données (items affichés) */
    rows: {
        type: Array,
        default: () => [],
    },
    /** Clé unique pour chaque ligne */
    rowKey: {
        type: String,
        default: 'id',
    },
    /** Objet de pagination Laravel (avec data, links, from, to, total, last_page) */
    pagination: {
        type: Object,
        default: null,
    },
    /** Texte affiché quand le tableau est vide */
    emptyTitle: {
        type: String,
        default: 'Aucun résultat trouvé',
    },
    emptySubtitle: {
        type: String,
        default: '',
    },
    /** Icône SVG path pour l'état vide */
    emptyIcon: {
        type: String,
        default: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
    },
    /** Afficher le loading */
    loading: {
        type: Boolean,
        default: false,
    },
    /** Afficher le skeleton sur les lignes même si des données existent */
    skeletonOnLoad: {
        type: Boolean,
        default: false,
    },
    /** Rendre les lignes cliquables */
    clickable: {
        type: Boolean,
        default: false,
    },
    /** Bordure arrondie */
    rounded: {
        type: Boolean,
        default: true,
    },
    /** Afficher les rayures */
    striped: {
        type: Boolean,
        default: false,
    },
    /** Colonne actuellement triée */
    sortBy: {
        type: String,
        default: '',
    },
    /** Direction du tri: 'asc' ou 'desc' */
    sortDirection: {
        type: String,
        default: 'asc',
        validator: (v) => ['asc', 'desc'].includes(v),
    },
});

const emit = defineEmits(['row-click', 'sort']);

const totalColumns = props.columns.length;

const handleSort = (col) => {
    if (!col.sortable) return;
    const direction = props.sortBy === col.key && props.sortDirection === 'asc' ? 'desc' : 'asc';
    emit('sort', { key: col.key, direction });
};

const isSorted = (col) => props.sortBy === col.key;
</script>

<template>
    <div
        class="bg-white border border-gray-200/80 overflow-hidden shadow-sm"
        :class="rounded ? 'rounded-2xl' : ''"
    >
        <!-- Loading bar -->
        <div v-if="loading" class="h-0.5 bg-gray-100 overflow-hidden">
            <div class="h-full bg-primary rounded-full animate-loading-bar"></div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/80">
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="text-left py-4 px-6 text-xs font-bold uppercase tracking-wider"
                            :class="[
                                col.hidden || '',
                                col.sortable ? 'cursor-pointer select-none hover:text-gray-700 transition-colors group/th' : '',
                                isSorted(col) ? 'text-primary' : 'text-gray-500',
                            ]"
                            @click="handleSort(col)"
                        >
                            <div class="flex items-center gap-1.5">
                                <span>{{ col.label }}</span>
                                <!-- Sort indicators -->
                                <span v-if="col.sortable" class="inline-flex flex-col -space-y-1.5">
                                    <svg
                                        class="w-3 h-3 transition-colors"
                                        :class="isSorted(col) && sortDirection === 'asc' ? 'text-primary' : 'text-gray-300 group-hover/th:text-gray-400'"
                                        viewBox="0 0 12 12" fill="currentColor"
                                    >
                                        <path d="M6 3L10 7H2L6 3Z" />
                                    </svg>
                                    <svg
                                        class="w-3 h-3 transition-colors"
                                        :class="isSorted(col) && sortDirection === 'desc' ? 'text-primary' : 'text-gray-300 group-hover/th:text-gray-400'"
                                        viewBox="0 0 12 12" fill="currentColor"
                                    >
                                        <path d="M6 9L2 5H10L6 9Z" />
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <!-- Actions column -->
                        <th v-if="$slots.actions" class="text-right py-4 px-6 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <!-- Loading skeleton (full replacement) -->
                    <template v-if="loading && (rows.length === 0 || skeletonOnLoad)">
                        <tr v-for="n in 5" :key="'skel-' + n" class="animate-pulse">
                            <td v-for="col in columns" :key="col.key" class="py-4 px-6" :class="col.hidden || ''">
                                <div class="flex items-center gap-3">
                                    <div v-if="n <= 1" class="w-9 h-9 rounded-full bg-gray-100 shrink-0"></div>
                                    <div class="space-y-1.5 flex-1">
                                        <div class="h-3.5 bg-gray-100 rounded-md" :style="{ width: (40 + ((n * 13 + columns.indexOf(col) * 17) % 45)) + '%' }"></div>
                                        <div v-if="columns.indexOf(col) === 0" class="h-2.5 bg-gray-50 rounded-md" :style="{ width: (30 + ((n * 7) % 30)) + '%' }"></div>
                                    </div>
                                </div>
                            </td>
                            <td v-if="$slots.actions" class="py-4 px-6">
                                <div class="h-4 w-16 bg-gray-100 rounded-lg ml-auto"></div>
                            </td>
                        </tr>
                    </template>

                    <!-- Real rows -->
                    <template v-else-if="rows.length > 0">
                        <tr
                            v-for="(row, index) in rows"
                            :key="row[rowKey] ?? index"
                            class="transition-colors duration-150 group"
                            :class="[
                                clickable ? 'cursor-pointer' : '',
                                striped && index % 2 === 1 ? 'bg-gray-50/40' : '',
                                'hover:bg-primary/[0.02]',
                            ]"
                            @click="clickable ? $emit('row-click', row) : null"
                        >
                            <td
                                v-for="col in columns"
                                :key="col.key"
                                class="py-4 px-6"
                                :class="col.hidden || ''"
                            >
                                <!-- Named slot per column, fallback to raw value -->
                                <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]" :index="index">
                                    <span class="text-sm text-gray-700">{{ row[col.key] ?? '—' }}</span>
                                </slot>
                            </td>
                            <!-- Actions slot -->
                            <td v-if="$slots.actions" class="py-4 px-6 text-right">
                                <slot name="actions" :row="row" :index="index" />
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <tr v-else-if="!loading">
                        <td :colspan="totalColumns + ($slots.actions ? 1 : 0)" class="py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="emptyIcon" />
                                    </svg>
                                </div>
                                <p class="text-gray-600 font-semibold mb-1">{{ emptyTitle }}</p>
                                <p v-if="emptySubtitle" class="text-gray-400 text-sm">{{ emptySubtitle }}</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="pagination && pagination.last_page > 1"
            class="border-t border-gray-100 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50/50"
        >
            <p class="text-sm text-gray-500 font-medium">
                Affichage de <span class="text-gray-700">{{ pagination.from }}</span>
                à <span class="text-gray-700">{{ pagination.to }}</span>
                sur <span class="text-gray-700">{{ pagination.total }}</span> résultats
            </p>
            <div class="flex items-center gap-1">
                <template v-for="link in pagination.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-3.5 py-2 rounded-xl text-sm font-semibold transition-all duration-200"
                        :class="link.active
                            ? 'bg-primary text-white shadow-md shadow-primary/30'
                            : 'text-gray-600 hover:bg-gray-100'"
                        v-html="link.label"
                        preserve-state
                        preserve-scroll
                    />
                    <span
                        v-else
                        class="px-3 py-2 text-sm text-gray-300"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes loading-bar {
    0% { width: 0; margin-left: 0; }
    50% { width: 60%; margin-left: 20%; }
    100% { width: 0; margin-left: 100%; }
}
.animate-loading-bar {
    animation: loading-bar 1.5s ease-in-out infinite;
}
</style>
