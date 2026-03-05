<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Card from '@/Components/Card.vue';
import Toggle from '@/Components/Toggle.vue';
import axios from 'axios';

const { t } = useI18n();

const props = defineProps({
    categories: Array,
});

// Color conversion helpers (hex ↔ Flutter ARGB int)
const hexToFlutterInt = (hex) => {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return ((0xFF << 24) | (r << 16) | (g << 8) | b) >>> 0;
};
const flutterIntToHex = (val) => {
    const r = (val >> 16) & 0xFF;
    const g = (val >> 8) & 0xFF;
    const b = val & 0xFF;
    return '#' + [r, g, b].map(c => c.toString(16).padStart(2, '0')).join('');
};

// Form data
const form = ref({
    title: '',
    preacher_name: '',
    category_sermon_id: '',
    description: '',
    color: null,
    is_published: false,
});

const colorHex = ref('#6B4EAF');

const audioFile = ref(null);
const coverFile = ref(null);
const audioPreview = ref(null);
const coverPreview = ref(null);
const errors = ref({});
const submitting = ref(false);
const uploadProgress = ref(0);
const uploadPhase = ref(''); // 'uploading', 'processing', ''

// File inputs refs
const audioInput = ref(null);
const coverInput = ref(null);

// Handle audio file selection
const onAudioChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // Validate file type
    const validTypes = ['audio/mpeg', 'audio/wav', 'audio/mp4', 'audio/x-m4a', 'audio/aac', 'audio/ogg', 'audio/flac'];
    if (!validTypes.includes(file.type) && !file.name.match(/\.(mp3|wav|m4a|aac|ogg|flac)$/i)) {
        errors.value.audio_file = t('sermons.create.errors.audioFormat');
        return;
    }

    // Validate file size (200MB max)
    if (file.size > 200 * 1024 * 1024) {
        errors.value.audio_file = t('sermons.create.errors.audioSize');
        return;
    }

    audioFile.value = file;
    delete errors.value.audio_file;

    audioPreview.value = {
        name: file.name,
        size: formatFileSize(file.size),
        type: file.type || 'audio',
    };
};

// Handle cover image selection
const onCoverChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        errors.value.cover_file = t('sermons.create.errors.imageSelect');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        errors.value.cover_file = t('sermons.create.errors.imageSize');
        return;
    }

    coverFile.value = file;
    delete errors.value.cover_file;

    const reader = new FileReader();
    reader.onload = (ev) => {
        coverPreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
};

// Remove audio
const removeAudio = () => {
    audioFile.value = null;
    audioPreview.value = null;
    if (audioInput.value) audioInput.value.value = '';
};

// Remove cover
const removeCover = () => {
    coverFile.value = null;
    coverPreview.value = null;
    if (coverInput.value) coverInput.value.value = '';
};

// Format file size
const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

// Progress bar color
const progressColor = computed(() => {
    if (uploadProgress.value < 30) return 'bg-red-500';
    if (uploadProgress.value < 70) return 'bg-amber-500';
    return 'bg-emerald-500';
});

// Submit form with upload progress tracking
const submit = async () => {
    errors.value = {};

    // Client-side validation
    if (!form.value.title.trim()) {
        errors.value.title = t('sermons.create.errors.titleRequired');
    }
    if (!form.value.preacher_name.trim()) {
        errors.value.preacher_name = t('sermons.create.errors.preacherRequired');
    }
    if (!form.value.category_sermon_id) {
        errors.value.category_sermon_id = t('sermons.create.errors.categoryRequired');
    }
    if (!audioFile.value) {
        errors.value.audio_file = t('sermons.create.errors.audioRequired');
    }

    if (Object.keys(errors.value).length > 0) return;

    submitting.value = true;
    uploadProgress.value = 0;
    uploadPhase.value = 'uploading';

    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('preacher_name', form.value.preacher_name);
    formData.append('category_sermon_id', form.value.category_sermon_id);
    formData.append('description', form.value.description || '');
    formData.append('is_published', form.value.is_published ? '1' : '0');
    if (colorHex.value) formData.append('color', hexToFlutterInt(colorHex.value));
    if (audioFile.value) formData.append('audio_file', audioFile.value);
    if (coverFile.value) formData.append('cover_file', coverFile.value);

    try {
        const response = await axios.post('/admin/sermons', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
                const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                uploadProgress.value = percent;
                if (percent >= 100) {
                    uploadPhase.value = 'processing';
                }
            },
        });

        // Redirect on success (Inertia redirect from axios)
        uploadPhase.value = '';
        router.visit('/admin/sermons');

    } catch (e) {
        uploadPhase.value = '';
        uploadProgress.value = 0;

        if (e.response?.status === 422) {
            const serverErrors = e.response.data.errors || {};
            for (const [key, messages] of Object.entries(serverErrors)) {
                errors.value[key] = Array.isArray(messages) ? messages[0] : messages;
            }
        } else if (e.response?.data?.errors?.general) {
            errors.value.general = e.response.data.errors.general;
        } else {
            errors.value.general = t('sermons.create.errors.createError');
        }
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <AdminLayout :title="t('sermons.create.title')">
        <div class="max-w-5xl mx-auto space-y-4">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: t('sermons.title'), href: '/admin/sermons' },
                { label: t('sermons.create.title') },
            ]" />

            <!-- Upload progress overlay -->
            <Transition
                enter-active-class="transition-all duration-300"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="submitting" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 space-y-5">
                        <div class="flex justify-center">
                            <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
                                <svg v-if="uploadPhase === 'uploading'" class="w-8 h-8 text-primary animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <svg v-else class="w-8 h-8 text-primary animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ uploadPhase === 'uploading' ? t('sermons.create.uploadPhase1') : t('sermons.create.uploadPhase2') }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ uploadPhase === 'uploading'
                                    ? t('sermons.create.uploadWait')
                                    : t('sermons.create.uploadPhase3')
                                }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ t('sermons.create.progress') }}</span>
                                <span class="font-bold" :class="uploadProgress >= 100 ? 'text-emerald-600' : 'text-primary'">
                                    {{ uploadProgress }}%
                                </span>
                            </div>
                            <div class="w-full h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-300 ease-out"
                                    :class="progressColor"
                                    :style="{ width: uploadProgress + '%' }"
                                ></div>
                            </div>
                            <p v-if="audioFile" class="text-xs text-gray-400 text-center">
                                {{ audioFile.name }} ({{ formatFileSize(audioFile.size) }})
                            </p>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- General error -->
            <div v-if="errors.general" class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-red-700">{{ errors.general }}</p>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                    <!-- Left column: Main fields (3/5) -->
                    <div class="lg:col-span-3 space-y-4">
                        <Card :title="t('sermons.create.information')" noPadding>
                            <div class="p-5 space-y-3">
                                <!-- Title -->
                                <div>
                                    <label for="title" class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                        {{ t('sermons.create.titleLabel') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        class="w-full px-3.5 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:bg-white dark:focus:bg-gray-600 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                                        :placeholder="t('sermons.create.titlePlaceholder')"
                                        :class="{ 'border-red-300 bg-red-50/50': errors.title }"
                                    />
                                    <p v-if="errors.title" class="mt-1 text-xs text-red-500">{{ errors.title }}</p>
                                </div>

                                <!-- Preacher + Category side by side -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label for="preacher_name" class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                            {{ t('sermons.create.preacherLabel') }} <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="preacher_name"
                                            v-model="form.preacher_name"
                                            type="text"
                                            class="w-full px-3.5 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:bg-white dark:focus:bg-gray-600 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                                            :placeholder="t('sermons.create.preacherPlaceholder')"
                                            :class="{ 'border-red-300 bg-red-50/50': errors.preacher_name }"
                                        />
                                        <p v-if="errors.preacher_name" class="mt-1 text-xs text-red-500">{{ errors.preacher_name }}</p>
                                    </div>
                                    <div>
                                        <label for="category" class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                            {{ t('sermons.create.categoryLabel') }} <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            id="category"
                                            v-model="form.category_sermon_id"
                                            class="w-full px-3.5 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-600 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                                            :class="{ 'border-red-300 bg-red-50/50': errors.category_sermon_id }"
                                        >
                                            <option value="">{{ t('common.select') }}</option>
                                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                        </select>
                                        <p v-if="errors.category_sermon_id" class="mt-1 text-xs text-red-500">{{ errors.category_sermon_id }}</p>
                                    </div>
                                </div>

                                <!-- Description (compact) -->
                                <div>
                                    <label for="description" class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                        {{ t('sermons.create.descriptionLabel') }} <span class="text-gray-400 dark:text-gray-500 font-normal">({{ t('common.optional') }})</span>
                                    </label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="w-full px-3.5 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:bg-white dark:focus:bg-gray-600 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all resize-none"
                                        :placeholder="t('sermons.create.descriptionPlaceholder')"
                                    ></textarea>
                                </div>

                                <!-- Couleur -->
                                <div>
                                    <label for="color" class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">
                                        {{ t('sermons.create.colorLabel') }} <span class="text-gray-400 dark:text-gray-500 font-normal">({{ t('common.optional') }})</span>
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <input
                                            id="color"
                                            v-model="colorHex"
                                            type="color"
                                            class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer p-0.5 bg-gray-50 dark:bg-gray-700"
                                        />
                                        <span class="text-sm text-gray-500 dark:text-gray-400 font-mono">{{ colorHex }}</span>
                                    </div>
                                </div>
                            </div>
                        </Card>

                        <!-- Audio Upload (compact) -->
                        <Card :title="t('sermons.create.audioFile')" noPadding>
                            <div class="p-5">
                                <!-- Dropzone -->
                                <div v-if="!audioPreview" class="relative">
                                    <input
                                        ref="audioInput"
                                        type="file"
                                        accept="audio/*,.mp3,.wav,.m4a,.aac,.ogg,.flac"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                        @change="onAudioChange"
                                    />
                                    <div
                                        class="flex items-center gap-4 px-5 py-4 border-2 border-dashed rounded-xl transition-colors"
                                        :class="errors.audio_file ? 'border-red-300 bg-red-50/30' : 'border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/30 hover:border-primary/40 hover:bg-primary/5 dark:hover:bg-primary/10'"
                                    >
                                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('sermons.create.audioDropzone') }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">MP3, WAV, M4A, AAC, OGG, FLAC — max 200 MB</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Audio file preview -->
                                <div v-else class="flex items-center gap-3 px-4 py-3 bg-emerald-50/60 dark:bg-emerald-900/20 border border-emerald-200/60 dark:border-emerald-800/40 rounded-xl">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ audioPreview.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ audioPreview.size }}</p>
                                    </div>
                                    <button type="button" @click="removeAudio" class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <p v-if="errors.audio_file" class="mt-1 text-xs text-red-500">{{ errors.audio_file }}</p>
                            </div>
                        </Card>
                    </div>

                    <!-- Right column: Media + Publish (2/5) -->
                    <div class="lg:col-span-2 space-y-4">
                        <!-- Cover Image (compact) -->
                        <Card :title="t('sermons.create.cover')" noPadding>
                            <div class="p-5">
                                <div v-if="!coverPreview" class="relative">
                                    <input
                                        ref="coverInput"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                        @change="onCoverChange"
                                    />
                                    <div
                                        class="flex flex-col items-center justify-center py-6 border-2 border-dashed rounded-xl transition-colors"
                                        :class="errors.cover_file ? 'border-red-300 bg-red-50/30' : 'border-gray-200 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-700/30 hover:border-primary/40 hover:bg-primary/5 dark:hover:bg-primary/10'"
                                    >
                                        <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ t('sermons.create.coverDropzone') }}</p>
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">JPG, PNG, WebP — max 5 MB</p>
                                    </div>
                                </div>

                                <div v-else class="relative">
                                    <img :src="coverPreview" :alt="t('sermons.create.cover')" class="w-full h-36 object-cover rounded-xl ring-1 ring-gray-200" />
                                    <button
                                        type="button"
                                        @click="removeCover"
                                        class="absolute top-2 right-2 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center shadow-md hover:bg-red-600 transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <p v-if="errors.cover_file" class="mt-1 text-xs text-red-500">{{ errors.cover_file }}</p>
                            </div>
                        </Card>

                        <!-- Publication toggle -->
                        <Card noPadding>
                            <div class="p-5 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('sermons.create.publishNow') }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ t('sermons.create.publishNowDesc') }}</p>
                                </div>
                                <Toggle v-model="form.is_published" color="emerald" />
                            </div>
                        </Card>

                        <!-- Actions -->
                        <div class="flex items-center gap-3">
                            <Link
                                href="/admin/sermons"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                {{ t('common.cancel') }}
                            </Link>
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-primary rounded-xl shadow-md shadow-primary/25 hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg v-if="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ submitting ? t('common.sending') : t('common.create') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
