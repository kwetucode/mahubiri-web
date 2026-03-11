<script setup>
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';

const { t } = useI18n();
const { theme, toggleTheme } = useTheme();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/admin/forgot-password', {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="fp-page">
        <!-- Left Side - Form -->
        <div class="fp-left">
            <div class="fp-form-wrapper">
                <!-- Logo -->
                <div class="fp-logo">
                    <img src="/logo.png" alt="Mahubiri" class="fp-logo-icon" />
                    <div>
                        <h1 class="fp-logo-title">Mahubiri</h1>
                        <p class="fp-logo-sub">Administration</p>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="fp-toolbar">
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

                <!-- Icon -->
                <div class="fp-icon-wrapper">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </div>

                <!-- Heading -->
                <div class="fp-heading">
                    <h2>{{ t('forgotPassword.title') }}</h2>
                    <p>{{ t('forgotPassword.subtitle') }}</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="fp-form">
                    <div class="form-group">
                        <label for="email">{{ t('forgotPassword.email') }}</label>
                        <div class="input-wrapper" :class="{ error: form.errors.email }">
                            <div class="input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                autofocus
                                :placeholder="t('forgotPassword.emailPlaceholder')"
                            />
                        </div>
                        <p v-if="form.errors.email" class="field-error">
                            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <button type="submit" :disabled="form.processing" class="submit-btn">
                        <svg v-if="form.processing" class="spinner" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity=".25"/>
                            <path fill="currentColor" opacity=".75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span v-if="form.processing">{{ t('forgotPassword.sending') }}</span>
                        <template v-else>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            <span>{{ t('forgotPassword.sendCode') }}</span>
                        </template>
                    </button>
                </form>

                <!-- Back to login -->
                <div class="fp-back">
                    <a :href="'/admin/login'" class="back-login-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        {{ t('forgotPassword.backToLogin') }}
                    </a>
                </div>

                <!-- Footer -->
                <div class="fp-footer">
                    <p>&copy; {{ new Date().getFullYear() }} Mahubiri</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Visual -->
        <div class="fp-right">
            <div class="right-bg"></div>
            <div class="right-pattern"></div>
            <div class="right-glow right-glow-1"></div>
            <div class="right-glow right-glow-2"></div>

            <div class="right-content">
                <div class="right-badge">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                        <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    {{ t('forgotPassword.secureProcess') }}
                </div>

                <h2 class="right-title">
                    {{ t('forgotPassword.heroTitle1') }}<br>
                    <span>{{ t('forgotPassword.heroTitle2') }}</span>
                </h2>
                <p class="right-desc">{{ t('forgotPassword.heroDesc') }}</p>

                <!-- Steps -->
                <div class="steps-list">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div>
                            <h4>{{ t('forgotPassword.step1Title') }}</h4>
                            <p>{{ t('forgotPassword.step1Desc') }}</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div>
                            <h4>{{ t('forgotPassword.step2Title') }}</h4>
                            <p>{{ t('forgotPassword.step2Desc') }}</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div>
                            <h4>{{ t('forgotPassword.step3Title') }}</h4>
                            <p>{{ t('forgotPassword.step3Desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:root { --primary: #6B4EAF; --primary-dark: #5a3d96; --accent: #E8B77D; }

.fp-page { display: flex; height: 100vh; background: #fff; overflow: hidden; font-family: 'Instrument Sans', system-ui, sans-serif; }

/* Left */
.fp-left { width: 100%; max-width: 520px; display: flex; flex-direction: column; padding: 40px 48px; background: #fff; overflow-y: auto; z-index: 10; }
@media (min-width: 1024px) { .fp-left { width: 520px; flex-shrink: 0; } }
@media (max-width: 1023px) { .fp-left { max-width: 100%; margin: 0 auto; padding: 32px 24px; } }
.fp-form-wrapper { width: 100%; max-width: 400px; margin: auto; animation: fadeUp .6s ease both; }

/* Logo */
.fp-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 36px; }
.fp-logo-icon { width: 44px; height: 44px; border-radius: 14px; object-fit: contain; box-shadow: 0 4px 12px rgba(107,78,175,0.3); }
.fp-logo-title { font-size: 20px; font-weight: 700; color: #111827; letter-spacing: -0.3px; line-height: 1.2; }
.fp-logo-sub { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

/* Toolbar */
.fp-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.fp-toolbar :deep(.relative > div[class*="absolute"]) { right: auto; left: 0; }
.theme-toggle { display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 12px; background: #f3f4f6; border: 2px solid #e5e7eb; color: #6b7280; cursor: pointer; transition: all .25s; }
.theme-toggle:hover { background: #e5e7eb; color: #6B4EAF; border-color: #6B4EAF; }
.theme-toggle svg { width: 18px; height: 18px; }

/* Icon */
.fp-icon-wrapper { width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, rgba(107,78,175,0.1), rgba(107,78,175,0.05)); display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
.fp-icon-wrapper svg { width: 28px; height: 28px; color: #6B4EAF; }

/* Heading */
.fp-heading { margin-bottom: 32px; }
.fp-heading h2 { font-size: 26px; font-weight: 800; color: #111827; letter-spacing: -0.5px; margin-bottom: 6px; }
.fp-heading p { font-size: 14px; color: #6b7280; line-height: 1.6; }

/* Form */
.fp-form { display: flex; flex-direction: column; gap: 20px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.input-wrapper { position: relative; display: flex; align-items: center; border: 2px solid #e5e7eb; border-radius: 14px; background: #f9fafb; transition: all .25s; overflow: hidden; }
.input-wrapper:focus-within { border-color: #6B4EAF; background: #fff; box-shadow: 0 0 0 4px rgba(107,78,175,0.08); }
.input-wrapper.error { border-color: #ef4444; background: #fef2f2; }
.input-wrapper.error:focus-within { box-shadow: 0 0 0 4px rgba(239,68,68,0.08); }
.input-icon { display: flex; align-items: center; justify-content: center; width: 48px; flex-shrink: 0; color: #9ca3af; transition: color .25s; }
.input-wrapper:focus-within .input-icon { color: #6B4EAF; }
.input-icon svg { width: 18px; height: 18px; }
.input-wrapper input { flex: 1; padding: 13px 16px 13px 0; font-size: 14px; color: #111827; background: transparent; border: none; outline: none; font-family: inherit; }
.input-wrapper input::placeholder { color: #9ca3af; }
.field-error { display: flex; align-items: center; gap: 5px; margin-top: 6px; font-size: 13px; color: #ef4444; }
.field-error svg { width: 14px; height: 14px; flex-shrink: 0; }

/* Submit */
.submit-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 14px 24px; background: linear-gradient(135deg, #6B4EAF 0%, #5a3d96 100%); color: #fff; font-size: 14px; font-weight: 700; border: none; border-radius: 14px; cursor: pointer; transition: all .3s; box-shadow: 0 4px 16px rgba(107,78,175,0.3); font-family: inherit; line-height: 1; }
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(107,78,175,0.35); }
.submit-btn:active { transform: translateY(0); }
.submit-btn:disabled { opacity: .6; cursor: not-allowed; transform: none !important; }
.submit-btn svg { width: 18px; height: 18px; }
.spinner { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Back link */
.fp-back { display: flex; justify-content: center; margin-top: 28px; }
.back-login-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #6B4EAF; text-decoration: none; transition: color .2s; }
.back-login-link:hover { color: #5a3d96; text-decoration: underline; }
.back-login-link svg { width: 16px; height: 16px; }

/* Footer */
.fp-footer { display: flex; justify-content: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #f3f4f6; }
.fp-footer p { font-size: 11px; color: #9ca3af; }

/* ── Right Panel ── */
.fp-right { display: none; flex: 1; position: relative; overflow: hidden; }
@media (min-width: 1024px) { .fp-right { display: flex; } }

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

/* Steps */
.steps-list { display: flex; flex-direction: column; gap: 16px; }
.step-item { display: flex; align-items: flex-start; gap: 14px; padding: 16px 18px; border-radius: 16px; background: rgba(255,255,255,0.06); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.08); transition: all .3s; }
.step-item:hover { background: rgba(255,255,255,0.1); transform: translateX(4px); }
.step-number { width: 32px; height: 32px; border-radius: 10px; background: rgba(232,183,125,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; font-weight: 800; color: #E8B77D; }
.step-item h4 { font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 2px; }
.step-item p { font-size: 11px; color: rgba(255,255,255,0.45); line-height: 1.5; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ── Dark Mode ── */
:root.dark .fp-page { background: #111827; }
:root.dark .fp-left { background: #111827; }
:root.dark .fp-logo-title { color: #f3f4f6; }
:root.dark .fp-logo-sub { color: #6b7280; }
:root.dark .fp-icon-wrapper { background: linear-gradient(135deg, rgba(139,111,207,0.2), rgba(139,111,207,0.1)); }
:root.dark .fp-icon-wrapper svg { color: #a78bfa; }
:root.dark .fp-heading h2 { color: #f3f4f6; }
:root.dark .fp-heading p { color: #9ca3af; }
:root.dark .form-group label { color: #d1d5db; }
:root.dark .input-wrapper { border-color: #374151; background: #1f2937; }
:root.dark .input-wrapper:focus-within { border-color: #8b6fcf; background: #1a1a2e; box-shadow: 0 0 0 4px rgba(139,111,207,0.15); }
:root.dark .input-wrapper.error { border-color: #dc2626; background: #1c1017; }
:root.dark .input-wrapper.error:focus-within { box-shadow: 0 0 0 4px rgba(220,38,38,0.15); }
:root.dark .input-icon { color: #6b7280; }
:root.dark .input-wrapper:focus-within .input-icon { color: #a78bfa; }
:root.dark .input-wrapper input { color: #f3f4f6; }
:root.dark .input-wrapper input::placeholder { color: #6b7280; }
:root.dark .submit-btn { background: linear-gradient(135deg, #7c5fc4 0%, #6B4EAF 100%); box-shadow: 0 4px 16px rgba(124,95,196,0.35); }
:root.dark .submit-btn:hover { box-shadow: 0 8px 24px rgba(124,95,196,0.4); }
:root.dark .back-login-link { color: #a78bfa; }
:root.dark .back-login-link:hover { color: #c4b5fd; }
:root.dark .fp-footer { border-top-color: #1f2937; }
:root.dark .fp-footer p { color: #6b7280; }
:root.dark .theme-toggle { background: #1f2937; border-color: #374151; color: #9ca3af; }
:root.dark .theme-toggle:hover { background: #374151; color: #e8b77d; border-color: #e8b77d; }
:root.dark .right-bg { background: linear-gradient(135deg, #4a2d8a 0%, #2d1a5e 40%, #1a1035 100%); }
</style>
