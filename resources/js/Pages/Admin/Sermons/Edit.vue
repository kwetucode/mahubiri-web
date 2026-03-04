<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Card from '@/Components/Card.vue';
import Toggle from '@/Components/Toggle.vue';
import axios from 'axios';

const props = defineProps({
    sermon: Object,
    categories: Array,
});

// Form data (pre-filled with existing sermon)
const form = ref({
    title: props.sermon.title || '',
    preacher_name: props.sermon.preacher_name || '',
    category_sermon_id: props.sermon.category_sermon_id || '',
    description: props.sermon.description || '',
    color: props.sermon.color || null,
    is_published: props.sermon.is_published ?? false,
});

const audioFile = ref(null);
const coverFile = ref(null);
const audioPreview = ref(null);
const coverPreview = ref(props.sermon.cover_url || null);
const errors = ref({});
const submitting = ref(false);
const uploadProgress = ref(0);
const uploadPhase = ref('');

const audioInput = ref(null);
const coverInput = ref(null);

// Current audio info
const hasExistingAudio = computed(() => !!props.sermon.audio_url);

// Handle audio file selection
const onAudioChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const validTypes = ['audio/mpeg', 'audio/wav', 'audio/mp4', 'audio/x-m4a', 'audio/aac', 'audio/ogg', 'audio/flac'];
    if (!validTypes.includes(file.type) && !file.name.match(/\.(mp3|wav|m4a|aac|ogg|flac)$/i)) {
        errors.value.audio_file = 'Format audio non supporté.';
        return;
    }

    if (file.size > 200 * 1024 * 1024) {
        errors.value.audio_file = 'Le fichier audio ne doit pas dépasser 200 MB.';
        return;
    }

    audioFile.value = file;
    delete errors.value.audio_file;

    audioPreview.value = {
        name: file.name,
        size: formatFileSize(file.size),
    };
};

// Handle cover image selection
const onCoverChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        errors.value.cover_file = 'Veuillez sélectionner une image.';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        errors.value.cover_file = "L'image ne doit pas dépasser 5 MB.";
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

const removeNewAudio = () => {
    audioFile.value = null;
    audioPreview.value = null;
    if (audioInput.value) audioInput.value.value = '';
};

const removeCover = () => {
    coverFile.value = null;
    coverPreview.value = null;
    if (coverInput.value) coverInput.value.value = '';
};

const formatFileSize = (bytes) => {
    if (!bytes) return '—';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const progressColor = computed(() => {
    if (uploadProgress.value < 30) return 'bg-red-500';
    if (uploadProgress.value < 70) return 'bg-amber-500';
    return 'bg-emerald-500';
});

const submit = async () => {
    errors.value = {};

    if (!form.value.title.trim()) {
        errors.value.title = 'Le titre est requis.';
    }
    if (!form.value.preacher_name.trim()) {
        errors.value.preacher_name = 'Le nom du prédicateur est requis.';
    }
    if (!form.value.category_sermon_id) {
        errors.value.category_sermon_id = 'La catégorie est requise.';
    }

    if (Object.keys(errors.value).length > 0) return;

    submitting.value = true;
    uploadProgress.value = 0;
    uploadPhase.value = audioFile.value ? 'uploading' : 'processing';

    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('title', form.value.title);
    formData.append('preacher_name', form.value.preacher_name);
    formData.append('category_sermon_id', form.value.category_sermon_id);
    formData.append('description', form.value.description || '');
    formData.append('is_published', form.value.is_published ? '1' : '0');
    if (form.value.color) formData.append('color', form.value.color);
    if (audioFile.value) formData.append('audio_file', audioFile.value);
    if (coverFile.value) formData.append('cover_file', coverFile.value);

    try {
        await axios.post(`/admin/sermons/${props.sermon.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
                const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                uploadProgress.value = percent;
                if (percent >= 100) {
                    uploadPhase.value = 'processing';
                }
            },
        });

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
        } else {
            errors.value.general = 'Une erreur est survenue lors de la mise à jour.';
        }
    } finally {
        submitting.value = false;
    }
};
</script>

<template>
    <AdminLayout title="Modifier la prédication">
        <div class="max-w-3xl mx-auto space-y-5">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: 'Prédications', href: '/admin/sermons' },
                { label: sermon.title },
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
                <div v-if="submitting && audioFile" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 space-y-5">
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
                            <h3 class="text-lg font-bold text-gray-900">
                                {{ uploadPhase === 'uploading' ? 'Envoi en cours...' : 'Traitement du fichier...' }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ uploadPhase === 'uploading'
                                    ? 'Veuillez patienter pendant l\'envoi du fichier audio.'
                                    : 'Extraction des métadonnées audio en cours...'
                                }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-700">Progression</span>
                                <span class="font-bold" :class="uploadProgress >= 100 ? 'text-emerald-600' : 'text-primary'">
                                    {{ uploadProgress }}%
                                </span>
                            </div>
                            <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
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
            <div v-if="errors.general" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-red-700">{{ errors.general }}</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Basic Info -->
                <Card title="Informations générales" subtitle="Modifier les détails de la prédication">
                    <div class="space-y-4">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Titre <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                                :class="{ 'border-red-300 bg-red-50/50': errors.title }"
                            />
                            <p v-if="errors.title" class="mt-1 text-xs text-red-500">{{ errors.title }}</p>
                        </div>

                        <!-- Preacher name -->
                        <div>
                            <label for="preacher_name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Nom du prédicateur <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="preacher_name"
                                v-model="form.preacher_name"
                                type="text"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                                :class="{ 'border-red-300 bg-red-50/50': errors.preacher_name }"
                            />
                            <p v-if="errors.preacher_name" class="mt-1 text-xs text-red-500">{{ errors.preacher_name }}</p>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Catégorie <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="category"
                                v-model="form.category_sermon_id"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                                :class="{ 'border-red-300 bg-red-50/50': errors.category_sermon_id }"
                            >
                                <option value="">Sélectionner une catégorie</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                            <p v-if="errors.category_sermon_id" class="mt-1 text-xs text-red-500">{{ errors.category_sermon_id }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Description
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all resize-none"
                            ></textarea>
                        </div>
                    </div>
                </Card>

                <!-- Audio Upload -->
                <Card title="Fichier audio" subtitle="Remplacer le fichier audio existant (optionnel)">
                    <div class="space-y-3">
                        <!-- Current audio info -->
                        <div v-if="hasExistingAudio && !audioPreview" class="flex items-center gap-4 p-4 bg-blue-50/60 border border-blue-200/60 rounded-xl">
                            <div class="w-11 h-11 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">Audio actuel</p>
                                <p class="text-xs text-gray-500">
                                    {{ sermon.duration_formatted || '—' }}
                                    <span v-if="sermon.size"> · {{ formatFileSize(sermon.size) }}</span>
                                </p>
                            </div>
                            <span class="px-2 py-1 text-[10px] font-semibold text-blue-700 bg-blue-100 rounded-md uppercase">actuel</span>
                        </div>

                        <!-- New audio dropzone -->
                        <div v-if="!audioPreview" class="relative">
                            <input
                                ref="audioInput"
                                type="file"
                                accept="audio/*,.mp3,.wav,.m4a,.aac,.ogg,.flac"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                @change="onAudioChange"
                            />
                            <div class="flex flex-col items-center justify-center py-8 border-2 border-dashed border-gray-200 bg-gray-50/50 rounded-xl hover:border-primary/40 hover:bg-primary/5 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">
                                    {{ hasExistingAudio ? 'Remplacer le fichier audio' : 'Ajouter un fichier audio' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">MP3, WAV, M4A, AAC, OGG, FLAC — max 200 MB</p>
                            </div>
                        </div>

                        <!-- New audio preview -->
                        <div v-else class="flex items-center gap-4 p-4 bg-emerald-50/60 border border-emerald-200/60 rounded-xl">
                            <div class="w-11 h-11 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ audioPreview.name }}</p>
                                <p class="text-xs text-gray-500">{{ audioPreview.size }} — Nouveau fichier</p>
                            </div>
                            <button
                                type="button"
                                @click="removeNewAudio"
                                class="p-2 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <p v-if="errors.audio_file" class="text-xs text-red-500">{{ errors.audio_file }}</p>
                    </div>
                </Card>

                <!-- Cover Image -->
                <Card title="Image de couverture" subtitle="Modifier ou ajouter une image d'illustration">
                    <div>
                        <div v-if="!coverPreview" class="relative">
                            <input
                                ref="coverInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                @change="onCoverChange"
                            />
                            <div class="flex flex-col items-center justify-center py-8 border-2 border-dashed border-gray-200 bg-gray-50/50 rounded-xl hover:border-primary/40 hover:bg-primary/5 transition-colors">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center mb-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Ajouter / Modifier la couverture</p>
                                <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, WebP — max 5 MB</p>
                            </div>
                        </div>

                        <div v-else class="relative inline-block">
                            <img :src="coverPreview" alt="Couverture" class="w-40 h-40 object-cover rounded-xl ring-1 ring-gray-200" />
                            <button
                                type="button"
                                @click="removeCover"
                                class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center shadow-md hover:bg-red-600 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <p v-if="errors.cover_file" class="mt-1.5 text-xs text-red-500">{{ errors.cover_file }}</p>
                    </div>
                </Card>

                <!-- Publication -->
                <Card title="Publication" subtitle="Contrôler la visibilité de la prédication">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Publiée</p>
                            <p class="text-xs text-gray-400 mt-0.5">Si désactivé, la prédication sera masquée pour les utilisateurs</p>
                        </div>
                        <Toggle v-model="form.is_published" color="emerald" />
                    </div>
                </Card>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-2">
                    <Link
                        href="/admin/sermons"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="submitting"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-primary rounded-xl shadow-md shadow-primary/25 hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg v-if="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ submitting ? 'Mise à jour...' : 'Enregistrer les modifications' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
