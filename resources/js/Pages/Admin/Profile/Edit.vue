<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const page = usePage();
const user = computed(() => page.props.auth.user);

const profileForm = useForm({
    name: user.value.name,
    email: user.value.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const activeTab = ref('profile');
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Avatar
const avatarInput = ref(null);
const avatarPreview = ref(null);
const avatarFile = ref(null);
const avatarUploading = ref(false);

const currentAvatar = computed(() => {
    if (avatarPreview.value) return avatarPreview.value;
    if (user.value.avatar_url) return `/${user.value.avatar_url}`;
    return null;
});

const userInitial = computed(() => user.value.name?.charAt(0)?.toUpperCase() || '?');

const onAvatarSelected = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    avatarFile.value = file;
    const reader = new FileReader();
    reader.onload = (ev) => { avatarPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const uploadAvatar = () => {
    if (!avatarFile.value) return;
    avatarUploading.value = true;
    const formData = new FormData();
    formData.append('avatar', avatarFile.value);
    router.post('/admin/profile/avatar', formData, {
        preserveScroll: true,
        onFinish: () => {
            avatarUploading.value = false;
            avatarFile.value = null;
            avatarPreview.value = null;
            if (avatarInput.value) avatarInput.value.value = '';
        },
    });
};

const removeAvatar = () => {
    if (avatarPreview.value) {
        avatarPreview.value = null;
        avatarFile.value = null;
        if (avatarInput.value) avatarInput.value.value = '';
        return;
    }
    router.delete('/admin/profile/avatar', { preserveScroll: true });
};

const updateProfile = () => {
    profileForm.put('/admin/profile', {
        preserveScroll: true,
    });
};

const updatePassword = () => {
    passwordForm.put('/admin/profile/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <!-- Page header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ t('profile.title') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('profile.subtitle') }}</p>
        </div>

        <!-- Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 shadow-sm overflow-hidden">
            <!-- Tab navigation -->
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <button
                    @click="activeTab = 'profile'"
                    class="flex items-center gap-2 px-6 py-3.5 text-sm font-semibold transition-all duration-200 border-b-2 -mb-px"
                    :class="activeTab === 'profile'
                        ? 'border-primary text-primary bg-primary/5'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ t('profile.tabProfile') }}
                </button>
                <button
                    @click="activeTab = 'password'"
                    class="flex items-center gap-2 px-6 py-3.5 text-sm font-semibold transition-all duration-200 border-b-2 -mb-px"
                    :class="activeTab === 'password'
                        ? 'border-primary text-primary bg-primary/5'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    {{ t('profile.tabPassword') }}
                </button>
            </div>

            <!-- Tab: Profile Information -->
            <div v-show="activeTab === 'profile'">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/50">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('profile.infoTitle') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('profile.infoSubtitle') }}</p>
                </div>

                <!-- Avatar section -->
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/50">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        {{ t('profile.avatar') }}
                    </label>
                    <div class="flex items-center gap-5">
                        <!-- Avatar preview -->
                        <button
                            type="button"
                            @click="avatarInput?.click()"
                            class="relative group w-20 h-20 rounded-full overflow-hidden ring-2 ring-gray-200 dark:ring-gray-600 hover:ring-primary/50 transition-all duration-200 flex-shrink-0 cursor-pointer"
                        >
                            <img
                                v-if="currentAvatar"
                                :src="currentAvatar"
                                alt="Avatar"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center">
                                <span class="text-2xl font-bold text-primary">{{ userInitial }}</span>
                            </div>
                            <!-- Hover overlay -->
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                        </button>
                        <input
                            ref="avatarInput"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="hidden"
                            @change="onAvatarSelected"
                        />
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <button
                                    v-if="avatarFile"
                                    type="button"
                                    @click="uploadAvatar"
                                    :disabled="avatarUploading"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-primary hover:bg-primary-dark shadow-sm transition-all duration-200 disabled:opacity-50"
                                >
                                    <svg v-if="avatarUploading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ t('profile.avatarSave') }}
                                </button>
                                <button
                                    v-if="!avatarFile"
                                    type="button"
                                    @click="avatarInput?.click()"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ t('profile.avatarChange') }}
                                </button>
                                <button
                                    v-if="currentAvatar"
                                    type="button"
                                    @click="removeAvatar"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    {{ t('profile.avatarRemove') }}
                                </button>
                            </div>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ t('profile.avatarHint') }}</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="updateProfile" class="px-6 py-5 space-y-5">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ t('profile.name') }}
                        </label>
                        <input
                            id="name"
                            v-model="profileForm.name"
                            type="text"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                        />
                        <p v-if="profileForm.errors.name" class="mt-1.5 text-sm text-red-500">{{ profileForm.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div class="group relative">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ t('profile.email') }}
                        </label>
                        <input
                            id="email"
                            :value="profileForm.email"
                            type="email"
                            readonly
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700/70 text-gray-500 dark:text-gray-400 text-sm cursor-not-allowed"
                        />
                        <div class="absolute left-0 right-0 -bottom-9 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-10">
                            <p class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700/50 rounded-lg px-3 py-1.5 inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ t('profile.emailReadonly') }}
                            </p>
                        </div>
                        <p v-if="profileForm.errors.email" class="mt-1.5 text-sm text-red-500">{{ profileForm.errors.email }}</p>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            :disabled="profileForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-primary hover:bg-primary-dark shadow-lg shadow-primary/25 transition-all duration-200 disabled:opacity-50"
                        >
                            <svg v-if="profileForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ t('profile.save') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab: Update Password -->
            <div v-show="activeTab === 'password'">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/50">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('profile.passwordTitle') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('profile.passwordSubtitle') }}</p>
                </div>
                <form @submit.prevent="updatePassword" class="px-6 py-5 space-y-5">
                    <!-- Current password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ t('profile.currentPassword') }}
                        </label>
                        <div class="relative">
                            <input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                            />
                            <button type="button" @click="showCurrentPassword = !showCurrentPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg v-if="!showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.current_password" class="mt-1.5 text-sm text-red-500">{{ passwordForm.errors.current_password }}</p>
                    </div>

                    <!-- New password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ t('profile.newPassword') }}
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="passwordForm.password"
                                :type="showNewPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                            />
                            <button type="button" @click="showNewPassword = !showNewPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg v-if="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.password" class="mt-1.5 text-sm text-red-500">{{ passwordForm.errors.password }}</p>
                    </div>

                    <!-- Confirm password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            {{ t('profile.confirmPassword') }}
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                            />
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-primary hover:bg-primary-dark shadow-lg shadow-primary/25 transition-all duration-200 disabled:opacity-50"
                        >
                            <svg v-if="passwordForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ t('profile.updatePassword') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
