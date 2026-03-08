<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';

const { t } = useI18n();
const { theme, toggleTheme } = useTheme();
const page = usePage();
const stats = computed(() => page.props.stats || {});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    });
};

const features = computed(() => [
    { icon: 'sermons', title: t('login.featureSermons'), desc: t('login.featureSermonsDesc') },
    { icon: 'users', title: t('login.featureCommunity'), desc: t('login.featureCommunityDesc') },
    { icon: 'analytics', title: t('login.featureAnalytics'), desc: t('login.featureAnalyticsDesc') },
    { icon: 'notifications', title: t('login.featureNotifications'), desc: t('login.featureNotificationsDesc') },
]);
</script>

<template>
    <div class="login-page">
        <!-- Left Side - Login Form -->
        <div class="login-left">
            <div class="login-form-wrapper">
                <!-- Logo -->
                <div class="login-logo">
                    <img src="/logo.png" alt="Mahubiri" class="login-logo-icon" />
                    <div>
                        <h1 class="login-logo-title">Mahubiri</h1>
                        <p class="login-logo-sub">Administration</p>
                    </div>
                </div>

                <!-- Locale Switcher & Theme Toggle -->
                <div class="login-toolbar">
                    <LocaleSwitcher />
                    <button @click="toggleTheme" class="theme-toggle" type="button">
                        <!-- Sun icon (shown in dark mode) -->
                        <svg v-if="theme === 'dark'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <!-- Moon icon (shown in light mode) -->
                        <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                        </svg>
                    </button>
                </div>

                <!-- Heading -->
                <div class="login-heading">
                    <h2>{{ t('login.welcomeBack') }}</h2>
                    <p>{{ t('login.subtitle') }}</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="login-form">
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">{{ t('login.email') }}</label>
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
                                placeholder="admin@mahubiri.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="field-error">
                            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">{{ t('login.password') }}</label>
                        <div class="input-wrapper" :class="{ error: form.errors.password }">
                            <div class="input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                            </div>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="toggle-password"
                            >
                                <svg v-if="!showPassword" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="field-error">
                            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember me -->
                    <div class="form-options">
                        <label class="remember-label">
                            <input v-model="form.remember" type="checkbox" />
                            <span class="custom-check">
                                <svg viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span>{{ t('login.rememberMe') }}</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="form.processing" class="submit-btn">
                        <svg v-if="form.processing" class="spinner" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity=".25"/>
                            <path fill="currentColor" opacity=".75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span v-if="form.processing">{{ t('login.submitting') }}</span>
                        <template v-else>
                            <span>{{ t('login.submit') }}</span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </template>
                    </button>
                </form>

                <!-- Back to home -->
                <div class="login-footer">
                    <a href="/" class="back-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        {{ t('login.backHome') }}
                    </a>
                    <p>&copy; {{ new Date().getFullYear() }} Mahubiri</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Hero -->
        <div class="login-right">
            <!-- Background layers -->
            <div class="right-bg"></div>
            <div class="right-pattern"></div>
            <div class="right-glow right-glow-1"></div>
            <div class="right-glow right-glow-2"></div>

            <!-- Content -->
            <div class="right-content">
                <!-- Badge -->
                <div class="right-badge">
                    <span class="pulse-dot"></span>
                    {{ t('login.platformActive') }}
                </div>

                <h2 class="right-title">
                    {{ t('login.heroTitle1') }}<br>
                    <span>{{ t('login.heroTitle2') }}</span><br>
                    {{ t('login.heroTitle3') }}
                </h2>
                <p class="right-desc">
                    {{ t('login.heroDesc') }}
                </p>

                <!-- Feature cards -->
                <div class="features-grid">
                    <div v-for="(feature, i) in features" :key="i" class="feature-card">
                        <div class="feature-icon">
                            <svg v-if="feature.icon === 'sermons'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                            <svg v-if="feature.icon === 'users'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                            </svg>
                            <svg v-if="feature.icon === 'analytics'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                            </svg>
                            <svg v-if="feature.icon === 'notifications'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>
                            </svg>
                        </div>
                        <div>
                            <h4>{{ feature.title }}</h4>
                            <p>{{ feature.desc }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="right-stats">
                    <div class="right-stat">
                        <span class="right-stat-val">{{ stats.sermons?.toLocaleString() ?? '—' }}</span>
                        <span class="right-stat-label">{{ t('login.statSermons') }}</span>
                    </div>
                    <div class="right-stat-divider"></div>
                    <div class="right-stat">
                        <span class="right-stat-val">{{ stats.churches?.toLocaleString() ?? '—' }}</span>
                        <span class="right-stat-label">{{ t('login.statChurches') }}</span>
                    </div>
                    <div class="right-stat-divider"></div>
                    <div class="right-stat">
                        <span class="right-stat-val">{{ stats.users?.toLocaleString() ?? '—' }}</span>
                        <span class="right-stat-label">{{ t('login.statUsers') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── Variables ── */
:root {
    --primary: #6B4EAF;
    --primary-dark: #5a3d96;
    --accent: #E8B77D;
    --danger: #ef4444;
}

/* ── Page layout ── */
.login-page {
    display: flex;
    height: 100vh;
    background: #ffffff;
    overflow: hidden;
    font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
}

/* ── Left Panel ── */
.login-left {
    width: 100%;
    max-width: 520px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 40px 48px;
    position: relative;
    z-index: 10;
    background: #ffffff;
}
@media (min-width: 1024px) {
    .login-left { width: 520px; flex-shrink: 0; }
}
@media (max-width: 1023px) {
    .login-left { max-width: 100%; margin: 0 auto; padding: 32px 24px; }
}

.login-form-wrapper {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    animation: fadeUp .6s ease both;
}

/* Logo */
.login-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 36px;
}
.login-logo-icon {
    width: 44px; height: 44px;
    border-radius: 14px;
    object-fit: contain;
    box-shadow: 0 4px 12px rgba(107, 78, 175, 0.3);
}
.login-logo-title {
    font-size: 20px; font-weight: 700; color: #111827;
    letter-spacing: -0.3px; line-height: 1.2;
}
.login-logo-sub {
    font-size: 10px; color: #9ca3af;
    text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600;
}

/* Heading */
.login-heading {
    margin-bottom: 32px;
}
.login-heading h2 {
    font-size: 26px; font-weight: 800; color: #111827;
    letter-spacing: -0.5px; margin-bottom: 6px;
}
.login-heading p {
    font-size: 14px; color: #6b7280; line-height: 1.5;
}

/* ── Form ── */
.login-form {
    display: flex; flex-direction: column; gap: 20px;
}
.form-group label {
    display: block; font-size: 13px; font-weight: 600; color: #374151;
    margin-bottom: 6px;
}
.input-wrapper {
    position: relative; display: flex; align-items: center;
    border: 2px solid #e5e7eb; border-radius: 14px;
    background: #f9fafb; transition: all .25s cubic-bezier(.4,0,.2,1);
    overflow: hidden;
}
.input-wrapper:focus-within {
    border-color: #6B4EAF; background: #fff;
    box-shadow: 0 0 0 4px rgba(107, 78, 175, 0.08);
}
.input-wrapper.error {
    border-color: #ef4444; background: #fef2f2;
}
.input-wrapper.error:focus-within {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
}
.input-icon {
    display: flex; align-items: center; justify-content: center;
    width: 48px; flex-shrink: 0; color: #9ca3af;
    transition: color .25s;
}
.input-wrapper:focus-within .input-icon { color: #6B4EAF; }
.input-icon svg { width: 18px; height: 18px; }
.input-wrapper input {
    flex: 1; padding: 13px 16px 13px 0;
    font-size: 14px; color: #111827; background: transparent;
    border: none; outline: none; font-family: inherit;
}
.input-wrapper input::placeholder { color: #9ca3af; }

.toggle-password {
    display: flex; align-items: center; justify-content: center;
    width: 48px; flex-shrink: 0; cursor: pointer;
    background: none; border: none; color: #9ca3af;
    transition: color .2s; padding: 0;
}
.toggle-password:hover { color: #6b7280; }
.toggle-password svg { width: 18px; height: 18px; }

.field-error {
    display: flex; align-items: center; gap: 5px;
    margin-top: 6px; font-size: 13px; color: #ef4444;
}
.field-error svg { width: 14px; height: 14px; flex-shrink: 0; }

/* ── Remember ── */
.form-options {
    display: flex; align-items: center;
}
.remember-label {
    display: flex; align-items: center; gap: 10px;
    cursor: pointer; font-size: 13px; color: #6b7280;
    user-select: none;
}
.remember-label input {
    position: absolute; opacity: 0; width: 0; height: 0;
}
.custom-check {
    width: 20px; height: 20px; border-radius: 6px;
    border: 2px solid #d1d5db; display: flex; align-items: center; justify-content: center;
    transition: all .2s; flex-shrink: 0;
}
.custom-check svg { width: 12px; height: 12px; opacity: 0; transition: opacity .15s; }
.remember-label input:checked ~ .custom-check {
    background: #6B4EAF; border-color: #6B4EAF;
}
.remember-label input:checked ~ .custom-check svg { opacity: 1; }
.remember-label:hover .custom-check { border-color: #6B4EAF; }

/* ── Submit ── */
.submit-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    width: 100%; padding: 14px 24px;
    background: linear-gradient(135deg, #6B4EAF 0%, #5a3d96 100%);
    color: #fff; font-size: 14px; font-weight: 700;
    border: none; border-radius: 14px; cursor: pointer;
    transition: all .3s cubic-bezier(.4,0,.2,1);
    box-shadow: 0 4px 16px rgba(107, 78, 175, 0.3);
    font-family: inherit; line-height: 1;
}
.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(107, 78, 175, 0.35);
}
.submit-btn:active { transform: translateY(0); }
.submit-btn:disabled {
    opacity: .6; cursor: not-allowed; transform: none !important;
}
.submit-btn svg { width: 18px; height: 18px; }
.spinner { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Footer ── */
.login-footer {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 32px; padding-top: 20px;
    border-top: 1px solid #f3f4f6;
}
.login-footer p { font-size: 11px; color: #9ca3af; }
.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600; color: #6b7280;
    text-decoration: none; transition: color .2s;
}
.back-link:hover { color: #6B4EAF; }
.back-link svg { width: 14px; height: 14px; }

/* ══════════════════════════════
   Right Panel
   ══════════════════════════════ */
.login-right {
    display: none; flex: 1; position: relative; overflow: hidden;
}
@media (min-width: 1024px) { .login-right { display: flex; } }

.right-bg {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, #6B4EAF 0%, #4a2d8a 40%, #3a2070 100%);
}
.right-pattern {
    position: absolute; inset: 0; opacity: .03;
    background-image: radial-gradient(circle, #fff 1px, transparent 1px);
    background-size: 28px 28px;
}
.right-glow {
    position: absolute; border-radius: 50%; filter: blur(100px);
}
.right-glow-1 {
    width: 500px; height: 500px;
    background: rgba(232, 183, 125, 0.2);
    top: -15%; right: -10%;
    animation: floatGlow 15s ease-in-out infinite alternate;
}
.right-glow-2 {
    width: 400px; height: 400px;
    background: rgba(156, 125, 199, 0.25);
    bottom: -10%; left: -5%;
    animation: floatGlow 18s ease-in-out infinite alternate-reverse;
}
@keyframes floatGlow {
    0%   { transform: translate(0, 0) scale(1); }
    50%  { transform: translate(20px, -30px) scale(1.1); }
    100% { transform: translate(-10px, 15px) scale(0.95); }
}

.right-content {
    position: relative; z-index: 10;
    display: flex; flex-direction: column; justify-content: center;
    padding: 48px 52px;
    width: 100%;
    animation: fadeUp .8s ease .2s both;
}

/* Badge */
.right-badge {
    display: inline-flex; align-items: center; gap: 8px; align-self: flex-start;
    padding: 7px 16px; border-radius: 999px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
    color: rgba(255, 255, 255, 0.9); font-size: 12px; font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 28px;
}
.pulse-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #34d399;
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: .5; transform: scale(0.8); }
}

/* Title */
.right-title {
    font-size: clamp(28px, 3vw, 38px); font-weight: 800;
    color: #fff; line-height: 1.15; letter-spacing: -0.5px;
    margin-bottom: 14px;
}
.right-title span { color: #E8B77D; }

.right-desc {
    font-size: 15px; color: rgba(255, 255, 255, 0.6);
    line-height: 1.65; max-width: 440px; margin-bottom: 32px;
}

/* Features */
.features-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
}
.feature-card {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 14px 16px; border-radius: 16px;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: all .3s;
}
.feature-card:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}
.feature-icon {
    width: 36px; height: 36px; border-radius: 10px;
    background: rgba(255, 255, 255, 0.12);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.feature-icon svg { width: 18px; height: 18px; color: #E8B77D; }
.feature-card h4 {
    font-size: 13px; font-weight: 700; color: #fff;
    margin-bottom: 2px;
}
.feature-card p {
    font-size: 11px; color: rgba(255, 255, 255, 0.45); line-height: 1.5;
}

/* Stats */
.right-stats {
    display: flex; align-items: center; gap: 28px;
    margin-top: 32px; padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}
.right-stat-val {
    display: block; font-size: 24px; font-weight: 800; color: #fff;
}
.right-stat-label {
    display: block; font-size: 11px; color: rgba(255, 255, 255, 0.45);
    margin-top: 2px;
}
.right-stat-divider {
    width: 1px; height: 36px;
    background: rgba(255, 255, 255, 0.1);
}

/* ── Animation ── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ══════════════════════════════
   Toolbar (Locale + Theme)
   ══════════════════════════════ */
.login-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
}
.login-toolbar :deep(.relative > div[class*="absolute"]) {
    right: auto;
    left: 0;
}
.theme-toggle {
    display: flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 12px;
    background: #f3f4f6; border: 2px solid #e5e7eb;
    color: #6b7280; cursor: pointer;
    transition: all .25s cubic-bezier(.4,0,.2,1);
}
.theme-toggle:hover {
    background: #e5e7eb; color: #6B4EAF;
    border-color: #6B4EAF;
}
.theme-toggle svg { width: 18px; height: 18px; }

/* ══════════════════════════════
   Dark Mode Overrides
   ══════════════════════════════ */
:root.dark .login-page { background: #111827; }
:root.dark .login-left { background: #111827; }

:root.dark .login-logo-title { color: #f3f4f6; }
:root.dark .login-logo-sub { color: #6b7280; }

:root.dark .login-heading h2 { color: #f3f4f6; }
:root.dark .login-heading p { color: #9ca3af; }

:root.dark .form-group label { color: #d1d5db; }

:root.dark .input-wrapper {
    border-color: #374151; background: #1f2937;
}
:root.dark .input-wrapper:focus-within {
    border-color: #8b6fcf; background: #1a1a2e;
    box-shadow: 0 0 0 4px rgba(139, 111, 207, 0.15);
}
:root.dark .input-wrapper.error {
    border-color: #dc2626; background: #1c1017;
}
:root.dark .input-wrapper.error:focus-within {
    box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15);
}
:root.dark .input-icon { color: #6b7280; }
:root.dark .input-wrapper:focus-within .input-icon { color: #a78bfa; }
:root.dark .input-wrapper input { color: #f3f4f6; }
:root.dark .input-wrapper input::placeholder { color: #6b7280; }

:root.dark .toggle-password { color: #6b7280; }
:root.dark .toggle-password:hover { color: #9ca3af; }

:root.dark .remember-label { color: #9ca3af; }
:root.dark .custom-check { border-color: #4b5563; }
:root.dark .remember-label:hover .custom-check { border-color: #8b6fcf; }

:root.dark .submit-btn {
    background: linear-gradient(135deg, #7c5fc4 0%, #6B4EAF 100%);
    box-shadow: 0 4px 16px rgba(124, 95, 196, 0.35);
}
:root.dark .submit-btn:hover {
    box-shadow: 0 8px 24px rgba(124, 95, 196, 0.4);
}

:root.dark .login-footer { border-top-color: #1f2937; }
:root.dark .login-footer p { color: #6b7280; }
:root.dark .back-link { color: #9ca3af; }
:root.dark .back-link:hover { color: #a78bfa; }

:root.dark .theme-toggle {
    background: #1f2937; border-color: #374151; color: #9ca3af;
}
:root.dark .theme-toggle:hover {
    background: #374151; color: #e8b77d; border-color: #e8b77d;
}

:root.dark .right-bg {
    background: linear-gradient(135deg, #4a2d8a 0%, #2d1a5e 40%, #1a1035 100%);
}
</style>
