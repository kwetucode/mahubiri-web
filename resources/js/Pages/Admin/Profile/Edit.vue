<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();
const page = usePage();
const user = computed(() => page.props.auth.user);
const twoFactor = computed(() => page.props.twoFactor ?? { enabled: false, confirmed: false });

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

// 2FA
const twoFactorEnableForm = useForm({ password: '' });
const twoFactorConfirmForm = useForm({ code: '' });
const twoFactorDisableForm = useForm({ password: '' });
const twoFactorRecoveryForm = useForm({ password: '' });
const showEnablePassword = ref(false);
const showDisablePassword = ref(false);
const showRecoveryPassword = ref(false);
const qrCodeSvg = ref(null);
const recoveryCodes = ref(null);
const setupStep = ref('idle'); // idle | setup | confirm | done
const isTwoFactorActive = ref(twoFactor.value.enabled && twoFactor.value.confirmed);

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

// 2FA methods
const enableTwoFactor = () => {
    twoFactorEnableForm.post('/admin/profile/two-factor/enable', {
        preserveScroll: true,
        onSuccess: () => {
            const flash = page.props.flash ?? {};
            if (flash.twoFactorQrCode) {
                qrCodeSvg.value = flash.twoFactorQrCode;
                recoveryCodes.value = flash.twoFactorRecoveryCodes || null;
                setupStep.value = 'confirm';
            }
            twoFactorEnableForm.reset();
            showEnablePassword.value = false;
        },
    });
};

const confirmTwoFactor = () => {
    twoFactorConfirmForm.post('/admin/profile/two-factor/confirm', {
        preserveScroll: true,
        onSuccess: () => {
            setupStep.value = 'done';
            isTwoFactorActive.value = true;
            twoFactorConfirmForm.reset();
            qrCodeSvg.value = null;
        },
    });
};

const disableTwoFactor = () => {
    twoFactorDisableForm.post('/admin/profile/two-factor/disable', {
        preserveScroll: true,
        onSuccess: () => {
            setupStep.value = 'idle';
            isTwoFactorActive.value = false;
            recoveryCodes.value = null;
            qrCodeSvg.value = null;
            twoFactorDisableForm.reset();
            showDisablePassword.value = false;
        },
    });
};

const fetchRecoveryCodes = () => {
    twoFactorRecoveryForm.post('/admin/profile/two-factor/recovery-codes', {
        preserveScroll: true,
        onSuccess: () => {
            const flash = page.props.flash ?? {};
            recoveryCodes.value = flash.twoFactorRecoveryCodes || null;
            twoFactorRecoveryForm.reset();
            showRecoveryPassword.value = false;
        },
    });
};

const regenerateRecoveryCodes = () => {
    twoFactorRecoveryForm.post('/admin/profile/two-factor/regenerate', {
        preserveScroll: true,
        onSuccess: () => {
            const flash = page.props.flash ?? {};
            recoveryCodes.value = flash.twoFactorRecoveryCodes || null;
            twoFactorRecoveryForm.reset();
        },
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
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
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
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    {{ t('profile.tabPassword') }}
                </button>
                <button
                    @click="activeTab = 'security'"
                    class="flex items-center gap-2 px-6 py-3.5 text-sm font-semibold transition-all duration-200 border-b-2 -mb-px"
                    :class="activeTab === 'security'
                        ? 'border-primary text-primary bg-primary/5'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    {{ t('profile.tabSecurity') }}
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

            <!-- Tab: Security (2FA) -->
            <div v-show="activeTab === 'security'">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/50">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ t('profile.securityTitle') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('profile.securitySubtitle') }}</p>
                </div>

                <div class="px-6 py-5 space-y-6">
                    <!-- Status badge -->
                    <div class="flex items-center gap-3 p-4 rounded-xl" :class="isTwoFactorActive ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600'">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" :class="isTwoFactorActive ? 'bg-green-100 dark:bg-green-900/40' : 'bg-gray-200 dark:bg-gray-600'">
                            <svg v-if="isTwoFactorActive" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <svg v-else class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold" :class="isTwoFactorActive ? 'text-green-800 dark:text-green-300' : 'text-gray-700 dark:text-gray-300'">
                                {{ isTwoFactorActive ? t('profile.twoFactorActive') : t('profile.twoFactorInactive') }}
                            </p>
                            <p class="text-xs" :class="isTwoFactorActive ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                                {{ isTwoFactorActive ? t('profile.twoFactorActiveDesc') : t('profile.twoFactorInactiveDesc') }}
                            </p>
                        </div>
                    </div>

                    <!-- Enable 2FA (when not enabled) -->
                    <div v-if="!isTwoFactorActive && setupStep === 'idle'">
                        <form @submit.prevent="enableTwoFactor" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                    {{ t('profile.confirmYourPassword') }}
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="twoFactorEnableForm.password"
                                        :type="showEnablePassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                                        :placeholder="t('profile.currentPassword')"
                                    />
                                    <button type="button" @click="showEnablePassword = !showEnablePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                        <svg v-if="!showEnablePassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                <p v-if="twoFactorEnableForm.errors.password" class="mt-1.5 text-sm text-red-500">{{ twoFactorEnableForm.errors.password }}</p>
                            </div>
                            <button
                                type="submit"
                                :disabled="twoFactorEnableForm.processing"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-primary hover:bg-primary-dark shadow-lg shadow-primary/25 transition-all duration-200 disabled:opacity-50"
                            >
                                <svg v-if="twoFactorEnableForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                {{ t('profile.enableTwoFactor') }}
                            </button>
                        </form>
                    </div>

                    <!-- QR Code + Confirm step -->
                    <div v-if="setupStep === 'confirm'" class="space-y-5">
                        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                            <p class="text-sm text-amber-800 dark:text-amber-300 font-medium">{{ t('profile.scanQrCode') }}</p>
                        </div>

                        <!-- QR Code -->
                        <div class="flex justify-center p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-600">
                            <div v-html="qrCodeSvg" class="[&>svg]:w-48 [&>svg]:h-48"></div>
                        </div>

                        <!-- Recovery codes (shown during setup) -->
                        <div v-if="recoveryCodes" class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-200 dark:border-gray-600">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ t('profile.recoveryCodes') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ t('profile.recoveryCodesHint') }}</p>
                            <div class="grid grid-cols-2 gap-2">
                                <code v-for="code in recoveryCodes" :key="code" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-600 rounded-lg text-xs font-mono text-gray-800 dark:text-gray-200 text-center">
                                    {{ code }}
                                </code>
                            </div>
                        </div>

                        <!-- Confirm with TOTP code -->
                        <form @submit.prevent="confirmTwoFactor" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                    {{ t('profile.enterTotpCode') }}
                                </label>
                                <input
                                    v-model="twoFactorConfirmForm.code"
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    maxlength="6"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors tracking-widest text-center text-lg font-mono"
                                    placeholder="000000"
                                />
                                <p v-if="twoFactorConfirmForm.errors.code" class="mt-1.5 text-sm text-red-500">{{ twoFactorConfirmForm.errors.code }}</p>
                            </div>
                            <button
                                type="submit"
                                :disabled="twoFactorConfirmForm.processing"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-primary hover:bg-primary-dark shadow-lg shadow-primary/25 transition-all duration-200 disabled:opacity-50"
                            >
                                <svg v-if="twoFactorConfirmForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                {{ t('profile.confirmCode') }}
                            </button>
                        </form>
                    </div>

                    <!-- 2FA is enabled — manage section -->
                    <div v-if="isTwoFactorActive" class="space-y-5">
                        <!-- Recovery codes section -->
                        <div class="p-5 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-200 dark:border-gray-600 space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ t('profile.recoveryCodes') }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t('profile.recoveryCodesManageHint') }}</p>
                            </div>

                            <!-- Show codes if loaded -->
                            <div v-if="recoveryCodes" class="grid grid-cols-2 gap-2">
                                <code v-for="code in recoveryCodes" :key="code" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-600 rounded-lg text-xs font-mono text-gray-800 dark:text-gray-200 text-center">
                                    {{ code }}
                                </code>
                            </div>

                            <!-- Password form to view/regenerate codes -->
                            <div v-if="!recoveryCodes" class="space-y-3">
                                <div class="relative">
                                    <input
                                        v-model="twoFactorRecoveryForm.password"
                                        :type="showRecoveryPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        class="w-full px-4 py-2.5 pr-11 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                                        :placeholder="t('profile.currentPassword')"
                                    />
                                    <button type="button" @click="showRecoveryPassword = !showRecoveryPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                        <svg v-if="!showRecoveryPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                <p v-if="twoFactorRecoveryForm.errors.password" class="text-sm text-red-500">{{ twoFactorRecoveryForm.errors.password }}</p>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="fetchRecoveryCodes"
                                        :disabled="twoFactorRecoveryForm.processing"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors disabled:opacity-50"
                                    >
                                        {{ t('profile.showRecoveryCodes') }}
                                    </button>
                                </div>
                            </div>

                            <div v-if="recoveryCodes" class="flex gap-2">
                                <button
                                    type="button"
                                    @click="regenerateRecoveryCodes"
                                    :disabled="twoFactorRecoveryForm.processing"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors disabled:opacity-50"
                                >
                                    {{ t('profile.regenerateRecoveryCodes') }}
                                </button>
                                <button
                                    type="button"
                                    @click="recoveryCodes = null"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                >
                                    {{ t('profile.hideRecoveryCodes') }}
                                </button>
                            </div>
                        </div>

                        <!-- Disable 2FA -->
                        <div class="p-5 bg-red-50 dark:bg-red-900/10 rounded-xl border border-red-200 dark:border-red-800 space-y-4">
                            <div>
                                <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">{{ t('profile.disableTwoFactor') }}</h3>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ t('profile.disableTwoFactorHint') }}</p>
                            </div>
                            <form @submit.prevent="disableTwoFactor" class="space-y-3">
                                <div class="relative">
                                    <input
                                        v-model="twoFactorDisableForm.password"
                                        :type="showDisablePassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        class="w-full px-4 py-2.5 pr-11 rounded-xl border border-red-200 dark:border-red-700 bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-red-300 focus:border-red-400 transition-colors"
                                        :placeholder="t('profile.currentPassword')"
                                    />
                                    <button type="button" @click="showDisablePassword = !showDisablePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                        <svg v-if="!showDisablePassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                <p v-if="twoFactorDisableForm.errors.password" class="text-sm text-red-500">{{ twoFactorDisableForm.errors.password }}</p>
                                <button
                                    type="submit"
                                    :disabled="twoFactorDisableForm.processing"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-600/25 transition-all duration-200 disabled:opacity-50"
                                >
                                    <svg v-if="twoFactorDisableForm.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ t('profile.disableTwoFactor') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
