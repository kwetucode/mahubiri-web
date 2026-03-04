<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/Card.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    church: Object,
});

const editing = ref(false);

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
    { label: 'Nom complet', value: props.church.name, icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { label: 'Abréviation', value: props.church.abbreviation, icon: 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14' },
    { label: 'Visionnaire', value: props.church.visionary_name, icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { label: 'Pays', value: props.church.country_name, icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { label: 'Ville', value: props.church.city, icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' },
    { label: 'Adresse', value: props.church.address, icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
]);
</script>

<template>
    <AdminLayout title="Mon église">
        <div class="space-y-4">
            <!-- Breadcrumb -->
            <Breadcrumb :items="[
                { label: 'Mon église' },
            ]" />

            <div class="max-w-4xl">
                <!-- Combined card -->
                <div class="bg-white rounded-2xl border border-gray-200/60 shadow-sm overflow-hidden">
                    <!-- Header with logo -->
                    <div class="relative">
                        <!-- Gradient strip -->
                        <div class="h-24 bg-linear-to-r from-primary/80 via-primary/60 to-primary/30"></div>

                        <!-- Logo + Church name overlay -->
                        <div class="px-5 pb-4 -mt-10 flex items-end gap-4">
                            <!-- Logo -->
                            <div class="relative shrink-0">
                                <div
                                    class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg overflow-hidden bg-white"
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

                            <!-- Church name & meta -->
                            <div class="flex-1 min-w-0 pb-1">
                                <h1 class="text-lg font-bold text-gray-900 truncate">{{ church.name }}</h1>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span
                                        v-if="church.abbreviation"
                                        class="text-xs font-semibold text-gray-500"
                                    >{{ church.abbreviation }}</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold"
                                        :class="church.is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10' : 'bg-red-50 text-red-600 ring-1 ring-red-600/10'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="church.is_active ? 'bg-emerald-500' : 'bg-red-400'"></span>
                                        {{ church.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="text-[11px] text-gray-400">· {{ church.sermons_count }} prédication{{ church.sermons_count > 1 ? 's' : '' }}</span>
                                    <span class="text-[11px] text-gray-400">· Depuis {{ church.created_at }}</span>
                                </div>
                            </div>

                            <!-- Edit / Cancel button -->
                            <div class="shrink-0 pb-1">
                                <button
                                    v-if="!editing"
                                    @click="startEditing"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white rounded-xl text-xs font-semibold shadow-md shadow-primary/25 hover:bg-primary-dark transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Modifier
                                </button>
                                <button
                                    v-else
                                    @click="cancelEditing"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 text-gray-600 rounded-xl text-xs font-semibold hover:bg-gray-200 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Annuler
                                </button>
                            </div>
                        </div>

                        <!-- Logo error -->
                        <p v-if="form.errors.logo" class="px-5 pb-2 text-xs text-red-500">{{ form.errors.logo }}</p>
                    </div>

                    <!-- VIEW MODE -->
                    <template v-if="!editing">
                        <div class="divide-y divide-gray-100">
                            <div
                                v-for="item in infoItems"
                                :key="item.label"
                                class="flex items-start gap-3 px-5 py-3"
                            >
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-400 shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">{{ item.label }}</p>
                                    <p class="text-[13px] text-gray-900 mt-0.5">{{ item.value || '—' }}</p>
                                </div>
                            </div>

                            <!-- Description (full width) -->
                            <div class="px-5 py-3">
                                <div class="flex items-start gap-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-400 shrink-0 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Description</p>
                                        <p class="text-[13px] text-gray-900 mt-0.5 whitespace-pre-line">{{ church.description || '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- EDIT MODE -->
                    <template v-else>
                        <form @submit.prevent="submit" class="divide-y divide-gray-100">
                            <!-- Row: Nom + Abréviation -->
                            <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 px-5 py-4">
                                <div class="sm:col-span-3">
                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Nom de l'église <span class="text-red-400">*</span>
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                        placeholder="Nom complet de l'église"
                                    />
                                    <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Abréviation
                                    </label>
                                    <input
                                        v-model="form.abbreviation"
                                        type="text"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                        placeholder="Ex: CBCA"
                                    />
                                    <p v-if="form.errors.abbreviation" class="text-xs text-red-500 mt-1">{{ form.errors.abbreviation }}</p>
                                </div>
                            </div>

                            <!-- Row: Visionnaire -->
                            <div class="px-5 py-4">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                    Nom du visionnaire / Pasteur principal
                                </label>
                                <input
                                    v-model="form.visionary_name"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                    placeholder="Nom complet du visionnaire"
                                />
                                <p v-if="form.errors.visionary_name" class="text-xs text-red-500 mt-1">{{ form.errors.visionary_name }}</p>
                            </div>

                            <!-- Row: Pays + Ville -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-5 py-4">
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Pays
                                    </label>
                                    <input
                                        v-model="form.country_name"
                                        type="text"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                        placeholder="Ex: RD Congo"
                                    />
                                    <p v-if="form.errors.country_name" class="text-xs text-red-500 mt-1">{{ form.errors.country_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                        Ville
                                    </label>
                                    <input
                                        v-model="form.city"
                                        type="text"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                        placeholder="Ex: Goma"
                                    />
                                    <p v-if="form.errors.city" class="text-xs text-red-500 mt-1">{{ form.errors.city }}</p>
                                </div>
                            </div>

                            <!-- Row: Adresse -->
                            <div class="px-5 py-4">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                    Adresse complète
                                </label>
                                <input
                                    v-model="form.address"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                    placeholder="Rue, avenue, quartier..."
                                />
                                <p v-if="form.errors.address" class="text-xs text-red-500 mt-1">{{ form.errors.address }}</p>
                            </div>

                            <!-- Row: Description -->
                            <div class="px-5 py-4">
                                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                                    Description
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors resize-none"
                                    placeholder="Courte description de l'église..."
                                ></textarea>
                                <p v-if="form.errors.description" class="text-xs text-red-500 mt-1">{{ form.errors.description }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-gray-50/50">
                                <button
                                    type="button"
                                    @click="cancelEditing"
                                    class="px-4 py-2 text-xs font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors"
                                >
                                    Annuler
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
                                    Enregistrer
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
