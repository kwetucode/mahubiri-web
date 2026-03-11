<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';

const { t } = useI18n();
const { theme, toggleTheme } = useTheme();
const page = usePage();

const email = computed(() => page.props.email || '');
const status = computed(() => page.props.status || '');

const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const resending = ref(false);

const form = useForm({
    email: email.value,
    code: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/admin/reset-password', {
        preserveScroll: true,
    });
};

const resendCode = () => {
    resending.value = true;
    form.post('/admin/forgot-password/resend', {
        data: { email: email.value },
        preserveScroll: true,
        onFinish: () => { resending.value = false; },
    });
};

const codeInputs = ref([]);

const handleCodeInput = (index, event) => {
    const value = event.target.value;
    if (value && index < 5) {
        codeInputs.value[index + 1]?.focus();
    }
    form.code = codeInputs.value.map(el => el?.value || '').join('');
};

const handleCodeKeydown = (index, event) => {
    if (event.key === 'Backspace' && !event.target.value && index > 0) {
        codeInputs.value[index - 1]?.focus();
    }
};

const handleCodePaste = (event) => {
    event.preventDefault();
    const paste = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    paste.split('').forEach((char, i) => {
        if (codeInputs.value[i]) {
            codeInputs.value[i].value = char;
        }
    });
    form.code = paste;
    const nextIndex = Math.min(paste.length, 5);
    codeInputs.value[nextIndex]?.focus();
};
</script>

<template>
    <div class="rp-page">
        <!-- Left Side - Form -->
        <div class="rp-left">
            <div class="rp-form-wrapper">
                <!-- Logo -->
                <div class="rp-logo">
                    <img src="/logo.png" alt="Mahubiri" class="rp-logo-icon" />
                    <div>
                        <h1 class="rp-logo-title">Mahubiri</h1>
                        <p class="rp-logo-sub">Administration</p>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="rp-toolbar">
                    <LocaleSwitcher />
                    <button @click="toggleTheme" class="theme-toggle" type="button">
                        <svg v-if="theme === 'dark'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                        </svg>
                    </button>
                </div>

                <!-- Status banner -->
                <div v-if="status === 'code-sent' || status === 'code-resent'" class="status-banner">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span>{{ status === 'code-resent' ? t('resetPassword.codeResent') : t('resetPassword.codeSent') }}</span>
                </div>

                <!-- Icon -->
                <div class="rp-icon-wrapper">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>

                <!-- Heading -->
                <div class="rp-heading">
                    <h2>{{ t('resetPassword.title') }}</h2>
                    <p>{{ t('resetPassword.subtitle') }} <strong>{{ email }}</strong></p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="rp-form">
                    <!-- Hidden email -->
                    <input type="hidden" :value="form.email" />

                    <!-- Verification Code -->
                    <div class="form-group">
                        <label>{{ t('resetPassword.code') }}</label>
                        <div class="code-inputs">
                            <input
                                v-for="i in 6"
                                :key="i"
                                :ref="el => { if (el) codeInputs[i - 1] = el; }"
                                type="text"
                                inputmode="numeric"
                                maxlength="1"
                                class="code-input"
                                :class="{ error: form.errors.code }"
                                @input="handleCodeInput(i - 1, $event)"
                                @keydown="handleCodeKeydown(i - 1, $event)"
                                @paste="handleCodePaste"
                            />
                        </div>
                        <p v-if="form.errors.code" class="field-error">
                            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ form.errors.code }}
                        </p>
                        <div class="resend-row">
                            <span class="resend-text">{{ t('resetPassword.noCode') }}</span>
                            <button type="button" @click="resendCode" :disabled="resending" class="resend-btn">
                                <svg v-if="resending" class="spinner-sm" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity=".25"/><path fill="currentColor" opacity=".75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                {{ resending ? t('resetPassword.resending') : t('resetPassword.resend') }}
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password">{{ t('resetPassword.newPassword') }}</label>
                        <div class="input-wrapper" :class="{ error: form.errors.password }">
                            <div class="input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="••••••••"
                                autocomplete="new-password"
                            />
                            <button type="button" @click="showPassword = !showPassword" class="toggle-password">
                                <svg v-if="!showPassword" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="field-error">
                            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation">{{ t('resetPassword.confirmPassword') }}</label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                required
                                placeholder="••••••••"
                                autocomplete="new-password"
                            />
                            <button type="button" @click="showPasswordConfirm = !showPasswordConfirm" class="toggle-password">
                                <svg v-if="!showPasswordConfirm" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="submit-btn">
                        <svg v-if="form.processing" class="spinner" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity=".25"/>
                            <path fill="currentColor" opacity=".75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span v-if="form.processing">{{ t('resetPassword.resetting') }}</span>
                        <template v-else>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>{{ t('resetPassword.submit') }}</span>
                        </template>
                    </button>
                </form>

                <!-- Back to login -->
                <div class="rp-back">
                    <a :href="'/admin/login'" class="back-login-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                        </svg>
                        {{ t('resetPassword.backToLogin') }}
                    </a>
                </div>

                <!-- Footer -->
                <div class="rp-footer">
                    <p>&copy; {{ new Date().getFullYear() }} Mahubiri</p>
                </div>
            </div>
        </div>

        <!-- Right Side -->
        <div class="rp-right">
            <div class="right-bg"></div>
            <div class="right-pattern"></div>
            <div class="right-glow right-glow-1"></div>
            <div class="right-glow right-glow-2"></div>

            <div class="right-content">
                <div class="right-badge">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    {{ t('resetPassword.secureReset') }}
                </div>

                <h2 class="right-title">
                    {{ t('resetPassword.heroTitle1') }}<br>
                    <span>{{ t('resetPassword.heroTitle2') }}</span>
                </h2>
                <p class="right-desc">{{ t('resetPassword.heroDesc') }}</p>

                <!-- Tips -->
                <div class="tips-list">
                    <div class="tip-item">
                        <div class="tip-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p>{{ t('resetPassword.tip1') }}</p>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p>{{ t('resetPassword.tip2') }}</p>
                    </div>
                    <div class="tip-item">
                        <div class="tip-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p>{{ t('resetPassword.tip3') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:root { --primary: #6B4EAF; --primary-dark: #5a3d96; --accent: #E8B77D; }

.rp-page { display: flex; height: 100vh; background: #fff; overflow: hidden; font-family: 'Instrument Sans', system-ui, sans-serif; }

/* Left */
.rp-left { width: 100%; max-width: 520px; display: flex; flex-direction: column; padding: 40px 48px; background: #fff; overflow-y: auto; z-index: 10; }
@media (min-width: 1024px) { .rp-left { width: 520px; flex-shrink: 0; } }
@media (max-width: 1023px) { .rp-left { max-width: 100%; margin: 0 auto; padding: 32px 24px; } }
.rp-form-wrapper { width: 100%; max-width: 400px; margin: auto; animation: fadeUp .6s ease both; }

/* Logo */
.rp-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 36px; }
.rp-logo-icon { width: 44px; height: 44px; border-radius: 14px; object-fit: contain; box-shadow: 0 4px 12px rgba(107,78,175,0.3); }
.rp-logo-title { font-size: 20px; font-weight: 700; color: #111827; letter-spacing: -0.3px; line-height: 1.2; }
.rp-logo-sub { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

/* Toolbar */
.rp-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.rp-toolbar :deep(.relative > div[class*="absolute"]) { right: auto; left: 0; }
.theme-toggle { display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 12px; background: #f3f4f6; border: 2px solid #e5e7eb; color: #6b7280; cursor: pointer; transition: all .25s; }
.theme-toggle:hover { background: #e5e7eb; color: #6B4EAF; border-color: #6B4EAF; }
.theme-toggle svg { width: 18px; height: 18px; }

/* Status banner */
.status-banner { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 14px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 13px; font-weight: 600; margin-bottom: 20px; }
.status-banner svg { width: 18px; height: 18px; flex-shrink: 0; color: #10b981; }

/* Icon */
.rp-icon-wrapper { width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, rgba(107,78,175,0.1), rgba(107,78,175,0.05)); display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
.rp-icon-wrapper svg { width: 28px; height: 28px; color: #6B4EAF; }

/* Heading */
.rp-heading { margin-bottom: 28px; }
.rp-heading h2 { font-size: 24px; font-weight: 800; color: #111827; letter-spacing: -0.5px; margin-bottom: 6px; }
.rp-heading p { font-size: 14px; color: #6b7280; line-height: 1.6; }
.rp-heading strong { color: #6B4EAF; font-weight: 700; }

/* Form */
.rp-form { display: flex; flex-direction: column; gap: 20px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }

/* Code inputs */
.code-inputs { display: flex; gap: 8px; }
.code-input { width: 48px; height: 52px; text-align: center; font-size: 20px; font-weight: 700; color: #111827; border: 2px solid #e5e7eb; border-radius: 14px; background: #f9fafb; outline: none; transition: all .25s; font-family: 'JetBrains Mono', monospace; }
.code-input:focus { border-color: #6B4EAF; background: #fff; box-shadow: 0 0 0 4px rgba(107,78,175,0.08); }
.code-input.error { border-color: #ef4444; background: #fef2f2; }
.field-error { display: flex; align-items: center; gap: 5px; margin-top: 6px; font-size: 13px; color: #ef4444; }
.field-error svg { width: 14px; height: 14px; flex-shrink: 0; }

/* Resend */
.resend-row { display: flex; align-items: center; gap: 6px; margin-top: 8px; }
.resend-text { font-size: 12px; color: #9ca3af; }
.resend-btn { font-size: 12px; font-weight: 600; color: #6B4EAF; background: none; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: color .2s; padding: 0; font-family: inherit; }
.resend-btn:hover { color: #5a3d96; text-decoration: underline; }
.resend-btn:disabled { opacity: .6; cursor: not-allowed; }
.spinner-sm { width: 14px; height: 14px; animation: spin .8s linear infinite; }

/* Input wrapper */
.input-wrapper { position: relative; display: flex; align-items: center; border: 2px solid #e5e7eb; border-radius: 14px; background: #f9fafb; transition: all .25s; overflow: hidden; }
.input-wrapper:focus-within { border-color: #6B4EAF; background: #fff; box-shadow: 0 0 0 4px rgba(107,78,175,0.08); }
.input-wrapper.error { border-color: #ef4444; background: #fef2f2; }
.input-wrapper.error:focus-within { box-shadow: 0 0 0 4px rgba(239,68,68,0.08); }
.input-icon { display: flex; align-items: center; justify-content: center; width: 48px; flex-shrink: 0; color: #9ca3af; transition: color .25s; }
.input-wrapper:focus-within .input-icon { color: #6B4EAF; }
.input-icon svg { width: 18px; height: 18px; }
.input-wrapper input { flex: 1; padding: 13px 16px 13px 0; font-size: 14px; color: #111827; background: transparent; border: none; outline: none; font-family: inherit; }
.input-wrapper input::placeholder { color: #9ca3af; }
.toggle-password { display: flex; align-items: center; justify-content: center; width: 48px; flex-shrink: 0; cursor: pointer; background: none; border: none; color: #9ca3af; transition: color .2s; padding: 0; }
.toggle-password:hover { color: #6b7280; }
.toggle-password svg { width: 18px; height: 18px; }

/* Submit */
.submit-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 14px 24px; background: linear-gradient(135deg, #6B4EAF 0%, #5a3d96 100%); color: #fff; font-size: 14px; font-weight: 700; border: none; border-radius: 14px; cursor: pointer; transition: all .3s; box-shadow: 0 4px 16px rgba(107,78,175,0.3); font-family: inherit; line-height: 1; }
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(107,78,175,0.35); }
.submit-btn:active { transform: translateY(0); }
.submit-btn:disabled { opacity: .6; cursor: not-allowed; transform: none !important; }
.submit-btn svg { width: 18px; height: 18px; }
.spinner { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Back */
.rp-back { display: flex; justify-content: center; margin-top: 28px; }
.back-login-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #6B4EAF; text-decoration: none; transition: color .2s; }
.back-login-link:hover { color: #5a3d96; text-decoration: underline; }
.back-login-link svg { width: 16px; height: 16px; }

/* Footer */
.rp-footer { display: flex; justify-content: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f3f4f6; }
.rp-footer p { font-size: 11px; color: #9ca3af; }

/* ── Right Panel ── */
.rp-right { display: none; flex: 1; position: relative; overflow: hidden; }
@media (min-width: 1024px) { .rp-right { display: flex; } }
.right-bg { position: absolute; inset: 0; background: linear-gradient(135deg, #6B4EAF 0%, #4a2d8a 40%, #3a2070 100%); }
.right-pattern { position: absolute; inset: 0; opacity: .03; background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 28px 28px; }
.right-glow { position: absolute; border-radius: 50%; filter: blur(100px); }
.right-glow-1 { width: 500px; height: 500px; background: rgba(232,183,125,0.2); top: -15%; right: -10%; animation: floatGlow 15s ease-in-out infinite alternate; }
.right-glow-2 { width: 400px; height: 400px; background: rgba(156,125,199,0.25); bottom: -10%; left: -5%; animation: floatGlow 18s ease-in-out infinite alternate-reverse; }
@keyframes floatGlow { 0% { transform: translate(0,0) scale(1); } 50% { transform: translate(20px,-30px) scale(1.1); } 100% { transform: translate(-10px,15px) scale(.95); } }
.right-content { position: relative; z-index: 10; display: flex; flex-direction: column; justify-content: center; padding: 48px 52px; width: 100%; animation: fadeUp .8s ease .2s both; }
.right-badge { display: inline-flex; align-items: center; gap: 8px; align-self: flex-start; padding: 7px 16px; border-radius: 999px; background: rgba(255,255,255,0.1); backdrop-filter: blur(12px); color: rgba(255,255,255,0.9); font-size: 12px; font-weight: 600; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 28px; }
.right-title { font-size: clamp(28px,3vw,38px); font-weight: 800; color: #fff; line-height: 1.15; letter-spacing: -0.5px; margin-bottom: 14px; }
.right-title span { color: #E8B77D; }
.right-desc { font-size: 15px; color: rgba(255,255,255,0.6); line-height: 1.65; max-width: 440px; margin-bottom: 32px; }

/* Tips */
.tips-list { display: flex; flex-direction: column; gap: 12px; }
.tip-item { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 14px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); }
.tip-icon { width: 24px; height: 24px; flex-shrink: 0; }
.tip-icon svg { width: 20px; height: 20px; color: #34d399; }
.tip-item p { font-size: 13px; color: rgba(255,255,255,0.7); line-height: 1.4; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ── Dark Mode ── */
:root.dark .rp-page { background: #111827; }
:root.dark .rp-left { background: #111827; }
:root.dark .rp-logo-title { color: #f3f4f6; }
:root.dark .rp-logo-sub { color: #6b7280; }
:root.dark .status-banner { background: #064e3b; border-color: #065f46; color: #a7f3d0; }
:root.dark .status-banner svg { color: #34d399; }
:root.dark .rp-icon-wrapper { background: linear-gradient(135deg, rgba(139,111,207,0.2), rgba(139,111,207,0.1)); }
:root.dark .rp-icon-wrapper svg { color: #a78bfa; }
:root.dark .rp-heading h2 { color: #f3f4f6; }
:root.dark .rp-heading p { color: #9ca3af; }
:root.dark .rp-heading strong { color: #a78bfa; }
:root.dark .form-group label { color: #d1d5db; }
:root.dark .code-input { border-color: #374151; background: #1f2937; color: #f3f4f6; }
:root.dark .code-input:focus { border-color: #8b6fcf; background: #1a1a2e; box-shadow: 0 0 0 4px rgba(139,111,207,0.15); }
:root.dark .code-input.error { border-color: #dc2626; background: #1c1017; }
:root.dark .resend-text { color: #6b7280; }
:root.dark .resend-btn { color: #a78bfa; }
:root.dark .resend-btn:hover { color: #c4b5fd; }
:root.dark .input-wrapper { border-color: #374151; background: #1f2937; }
:root.dark .input-wrapper:focus-within { border-color: #8b6fcf; background: #1a1a2e; box-shadow: 0 0 0 4px rgba(139,111,207,0.15); }
:root.dark .input-wrapper.error { border-color: #dc2626; background: #1c1017; }
:root.dark .input-icon { color: #6b7280; }
:root.dark .input-wrapper:focus-within .input-icon { color: #a78bfa; }
:root.dark .input-wrapper input { color: #f3f4f6; }
:root.dark .input-wrapper input::placeholder { color: #6b7280; }
:root.dark .toggle-password { color: #6b7280; }
:root.dark .toggle-password:hover { color: #9ca3af; }
:root.dark .submit-btn { background: linear-gradient(135deg, #7c5fc4 0%, #6B4EAF 100%); box-shadow: 0 4px 16px rgba(124,95,196,0.35); }
:root.dark .submit-btn:hover { box-shadow: 0 8px 24px rgba(124,95,196,0.4); }
:root.dark .back-login-link { color: #a78bfa; }
:root.dark .back-login-link:hover { color: #c4b5fd; }
:root.dark .rp-footer { border-top-color: #1f2937; }
:root.dark .rp-footer p { color: #6b7280; }
:root.dark .theme-toggle { background: #1f2937; border-color: #374151; color: #9ca3af; }
:root.dark .theme-toggle:hover { background: #374151; color: #e8b77d; border-color: #e8b77d; }
:root.dark .right-bg { background: linear-gradient(135deg, #4a2d8a 0%, #2d1a5e 40%, #1a1035 100%); }
</style>
