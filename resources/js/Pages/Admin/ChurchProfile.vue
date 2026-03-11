<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Card from '@/Components/Card.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    church: Object,
    stats: Object,
    topSermons: { type: Array, default: () => [] },
});

const { t } = useI18n();

const editing = ref(false);

const icons = {
    mic: 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z',
    published: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    draft: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
    eye: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
    heart: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    trophy: 'M5 3l14 0M5 3v4a7.012 7.012 0 004 6.32V17H9a2 2 0 00-2 2v2h10v-2a2 2 0 00-2-2h-1v-3.68A7.012 7.012 0 0019 7V3',
};

const statCards = computed(() => [
    { label: t('churchProfile.totalSermons'), value: props.stats?.totalSermons ?? 0, icon: icons.mic, color: 'primary' },
    { label: t('churchProfile.publishedSermons'), value: props.stats?.publishedSermons ?? 0, icon: icons.published, color: 'emerald' },
    { label: t('churchProfile.draftSermons'), value: props.stats?.draftSermons ?? 0, icon: icons.draft, color: 'amber' },
    { label: t('churchProfile.totalListens'), value: props.stats?.totalViews ?? 0, icon: icons.eye, color: 'blue' },
    { label: t('churchProfile.totalFavorites'), value: props.stats?.totalFavorites ?? 0, icon: icons.heart, color: 'red' },
    { label: t('churchProfile.thisMonth'), value: props.stats?.sermonsThisMonth ?? 0, icon: icons.calendar, color: 'primary' },
]);

const form = useForm({
    name: props.church.name ?? '',
    abbreviation: props.church.abbreviation ?? '',
    visionary_name: props.church.visionary_name ?? '',
    description: props.church.description ?? '',
    country_name: props.church.country_name ?? '',
    city: props.church.city ?? '',
    address: props.church.address ?? '',
    logo: null,
    remove_logo: false,
});

const logoPreview = ref(null);
const currentLogo = ref(props.church.logo_url);

const handleLogoSelect = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.logo = file;
    form.remove_logo = false;
    logoPreview.value = URL.createObjectURL(file);
};

const removeLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    logoPreview.value = null;
    currentLogo.value = null;
};

const displayLogo = computed(() => logoPreview.value || currentLogo.value);

const startEditing = () => {
    editing.value = true;
};

const cancelEditing = () => {
    editing.value = false;
    // Reset form to original values
    form.name = props.church.name ?? '';
    form.abbreviation = props.church.abbreviation ?? '';
    form.visionary_name = props.church.visionary_name ?? '';
    form.description = props.church.description ?? '';
    form.country_name = props.church.country_name ?? '';
    form.city = props.church.city ?? '';
    form.address = props.church.address ?? '';
    form.logo = null;
    form.remove_logo = false;
    logoPreview.value = null;
    currentLogo.value = props.church.logo_url;
};

const submit = () => {
    form.post('/admin/church-profile', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            editing.value = false;
        },
    });
};

// Info items for view mode
const infoItems = computed(() => [
    { label: t('churchProfile.name'), value: props.church.name, icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { label: t('churchProfile.abbreviation'), value: props.church.abbreviation, icon: 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14' },
    { label: t('churchProfile.visionary'), value: props.church.visionary_name, icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { label: t('churchProfile.country'), value: props.church.country_name, icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: t('churchProfile.city'), value: props.church.city, icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' },
    { label: t('churchProfile.address'), value: props.church.address, icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
]);
</script>

<template>
    <AdminLayout :title="t('churchProfile.title')">
        <div class="space-y-5">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: t('churchProfile.title') },
            ]" />

            <!-- Stats Grid (3 cols on md, 6 on xl) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
                <StatCard
                    v-for="stat in statCards"
                    :key="stat.label"
                    :value="stat.value"
                    :label="stat.label"
                    :icon="stat.icon"
                    :color="stat.color"
                />
            </div>

            <!-- Main layout: 2 columns -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Left: Church profile card (spans 2) -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/60 dark:border-gray-700 shadow-sm overflow-hidden">
                        <!-- Header with logo -->
                        <div class="relative">
                            <!-- Gradient strip -->
                            <div class="h-24 bg-linear-to-r from-primary via-primary/80 to-primary/60 relative overflow-hidden">
                                <!-- Decorative pattern -->
                                <div class="absolute inset-0 opacity-10">
                                    <svg class="w-full h-full" viewBox="0 0 400 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="50" cy="60" r="40" stroke="white" stroke-width="0.5" opacity="0.5"/>
                                        <circle cx="350" cy="30" r="60" stroke="white" stroke-width="0.5" opacity="0.3"/>
                                        <circle cx="200" cy="90" r="30" stroke="white" stroke-width="0.5" opacity="0.4"/>
                                    </svg>
                                </div>

                                <!-- Edit / Cancel button (in the gradient) -->
                                <div class="absolute top-3 right-4 z-10">
                                    <button
                                        v-if="!editing"
                                        @click="startEditing"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white/90 dark:bg-gray-800/90 text-primary rounded-xl text-xs font-semibold hover:bg-white dark:hover:bg-gray-800 transition-colors shadow-sm"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        {{ t('churchProfile.editProfile') }}
                                    </button>
                                    <button
                                        v-else
                                        @click="cancelEditing"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white/90 dark:bg-gray-800/90 text-primary rounded-xl text-xs font-semibold hover:bg-white dark:hover:bg-gray-800 transition-colors shadow-sm"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        {{ t('churchProfile.cancel') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Logo positioned to overlap -->
                            <div class="px-5 -mt-10 relative z-10">
                                <div class="relative inline-block shrink-0">
                                    <div
                                        class="w-20 h-20 rounded-2xl border-4 border-white dark:border-gray-800 shadow-lg overflow-hidden bg-white dark:bg-gray-700"
                                    >
                                        <img
                                            v-if="displayLogo"
                                            :src="displayLogo"
                                            :alt="church.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="w-full h-full flex items-center justify-center bg-primary/10 text-primary"
                                        >
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Edit logo overlay (edit mode only) -->
                                    <template v-if="editing">
                                        <label
                                            class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-2xl cursor-pointer opacity-0 hover:opacity-100 transition-opacity"
                                        >
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <input
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                class="hidden"
                                                @change="handleLogoSelect"
                                            />
                                        </label>
                                        <button
                                            v-if="displayLogo"
                                            @click="removeLogo"
                                            class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-sm"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Church name & meta (fully in white zone) -->
                            <div class="px-5 pt-3 pb-4">
                                <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100 truncate">{{ church.name }}</h1>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span
                                        v-if="church.abbreviation"
                                        class="text-xs font-semibold text-gray-500 dark:text-gray-400"
                                    >{{ church.abbreviation }}</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                        :class="church.is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10' : 'bg-red-50 text-red-600 ring-1 ring-red-600/10'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="church.is_active ? 'bg-emerald-500' : 'bg-red-400'"></span>
                                        {{ church.is_active ? t('churchProfile.active') : t('churchProfile.inactive') }}
                                    </span>
                                    <span v-if="church.is_featured" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/10">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        {{ t('churchProfile.featured') }}
                                    </span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">· {{ t('churchProfile.sermonCount', { count: church.sermons_count }) }}</span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">· {{ t('churchProfile.since', { date: church.created_at }) }}</span>
                                </div>
                            </div>

                            <!-- Logo error -->
                            <p v-if="form.errors.logo" class="px-5 pb-2 text-xs text-red-500">{{ form.errors.logo }}</p>
                        </div>

                        <!-- VIEW MODE -->
                        <template v-if="!editing">
                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                <div
                                    v-for="item in infoItems"
                                    :key="item.label"
                                    class="flex items-start gap-3 px-5 py-3 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                                >
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ item.label }}</p>
                                        <p class="text-[13px] text-gray-900 dark:text-gray-100 mt-0.5">{{ item.value || t('churchProfile.noInfoAvailable') }}</p>
                                    </div>
                                </div>

                                <!-- Description (full width) -->
                                <div class="px-5 py-3 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                    <div class="flex items-start gap-3">
                                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ t('churchProfile.description') }}</p>
                                            <p class="text-[13px] text-gray-900 dark:text-gray-100 mt-0.5 whitespace-pre-line">{{ church.description || t('churchProfile.noDescription') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- EDIT MODE -->
                        <template v-else>
                            <form @submit.prevent="submit" class="divide-y divide-gray-100 dark:divide-gray-700">
                                <!-- Row: Nom + Abréviation -->
                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 px-5 py-4">
                                    <div class="sm:col-span-3">
                                        <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                            {{ t('churchProfile.name') }} <span class="text-red-400">*</span>
                                        </label>
                                        <input
                                            v-model="form.name"
                                            type="text"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                            :placeholder="t('churchProfile.namePlaceholder')"
                                        />
                                        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                            {{ t('churchProfile.abbreviation') }}
                                        </label>
                                        <input
                                            v-model="form.abbreviation"
                                            type="text"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                            placeholder="Ex: CBCA"
                                        />
                                        <p v-if="form.errors.abbreviation" class="text-xs text-red-500 mt-1">{{ form.errors.abbreviation }}</p>
                                    </div>
                                </div>

                                <!-- Row: Visionnaire -->
                                <div class="px-5 py-4">
                                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                        {{ t('churchProfile.visionary') }}
                                    </label>
                                    <input
                                        v-model="form.visionary_name"
                                        type="text"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                        :placeholder="t('churchProfile.visionaryPlaceholder')"
                                    />
                                    <p v-if="form.errors.visionary_name" class="text-xs text-red-500 mt-1">{{ form.errors.visionary_name }}</p>
                                </div>

                                <!-- Row: Pays + Ville -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-5 py-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                            {{ t('churchProfile.country') }}
                                        </label>
                                        <input
                                            v-model="form.country_name"
                                            type="text"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                            placeholder="Ex: RD Congo"
                                        />
                                        <p v-if="form.errors.country_name" class="text-xs text-red-500 mt-1">{{ form.errors.country_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                            {{ t('churchProfile.city') }}
                                        </label>
                                        <input
                                            v-model="form.city"
                                            type="text"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                            placeholder="Ex: Goma"
                                        />
                                        <p v-if="form.errors.city" class="text-xs text-red-500 mt-1">{{ form.errors.city }}</p>
                                    </div>
                                </div>

                                <!-- Row: Adresse -->
                                <div class="px-5 py-4">
                                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                        {{ t('churchProfile.address') }}
                                    </label>
                                    <input
                                        v-model="form.address"
                                        type="text"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                        :placeholder="t('churchProfile.addressPlaceholder')"
                                    />
                                    <p v-if="form.errors.address" class="text-xs text-red-500 mt-1">{{ form.errors.address }}</p>
                                </div>

                                <!-- Row: Description -->
                                <div class="px-5 py-4">
                                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                        {{ t('churchProfile.description') }}
                                    </label>
                                    <textarea
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors resize-none"
                                        :placeholder="t('churchProfile.descriptionPlaceholder')"
                                    ></textarea>
                                    <p v-if="form.errors.description" class="text-xs text-red-500 mt-1">{{ form.errors.description }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-gray-50/50 dark:bg-gray-800/50">
                                    <button
                                        type="button"
                                        @click="cancelEditing"
                                        class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        {{ t('churchProfile.cancel') }}
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white rounded-xl text-xs font-semibold shadow-md shadow-primary/25 hover:bg-primary-dark transition-colors disabled:opacity-50"
                                    >
                                        <svg v-if="form.processing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ t('churchProfile.save') }}
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>

                <!-- Right column: sidebar cards -->
                <div class="space-y-5">
                    <!-- Résumé rapide -->
                    <Card dark no-padding>
                        <template #header>
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                                <h3 class="text-xs font-semibold text-white/90">{{ t('churchProfile.summary') }}</h3>
                            </div>
                        </template>
                        <div class="px-5 py-3.5 space-y-3">
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('churchProfile.totalSermons') }}</span>
                                <span class="text-blue-400 font-medium">{{ stats?.totalSermons ?? 0 }} total</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('churchProfile.listensThisMonth') }}</span>
                                <span class="text-emerald-400 font-medium">{{ stats?.viewsThisMonth ?? 0 }} {{ t('churchProfile.listens') }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('churchProfile.publicationRate') }}</span>
                                <span class="text-accent-warm font-medium">{{ stats?.publicationRate ?? 0 }}%</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('churchProfile.lastAdded') }}</span>
                                <span class="text-purple-400 font-medium">{{ stats?.lastSermonDate ?? t('churchProfile.none') }}</span>
                            </div>
                        </div>
                    </Card>

                    <!-- Top 3 prédications -->
                    <Card :title="t('churchProfile.topSermons')" :subtitle="t('churchProfile.mostListened')" no-padding>
                        <div v-if="topSermons.length === 0" class="px-5 py-6 text-center">
                            <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="icons.trophy" />
                                </svg>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('churchProfile.noSermons') }}</p>
                        </div>
                        <div v-else class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <div
                                v-for="(sermon, i) in topSermons"
                                :key="sermon.id"
                                class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                            >
                                <span
                                    class="flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold shrink-0"
                                    :class="i === 0 ? 'bg-amber-100 text-amber-700' : i === 1 ? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' : 'bg-orange-50 text-orange-600'"
                                >
                                    {{ i + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ sermon.title }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ sermon.preacher_name }}</p>
                                </div>
                                <div class="flex items-center gap-1 text-[11px] font-semibold text-gray-500 dark:text-gray-400 shrink-0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ sermon.views_count }}
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Quick actions -->
                    <Card :title="t('churchProfile.quickActions')">
                        <div class="space-y-2">
                            <Link
                                href="/admin/sermons/create"
                                class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-primary/5 dark:hover:bg-primary/10 border border-gray-100 dark:border-gray-600 hover:border-primary/20 transition-all duration-200 group"
                            >
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ t('churchProfile.newSermon') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('churchProfile.publishNewSermon') }}</p>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ t('churchProfile.manageSermons') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('churchProfile.manageSermonsDesc') }}</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-500 group-hover:text-primary/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                            <Link
                                href="/admin/dashboard"
                                class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-primary/5 dark:hover:bg-primary/10 border border-gray-100 dark:border-gray-600 hover:border-primary/20 transition-all duration-200 group"
                            >
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ t('churchProfile.viewDashboard') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('churchProfile.viewStats') }}</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-500 group-hover:text-primary/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
