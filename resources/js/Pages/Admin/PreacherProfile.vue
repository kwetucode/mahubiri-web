<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Card from '@/Components/Card.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    preacher: Object,
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
    { label: t('preacherProfile.totalSermons'), value: props.stats?.totalSermons ?? 0, icon: icons.mic, color: 'primary' },
    { label: t('preacherProfile.publishedSermons'), value: props.stats?.publishedSermons ?? 0, icon: icons.published, color: 'emerald' },
    { label: t('preacherProfile.draftSermons'), value: props.stats?.draftSermons ?? 0, icon: icons.draft, color: 'amber' },
    { label: t('preacherProfile.totalListens'), value: props.stats?.totalViews ?? 0, icon: icons.eye, color: 'blue' },
    { label: t('preacherProfile.totalFavorites'), value: props.stats?.totalFavorites ?? 0, icon: icons.heart, color: 'red' },
    { label: t('preacherProfile.thisMonth'), value: props.stats?.sermonsThisMonth ?? 0, icon: icons.calendar, color: 'primary' },
]);

const form = useForm({
    ministry_name: props.preacher.ministry_name ?? '',
    ministry_type: props.preacher.ministry_type ?? '',
    country_name: props.preacher.country_name ?? '',
    city: props.preacher.city ?? '',
    avatar: null,
    remove_avatar: false,
});

const avatarPreview = ref(null);
const currentAvatar = ref(props.preacher.avatar_url);

const handleAvatarSelect = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.avatar = file;
    form.remove_avatar = false;
    avatarPreview.value = URL.createObjectURL(file);
};

const removeAvatar = () => {
    form.avatar = null;
    form.remove_avatar = true;
    avatarPreview.value = null;
    currentAvatar.value = null;
};

const displayAvatar = computed(() => avatarPreview.value || currentAvatar.value);

const startEditing = () => {
    editing.value = true;
};

const cancelEditing = () => {
    editing.value = false;
    form.ministry_name = props.preacher.ministry_name ?? '';
    form.ministry_type = props.preacher.ministry_type ?? '';
    form.country_name = props.preacher.country_name ?? '';
    form.city = props.preacher.city ?? '';
    form.avatar = null;
    form.remove_avatar = false;
    avatarPreview.value = null;
    currentAvatar.value = props.preacher.avatar_url;
};

const submit = () => {
    form.post('/admin/preacher-profile', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            editing.value = false;
        },
    });
};

// Info items for view mode
const infoItems = computed(() => [
    { label: t('preacherProfile.ministryName'), value: props.preacher.ministry_name, icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { label: t('preacherProfile.ministryType'), value: props.preacher.ministry_type_description, icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { label: t('preacherProfile.userName'), value: props.preacher.user_name, icon: 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: t('preacherProfile.country'), value: props.preacher.country_name, icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: t('preacherProfile.city'), value: props.preacher.city, icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' },
]);

const ministryTypes = [
    { value: 'pasteur', label: 'Pasteur' },
    { value: 'apotre', label: 'Apôtre' },
    { value: 'evangeliste', label: 'Évangéliste' },
    { value: 'prophete', label: 'Prophète' },
    { value: 'enseignant', label: 'Enseignant' },
    { value: 'docteur', label: 'Docteur' },
];
</script>

<template>
    <AdminLayout :title="t('preacherProfile.title')">
        <div class="space-y-5">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: t('preacherProfile.title') },
            ]" />

            <!-- Stats Grid -->
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
                <!-- Left: Preacher profile card (spans 2) -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/60 dark:border-gray-700 shadow-sm overflow-hidden">
                        <!-- Header with avatar -->
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

                                <!-- Edit / Cancel button -->
                                <div class="absolute top-3 right-4 z-10">
                                    <button
                                        v-if="!editing"
                                        @click="startEditing"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white/90 dark:bg-gray-800/90 text-primary rounded-xl text-xs font-semibold hover:bg-white dark:hover:bg-gray-800 transition-colors shadow-sm"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        {{ t('preacherProfile.editProfile') }}
                                    </button>
                                    <button
                                        v-else
                                        @click="cancelEditing"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white/90 dark:bg-gray-800/90 text-primary rounded-xl text-xs font-semibold hover:bg-white dark:hover:bg-gray-800 transition-colors shadow-sm"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        {{ t('preacherProfile.cancel') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Avatar positioned to overlap -->
                            <div class="px-5 -mt-10 relative z-10">
                                <div class="relative inline-block shrink-0">
                                    <div
                                        class="w-20 h-20 rounded-2xl border-4 border-white dark:border-gray-800 shadow-lg overflow-hidden bg-white dark:bg-gray-700"
                                    >
                                        <img
                                            v-if="displayAvatar"
                                            :src="displayAvatar"
                                            :alt="preacher.ministry_name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="w-full h-full flex items-center justify-center bg-primary/10 text-primary"
                                        >
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Edit avatar overlay (edit mode only) -->
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
                                                @change="handleAvatarSelect"
                                            />
                                        </label>
                                        <button
                                            v-if="displayAvatar"
                                            @click="removeAvatar"
                                            class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-sm"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Preacher name & meta -->
                            <div class="px-5 pt-3 pb-4">
                                <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100 truncate">{{ preacher.ministry_name }}</h1>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span
                                        v-if="preacher.ministry_type_description"
                                        class="text-xs font-semibold text-gray-500 dark:text-gray-400"
                                    >{{ preacher.ministry_type_description }}</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                        :class="preacher.is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10' : 'bg-red-50 text-red-600 ring-1 ring-red-600/10'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="preacher.is_active ? 'bg-emerald-500' : 'bg-red-400'"></span>
                                        {{ preacher.is_active ? t('preacherProfile.active') : t('preacherProfile.inactive') }}
                                    </span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">· {{ t('preacherProfile.sermonCount', { count: preacher.sermons_count }) }}</span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">· {{ t('preacherProfile.since', { date: preacher.created_at }) }}</span>
                                </div>
                            </div>

                            <!-- Avatar error -->
                            <p v-if="form.errors.avatar" class="px-5 pb-2 text-xs text-red-500">{{ form.errors.avatar }}</p>
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
                                        <p class="text-[13px] text-gray-900 dark:text-gray-100 mt-0.5">{{ item.value || t('preacherProfile.noInfoAvailable') }}</p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- EDIT MODE -->
                        <template v-else>
                            <form @submit.prevent="submit" class="divide-y divide-gray-100 dark:divide-gray-700">
                                <!-- Row: Ministry Name + Ministry Type -->
                                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 px-5 py-4">
                                    <div class="sm:col-span-3">
                                        <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                            {{ t('preacherProfile.ministryName') }} <span class="text-red-400">*</span>
                                        </label>
                                        <input
                                            v-model="form.ministry_name"
                                            type="text"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                            :placeholder="t('preacherProfile.ministryNamePlaceholder')"
                                        />
                                        <p v-if="form.errors.ministry_name" class="text-xs text-red-500 mt-1">{{ form.errors.ministry_name }}</p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                            {{ t('preacherProfile.ministryType') }}
                                        </label>
                                        <select
                                            v-model="form.ministry_type"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                        >
                                            <option value="">{{ t('preacherProfile.selectMinistryType') }}</option>
                                            <option v-for="mt in ministryTypes" :key="mt.value" :value="mt.value">{{ mt.label }}</option>
                                        </select>
                                        <p v-if="form.errors.ministry_type" class="text-xs text-red-500 mt-1">{{ form.errors.ministry_type }}</p>
                                    </div>
                                </div>

                                <!-- Row: Country + City -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-5 py-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                                            {{ t('preacherProfile.country') }}
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
                                            {{ t('preacherProfile.city') }}
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

                                <!-- Actions -->
                                <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-gray-50/50 dark:bg-gray-800/50">
                                    <button
                                        type="button"
                                        @click="cancelEditing"
                                        class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        {{ t('preacherProfile.cancel') }}
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
                                        {{ t('preacherProfile.save') }}
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>

                <!-- Right column: sidebar cards -->
                <div class="space-y-5">
                    <!-- Quick summary -->
                    <Card dark no-padding>
                        <template #header>
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
                                <h3 class="text-xs font-semibold text-white/90">{{ t('preacherProfile.summary') }}</h3>
                            </div>
                        </template>
                        <div class="px-5 py-3.5 space-y-3">
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('preacherProfile.totalSermons') }}</span>
                                <span class="text-blue-400 font-medium">{{ stats?.totalSermons ?? 0 }} total</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('preacherProfile.listensThisMonth') }}</span>
                                <span class="text-emerald-400 font-medium">{{ stats?.viewsThisMonth ?? 0 }} {{ t('preacherProfile.listens') }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('preacherProfile.publicationRate') }}</span>
                                <span class="text-accent-warm font-medium">{{ stats?.publicationRate ?? 0 }}%</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-white/60">{{ t('preacherProfile.lastAdded') }}</span>
                                <span class="text-purple-400 font-medium">{{ stats?.lastSermonDate ?? t('preacherProfile.none') }}</span>
                            </div>
                        </div>
                    </Card>

                    <!-- Top 3 sermons -->
                    <Card :title="t('preacherProfile.topSermons')" :subtitle="t('preacherProfile.mostListened')" no-padding>
                        <div v-if="topSermons.length === 0" class="px-5 py-6 text-center">
                            <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="icons.trophy" />
                                </svg>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('preacherProfile.noSermons') }}</p>
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
                    <Card :title="t('preacherProfile.quickActions')">
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
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ t('preacherProfile.newSermon') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('preacherProfile.publishNewSermon') }}</p>
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
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ t('preacherProfile.manageSermons') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('preacherProfile.manageSermonsDesc') }}</p>
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
                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors">{{ t('preacherProfile.viewDashboard') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('preacherProfile.viewStats') }}</p>
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
