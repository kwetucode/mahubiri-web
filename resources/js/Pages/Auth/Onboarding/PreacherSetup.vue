<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import countries from 'i18n-iso-countries';
import countriesFr from 'i18n-iso-countries/langs/fr.json';
import countriesEn from 'i18n-iso-countries/langs/en.json';

countries.registerLocale(countriesFr);
countries.registerLocale(countriesEn);

const { t, locale } = useI18n();
const { theme, toggleTheme } = useTheme();
const page = usePage();

const defaultCountryCode = 'CD';
const getCountryName = (code) => {
    const lang = ['fr', 'en'].includes(locale.value) ? locale.value : 'fr';
    return countries.getName(code, lang, { select: 'official' }) || '';
};

const ministryTypes = computed(() => page.props.ministryTypes || {});

const avatarPreview = ref(null);
const avatarInput = ref(null);

const onAvatarSelected = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.avatar = file;
    const reader = new FileReader();
    reader.onload = (ev) => { avatarPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const removeAvatar = () => {
    form.avatar = null;
    avatarPreview.value = null;
    if (avatarInput.value) avatarInput.value.value = '';
};

const countryList = computed(() => {
    const lang = ['fr', 'en'].includes(locale.value) ? locale.value : 'fr';
    const names = countries.getNames(lang, { select: 'official' });
    return Object.entries(names)
        .map(([code, name]) => ({ code, name }))
        .sort((a, b) => a.name.localeCompare(b.name, lang));
});

const onCountryChange = (e) => {
    const code = e.target.value;
    if (!code) { form.country_name = ''; form.country_code = ''; return; }
    form.country_code = code;
    form.country_name = getCountryName(code);
};

const form = useForm({
    ministry_name: '',
    ministry_type: '',
    country_name: getCountryName(defaultCountryCode),
    country_code: defaultCountryCode,
    city: '',
    avatar: null,
});

const submit = () => {
    form.post('/admin/onboarding/preacher', {
        forceFormData: true,
    });
};

const logout = () => {
    router.post('/admin/logout');
};
</script>

<template>
    <div class="setup-page">
        <!-- Left Side - Form -->
        <div class="setup-left">
            <div class="setup-form-wrapper">
                <!-- Logo -->
                <div class="setup-logo">
                    <img src="/logo.png" alt="Mahubiri" class="setup-logo-icon" />
                    <div>
                        <h1 class="setup-logo-title">Mahubiri</h1>
                        <p class="setup-logo-sub">{{ t('onboarding.preacherSetupSubtitle') }}</p>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="setup-toolbar">
                    <LocaleSwitcher />
                    <button @click="toggleTheme" class="theme-toggle" type="button">
                        <svg v-if="theme === 'dark'" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg v-else width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                        </svg>
                    </button>
                </div>

                <!-- Heading -->
                <div class="setup-heading">
                    <h2>{{ t('onboarding.preacherTitle') }}</h2>
                    <p>{{ t('onboarding.preacherDesc') }}</p>
                </div>

                <!-- Step indicator -->
                <div class="step-indicator">
                    <div class="step completed">
                        <div class="step-dot">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <span>{{ t('register.step1') }}</span>
                    </div>
                    <div class="step-line completed-line"></div>
                    <div class="step completed">
                        <div class="step-dot">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <span>{{ t('verification.stepLabel') }}</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step active">
                        <div class="step-dot">3</div>
                        <span>{{ t('register.step2') }}</span>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="preacher-form">
                    <!-- Avatar upload -->
                    <div class="form-group">
                        <label>{{ t('onboarding.avatar') }}</label>
                        <div class="avatar-upload-zone" @click="avatarInput?.click()">
                            <input ref="avatarInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden-input" @change="onAvatarSelected" />
                            <div v-if="avatarPreview" class="avatar-preview">
                                <img :src="avatarPreview" alt="Avatar preview" />
                                <button type="button" class="avatar-remove" @click.stop="removeAvatar">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                            <div v-else class="avatar-placeholder">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span>{{ t('onboarding.avatarUpload') }}</span>
                                <span class="avatar-hint">{{ t('onboarding.avatarHint') }}</span>
                            </div>
                        </div>
                        <p v-if="form.errors.avatar" class="field-error">{{ form.errors.avatar }}</p>
                    </div>

                    <!-- Ministry name -->
                    <div class="form-group">
                        <label for="ministry_name">{{ t('onboarding.ministryName') }} *</label>
                        <div class="input-wrapper" :class="{ error: form.errors.ministry_name }">
                            <div class="input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                            <input id="ministry_name" v-model="form.ministry_name" type="text" required :placeholder="t('onboarding.ministryNamePlaceholder')" />
                        </div>
                        <p v-if="form.errors.ministry_name" class="field-error">{{ form.errors.ministry_name }}</p>
                    </div>

                    <!-- Ministry type -->
                    <div class="form-group">
                        <label for="ministry_type">{{ t('onboarding.ministryType') }} *</label>
                        <select id="ministry_type" v-model="form.ministry_type" required class="select" :class="{ error: form.errors.ministry_type }">
                            <option value="" disabled>{{ t('onboarding.selectMinistryType') }}</option>
                            <option v-for="(label, value) in ministryTypes" :key="value" :value="value">{{ label }}</option>
                        </select>
                        <p v-if="form.errors.ministry_type" class="field-error">{{ form.errors.ministry_type }}</p>
                    </div>

                    <!-- Country -->
                    <div class="form-group">
                        <label for="country_select">{{ t('onboarding.country') }} *</label>
                        <select id="country_select" :value="form.country_code" @change="onCountryChange" required class="select" :class="{ error: form.errors.country_name || form.errors.country_code }">
                            <option value="" disabled>{{ t('onboarding.countryPlaceholder') }}</option>
                            <option v-for="c in countryList" :key="c.code" :value="c.code">{{ c.name }}</option>
                        </select>
                        <p v-if="form.errors.country_name" class="field-error">{{ form.errors.country_name }}</p>
                        <p v-if="form.errors.country_code" class="field-error">{{ form.errors.country_code }}</p>
                    </div>

                    <!-- City -->
                    <div class="form-group">
                        <label for="city">{{ t('onboarding.city') }} *</label>
                        <div class="input-wrapper" :class="{ error: form.errors.city }">
                            <div class="input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <input id="city" v-model="form.city" type="text" required :placeholder="t('onboarding.cityPlaceholder')" />
                        </div>
                        <p v-if="form.errors.city" class="field-error">{{ form.errors.city }}</p>
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="form.processing" class="submit-btn">
                        <svg v-if="form.processing" class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity=".25"/><path fill="currentColor" opacity=".75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span v-if="form.processing">{{ t('onboarding.saving') }}</span>
                        <template v-else>
                            <span>{{ t('onboarding.finishSetup') }}</span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        </template>
                    </button>
                </form>

                <!-- Footer -->
                <div class="setup-footer">
                    <button @click="logout" class="logout-link" type="button">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        {{ t('verification.logout') }}
                    </button>
                    <p>&copy; {{ new Date().getFullYear() }} Mahubiri</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Hero -->
        <div class="setup-right">
            <div class="right-bg"></div>
            <div class="right-pattern"></div>
            <div class="right-glow right-glow-1"></div>
            <div class="right-glow right-glow-2"></div>

            <div class="right-content">
                <div class="right-badge">
                    <span class="pulse-dot"></span>
                    {{ t('onboarding.preacherHeroBadge') }}
                </div>
                <h2 class="right-title">
                    {{ t('onboarding.preacherHeroTitle1') }}<br>
                    <span>{{ t('onboarding.preacherHeroTitle2') }}</span>
                </h2>
                <p class="right-desc">{{ t('onboarding.preacherHeroDesc') }}</p>

                <div class="right-steps">
                    <div class="right-step done">
                        <div class="rs-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div class="rs-text">
                            <strong>{{ t('onboarding.heroStep1') }}</strong>
                            <span>{{ t('onboarding.heroStep1Desc') }}</span>
                        </div>
                    </div>
                    <div class="right-step done">
                        <div class="rs-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div class="rs-text">
                            <strong>{{ t('onboarding.heroStep2') }}</strong>
                            <span>{{ t('onboarding.heroStep2Desc') }}</span>
                        </div>
                    </div>
                    <div class="right-step active">
                        <div class="rs-icon rs-active-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        </div>
                        <div class="rs-text">
                            <strong>{{ t('onboarding.preacherHeroStep3') }}</strong>
                            <span>{{ t('onboarding.preacherHeroStep3Desc') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── Page layout ── */
.setup-page {
    display: flex; height: 100vh; background: #ffffff;
    overflow: hidden; font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
}

/* ── Left Panel ── */
.setup-left {
    width: 100%; max-width: 580px;
    display: flex; flex-direction: column;
    padding: 24px 48px; position: relative; z-index: 10; background: #ffffff;
    overflow-y: auto;
}
@media (min-width: 1024px) { .setup-left { width: 580px; flex-shrink: 0; } }
@media (max-width: 1023px) { .setup-left { max-width: 100%; margin: 0 auto; padding: 20px 24px; } }

.setup-form-wrapper {
    width: 100%; max-width: 460px; margin: auto;
    padding: 12px 0;
    animation: fadeUp .6s ease both;
}

/* Logo */
.setup-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.setup-logo-icon {
    width: 44px; height: 44px; border-radius: 14px; object-fit: contain;
    box-shadow: 0 4px 12px rgba(107, 78, 175, 0.3);
}
.setup-logo-title { font-size: 20px; font-weight: 700; color: #111827; letter-spacing: -0.3px; line-height: 1.2; }
.setup-logo-sub { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

/* Toolbar */
.setup-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.setup-toolbar :deep(.relative > div[class*="absolute"]) { right: auto; left: 0; }
.theme-toggle {
    display: flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 12px;
    background: #f3f4f6; border: 2px solid #e5e7eb; color: #6b7280; cursor: pointer;
    transition: all .25s;
}
.theme-toggle:hover { background: #e5e7eb; color: #6B4EAF; border-color: #6B4EAF; }

/* Heading */
.setup-heading { margin-bottom: 14px; }
.setup-heading h2 { font-size: 22px; font-weight: 800; color: #111827; letter-spacing: -0.5px; margin-bottom: 2px; }
.setup-heading p { font-size: 13px; color: #6b7280; line-height: 1.5; }

/* Step indicator */
.step-indicator { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.step { display: flex; align-items: center; gap: 6px; }
.step-dot {
    width: 26px; height: 26px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700;
    background: #f3f4f6; color: #9ca3af; border: 2px solid #e5e7eb;
    transition: all .3s;
}
.step.active .step-dot { background: #6B4EAF; color: #fff; border-color: #6B4EAF; }
.step.completed .step-dot { background: #10b981; color: #fff; border-color: #10b981; }
.step.completed .step-dot svg { width: 12px; height: 12px; }
.step span:last-child { font-size: 11px; font-weight: 600; color: #9ca3af; }
.step.active span:last-child { color: #6B4EAF; }
.step.completed span:last-child { color: #10b981; }
.step-line { flex: 1; height: 2px; background: #e5e7eb; }
.step-line.completed-line { background: #10b981; }

/* Avatar upload */
.avatar-upload-zone {
    display: flex; align-items: center; justify-content: center;
    border: 2px dashed #d1d5db; border-radius: 14px;
    padding: 16px; cursor: pointer; transition: all .25s;
    background: #f9fafb; min-height: 100px; position: relative;
}
.avatar-upload-zone:hover { border-color: #6B4EAF; background: rgba(107,78,175,0.02); }
.hidden-input { display: none; }
.avatar-preview { position: relative; width: 80px; height: 80px; }
.avatar-preview img {
    width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
    border: 2px solid #e5e7eb;
}
.avatar-remove {
    position: absolute; top: -6px; right: -6px;
    width: 22px; height: 22px; border-radius: 50%;
    background: #ef4444; color: #fff; border: 2px solid #fff;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .2s;
}
.avatar-remove:hover { background: #dc2626; transform: scale(1.1); }
.avatar-remove svg { width: 12px; height: 12px; }
.avatar-placeholder {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    color: #9ca3af;
}
.avatar-placeholder svg { width: 32px; height: 32px; }
.avatar-placeholder span { font-size: 13px; font-weight: 600; color: #6b7280; }
.avatar-hint { font-size: 11px; font-weight: 400; color: #9ca3af !important; }

/* Form */
.preacher-form { display: flex; flex-direction: column; gap: 12px; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
.input-wrapper {
    position: relative; display: flex; align-items: center;
    border: 2px solid #e5e7eb; border-radius: 12px;
    background: #f9fafb; transition: all .25s; overflow: hidden;
}
.input-wrapper:focus-within { border-color: #6B4EAF; background: #fff; box-shadow: 0 0 0 4px rgba(107,78,175,0.08); }
.input-wrapper.error { border-color: #ef4444; background: #fef2f2; }
.input-icon { display: flex; align-items: center; justify-content: center; width: 40px; flex-shrink: 0; color: #9ca3af; }
.input-wrapper:focus-within .input-icon { color: #6B4EAF; }
.input-icon svg { width: 16px; height: 16px; }
.input-wrapper input {
    flex: 1; padding: 10px 12px 10px 0; font-size: 13px; color: #111827;
    background: transparent; border: none; outline: none; font-family: inherit;
}
.input-wrapper input::placeholder { color: #9ca3af; }
.select {
    width: 100%; padding: 10px 14px; font-size: 13px; color: #111827;
    background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 12px;
    outline: none; font-family: inherit; cursor: pointer; transition: all .25s;
    appearance: auto;
}
.select:focus { border-color: #6B4EAF; background: #fff; box-shadow: 0 0 0 4px rgba(107,78,175,0.08); }
.select.error { border-color: #ef4444; }
.field-error { margin-top: 4px; font-size: 12px; color: #ef4444; }

/* Submit */
.submit-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    width: 100%; padding: 12px 24px; margin-top: 4px;
    background: linear-gradient(135deg, #6B4EAF 0%, #5a3d96 100%);
    color: #fff; font-size: 14px; font-weight: 700;
    border: none; border-radius: 12px; cursor: pointer;
    transition: all .3s; box-shadow: 0 4px 16px rgba(107,78,175,0.3);
    font-family: inherit; line-height: 1;
}
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(107,78,175,0.35); }
.submit-btn:disabled { opacity: .6; cursor: not-allowed; transform: none !important; }
.submit-btn svg { width: 18px; height: 18px; }
.spinner { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Footer */
.setup-footer {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 16px; padding-top: 12px; border-top: 1px solid #f3f4f6;
}
.setup-footer p { font-size: 11px; color: #9ca3af; }
.logout-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600; color: #6b7280; background: none; border: none;
    cursor: pointer; font-family: inherit;
}
.logout-link:hover { color: #ef4444; }
.logout-link svg { width: 14px; height: 14px; }

/* ── Right Panel ── */
.setup-right { display: none; flex: 1; position: relative; overflow: hidden; }
@media (min-width: 1024px) { .setup-right { display: flex; } }
.right-bg { position: absolute; inset: 0; background: linear-gradient(135deg, #6B4EAF 0%, #4a2d8a 40%, #3a2070 100%); }
.right-pattern { position: absolute; inset: 0; opacity: .03; background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 28px 28px; }
.right-glow { position: absolute; border-radius: 50%; filter: blur(100px); }
.right-glow-1 { width: 500px; height: 500px; background: rgba(232, 183, 125, 0.2); top: -15%; right: -10%; animation: floatGlow 15s ease-in-out infinite alternate; }
.right-glow-2 { width: 400px; height: 400px; background: rgba(156, 125, 199, 0.25); bottom: -10%; left: -5%; animation: floatGlow 18s ease-in-out infinite alternate-reverse; }
@keyframes floatGlow { 0% { transform: translate(0,0) scale(1); } 50% { transform: translate(20px,-30px) scale(1.1); } 100% { transform: translate(-10px,15px) scale(.95); } }

.right-content {
    position: relative; z-index: 10; display: flex; flex-direction: column; justify-content: center;
    padding: 48px 52px; width: 100%; animation: fadeUp .8s ease .2s both;
}
.right-badge {
    display: inline-flex; align-items: center; gap: 8px; align-self: flex-start;
    padding: 7px 16px; border-radius: 999px; background: rgba(255,255,255,0.1);
    backdrop-filter: blur(12px); color: rgba(255,255,255,0.9); font-size: 12px; font-weight: 600;
    border: 1px solid rgba(255,255,255,0.1); margin-bottom: 28px;
}
.pulse-dot { width: 7px; height: 7px; border-radius: 50%; background: #34d399; animation: pulse 2s ease-in-out infinite; }
@keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: .5; transform: scale(.8); } }
.right-title { font-size: clamp(28px,3vw,38px); font-weight: 800; color: #fff; line-height: 1.15; letter-spacing: -0.5px; margin-bottom: 14px; }
.right-title span { color: #E8B77D; }
.right-desc { font-size: 15px; color: rgba(255,255,255,0.6); line-height: 1.65; max-width: 440px; margin-bottom: 32px; }

/* Right steps */
.right-steps { display: flex; flex-direction: column; gap: 16px; }
.right-step {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 16px 18px; border-radius: 14px;
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);
    backdrop-filter: blur(8px); transition: all .3s;
}
.right-step.done { background: rgba(16,185,129,0.08); border-color: rgba(16,185,129,0.15); }
.right-step.active { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.15); }
.rs-icon {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: rgba(16,185,129,0.2); color: #34d399;
}
.rs-icon svg { width: 18px; height: 18px; }
.rs-active-icon { background: rgba(232,183,125,0.2); color: #E8B77D; }
.rs-text { display: flex; flex-direction: column; gap: 2px; }
.rs-text strong { font-size: 14px; font-weight: 700; color: #fff; }
.rs-text span { font-size: 12px; color: rgba(255,255,255,0.5); line-height: 1.4; }

/* ── Animation ── */
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ── Dark Mode ── */
:root.dark .setup-page { background: #111827; }
:root.dark .setup-left { background: #111827; }
:root.dark .setup-logo-title { color: #f3f4f6; }
:root.dark .setup-heading h2 { color: #f3f4f6; }
:root.dark .setup-heading p { color: #9ca3af; }
:root.dark .form-group label { color: #d1d5db; }
:root.dark .input-wrapper { border-color: #374151; background: #1f2937; }
:root.dark .input-wrapper:focus-within { border-color: #8b6fcf; background: #1a1a2e; box-shadow: 0 0 0 4px rgba(139,111,207,0.15); }
:root.dark .input-wrapper input { color: #f3f4f6; }
:root.dark .input-wrapper input::placeholder { color: #6b7280; }
:root.dark .select { background: #1f2937; border-color: #374151; color: #f3f4f6; }
:root.dark .select:focus { border-color: #8b6fcf; background: #1a1a2e; }
:root.dark .step-dot { background: #1f2937; border-color: #374151; color: #6b7280; }
:root.dark .step.active .step-dot { background: #6B4EAF; color: #fff; border-color: #6B4EAF; }
:root.dark .step.completed .step-dot { background: #10b981; border-color: #10b981; }
:root.dark .step-line { background: #374151; }
:root.dark .step-line.completed-line { background: #10b981; }
:root.dark .theme-toggle { background: #1f2937; border-color: #374151; color: #94a3b8; }
:root.dark .theme-toggle:hover { border-color: #e8b77d; color: #e8b77d; }
:root.dark .setup-footer { border-top-color: #1f2937; }
:root.dark .avatar-upload-zone { border-color: #374151; background: #1f2937; }
:root.dark .avatar-upload-zone:hover { border-color: #8b6fcf; background: rgba(139,111,207,0.05); }
:root.dark .avatar-placeholder span { color: #94a3b8; }
:root.dark .avatar-preview img { border-color: #374151; }
</style>
