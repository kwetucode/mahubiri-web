<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';
import { getCountries, getCountryCallingCode, parsePhoneNumberFromString, AsYouType, getExampleNumber } from 'libphonenumber-js';
import examples from 'libphonenumber-js/mobile/examples';
import countries from 'i18n-iso-countries';
import countriesFr from 'i18n-iso-countries/langs/fr.json';
import countriesEn from 'i18n-iso-countries/langs/en.json';

countries.registerLocale(countriesFr);
countries.registerLocale(countriesEn);

const { t, locale } = useI18n();
const { theme, toggleTheme } = useTheme();

const selectedCountry = ref('CD');
const phoneError = ref('');
const showCountryDropdown = ref(false);
const countrySearch = ref('');
const countryDropdownRef = ref(null);

const phoneCountries = computed(() => {
    const lang = ['fr', 'en'].includes(locale.value) ? locale.value : 'fr';
    return getCountries()
        .filter(code => { try { getCountryCallingCode(code); return true; } catch { return false; } })
        .map(code => ({
            code,
            name: countries.getName(code, lang, { select: 'official' }) || code,
            dialCode: '+' + getCountryCallingCode(code),
        }))
        .sort((a, b) => a.name.localeCompare(b.name, lang));
});

const filteredCountries = computed(() => {
    if (!countrySearch.value) return phoneCountries.value;
    const q = countrySearch.value.toLowerCase();
    return phoneCountries.value.filter(c =>
        c.name.toLowerCase().includes(q) || c.dialCode.includes(q) || c.code.toLowerCase().includes(q)
    );
});

const currentDialCode = computed(() => {
    try { return '+' + getCountryCallingCode(selectedCountry.value); } catch { return ''; }
});

const countryFlag = (code) => {
    return code.toUpperCase().replace(/./g, ch => String.fromCodePoint(0x1F1E6 + ch.charCodeAt(0) - 65));
};

const phonePlaceholder = computed(() => {
    try {
        const ex = getExampleNumber(selectedCountry.value, examples);
        return ex ? ex.formatNational() : '';
    } catch { return ''; }
});

const selectCountry = (code) => {
    selectedCountry.value = code;
    showCountryDropdown.value = false;
    countrySearch.value = '';
    validatePhone();
};

const onPhoneBlur = () => { validatePhone(); };

const validatePhone = () => {
    phoneError.value = '';
    if (!form.phone) return;
    const fullNumber = currentDialCode.value + form.phone.replace(/^0+/, '');
    const parsed = parsePhoneNumberFromString(fullNumber, selectedCountry.value);
    if (!parsed || !parsed.isValid()) {
        phoneError.value = t('register.phoneInvalid');
    }
};

const closeDropdown = (e) => {
    if (countryDropdownRef.value && !countryDropdownRef.value.contains(e.target)) {
        showCountryDropdown.value = false;
        countrySearch.value = '';
    }
};

watch(showCountryDropdown, (val) => {
    if (val) { document.addEventListener('click', closeDropdown, true); }
    else { document.removeEventListener('click', closeDropdown, true); }
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    account_type: 'church_admin',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    if (form.phone) {
        const fullNumber = currentDialCode.value + form.phone.replace(/^0+/, '');
        const parsed = parsePhoneNumberFromString(fullNumber, selectedCountry.value);
        if (!parsed || !parsed.isValid()) {
            phoneError.value = t('register.phoneInvalid');
            return;
        }
        form.phone = parsed.format('E.164');
    }
    form.post('/admin/register', {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

const accountTypes = computed(() => [
    { value: 'church_admin', label: t('register.typeChurchAdmin'), icon: '⛪', desc: t('register.typeChurchAdminDesc') },
    { value: 'independent_preacher', label: t('register.typePreacher'), icon: '🎤', desc: t('register.typePreacherDesc') },
]);
</script>

<template>
    <div class="register-page">
        <!-- Left Side - Form -->
        <div class="register-left">
            <div class="register-form-wrapper">
                <!-- Logo -->
                <div class="register-logo">
                    <img src="/logo.png" alt="Mahubiri" class="register-logo-icon" />
                    <div>
                        <h1 class="register-logo-title">Mahubiri</h1>
                        <p class="register-logo-sub">{{ t('register.subtitle') }}</p>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="register-toolbar">
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
                <div class="register-heading">
                    <h2>{{ t('register.title') }}</h2>
                    <p>{{ t('register.description') }}</p>
                </div>

                <!-- Step indicator -->
                <div class="step-indicator">
                    <div class="step active">
                        <div class="step-dot">1</div>
                        <span>{{ t('register.step1') }}</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step">
                        <div class="step-dot">2</div>
                        <span>{{ t('register.step2') }}</span>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="register-form">
                    <!-- Account type selector -->
                    <div class="form-group">
                        <label>{{ t('register.accountType') }}</label>
                        <div class="account-type-grid">
                            <button
                                v-for="type in accountTypes"
                                :key="type.value"
                                type="button"
                                class="account-type-card"
                                :class="{ active: form.account_type === type.value }"
                                @click="form.account_type = type.value"
                            >
                                <span class="type-icon">{{ type.icon }}</span>
                                <span class="type-label">{{ type.label }}</span>
                                <span class="type-desc">{{ type.desc }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.account_type" class="field-error">
                            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ form.errors.account_type }}
                        </p>
                    </div>

                    <!-- Name & Email row -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">{{ t('register.name') }}</label>
                            <div class="input-wrapper" :class="{ error: form.errors.name }">
                                <div class="input-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <input id="name" v-model="form.name" type="text" required autofocus :placeholder="t('register.namePlaceholder')" />
                            </div>
                            <p v-if="form.errors.name" class="field-error">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="email">{{ t('register.email') }}</label>
                            <div class="input-wrapper" :class="{ error: form.errors.email }">
                                <div class="input-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <input id="email" v-model="form.email" type="email" required :placeholder="t('register.emailPlaceholder')" />
                            </div>
                            <p v-if="form.errors.email" class="field-error">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                {{ form.errors.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="form-group">
                        <label for="phone">{{ t('register.phone') }}</label>
                        <div class="phone-wrapper" :class="{ error: form.errors.phone || phoneError }">
                            <div ref="countryDropdownRef" class="country-selector">
                                <button type="button" class="country-btn" @click.stop="showCountryDropdown = !showCountryDropdown">
                                    <span class="country-flag">{{ countryFlag(selectedCountry) }}</span>
                                    <span class="country-dial">{{ currentDialCode }}</span>
                                    <svg class="country-chevron" :class="{ open: showCountryDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                                <div v-if="showCountryDropdown" class="country-dropdown">
                                    <div class="country-search-wrap">
                                        <input v-model="countrySearch" type="text" class="country-search" :placeholder="t('register.searchCountry')" @click.stop />
                                    </div>
                                    <div class="country-list">
                                        <button v-for="c in filteredCountries" :key="c.code" type="button" class="country-option" :class="{ active: c.code === selectedCountry }" @click="selectCountry(c.code)">
                                            <span class="co-flag">{{ countryFlag(c.code) }}</span>
                                            <span class="co-name">{{ c.name }}</span>
                                            <span class="co-dial">{{ c.dialCode }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input id="phone" v-model="form.phone" type="tel" :placeholder="phonePlaceholder" @blur="onPhoneBlur" />
                        </div>
                        <p v-if="form.errors.phone || phoneError" class="field-error">
                            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            {{ form.errors.phone || phoneError }}
                        </p>
                    </div>

                    <!-- Password & Confirm Password row -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">{{ t('register.password') }}</label>
                            <div class="input-wrapper" :class="{ error: form.errors.password }">
                                <div class="input-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                </div>
                                <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" required placeholder="••••••••" />
                                <button type="button" @click="showPassword = !showPassword" class="toggle-password">
                                    <svg v-if="!showPassword" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="field-error">
                                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">{{ t('register.confirmPassword') }}</label>
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </div>
                                <input id="password_confirmation" v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" required placeholder="••••••••" />
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="toggle-password">
                                    <svg v-if="!showConfirmPassword" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="form.processing" class="submit-btn">
                        <svg v-if="form.processing" class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity=".25"/><path fill="currentColor" opacity=".75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span v-if="form.processing">{{ t('register.submitting') }}</span>
                        <template v-else>
                            <span>{{ t('register.submit') }}</span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </template>
                    </button>
                </form>

                <!-- Footer -->
                <div class="register-footer">
                    <a href="/admin/login" class="back-link">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        {{ t('register.backToLogin') }}
                    </a>
                    <p>&copy; {{ new Date().getFullYear() }} Mahubiri</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Hero -->
        <div class="register-right">
            <div class="right-bg"></div>
            <div class="right-pattern"></div>
            <div class="right-glow right-glow-1"></div>
            <div class="right-glow right-glow-2"></div>

            <div class="right-content">
                <div class="right-badge">
                    <span class="pulse-dot"></span>
                    {{ t('register.heroBadge') }}
                </div>
                <h2 class="right-title">
                    {{ t('register.heroTitle1') }}<br>
                    <span>{{ t('register.heroTitle2') }}</span>
                </h2>
                <p class="right-desc">{{ t('register.heroDesc') }}</p>

                <div class="right-features">
                    <div class="right-feature" v-for="(f, i) in [
                        { icon: '⛪', text: t('register.heroFeature1') },
                        { icon: '🎤', text: t('register.heroFeature2') },
                        { icon: '📊', text: t('register.heroFeature3') },
                    ]" :key="i">
                        <span class="rf-icon">{{ f.icon }}</span>
                        <span class="rf-text">{{ f.text }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── Page layout ── */
.register-page {
    display: flex; height: 100vh; background: #ffffff;
    overflow: hidden; font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
}

/* ── Left Panel ── */
.register-left {
    width: 100%; max-width: 560px;
    display: flex; flex-direction: column;
    padding: 24px 48px; position: relative; z-index: 10; background: #ffffff;
    overflow-y: auto;
}
@media (min-width: 1024px) { .register-left { width: 560px; flex-shrink: 0; } }
@media (max-width: 1023px) { .register-left { max-width: 100%; margin: 0 auto; padding: 20px 24px; } }

.register-form-wrapper {
    width: 100%; max-width: 440px; margin: auto;
    padding: 12px 0;
    animation: fadeUp .6s ease both;
}

/* Logo */
.register-logo {
    display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
}
.register-logo-icon {
    width: 44px; height: 44px; border-radius: 14px; object-fit: contain;
    box-shadow: 0 4px 12px rgba(107, 78, 175, 0.3);
}
.register-logo-title { font-size: 20px; font-weight: 700; color: #111827; letter-spacing: -0.3px; line-height: 1.2; }
.register-logo-sub { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

/* Toolbar */
.register-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.register-toolbar :deep(.relative > div[class*="absolute"]) { right: auto; left: 0; }
.theme-toggle {
    display: flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 12px;
    background: #f3f4f6; border: 2px solid #e5e7eb; color: #6b7280; cursor: pointer;
    transition: all .25s;
}
.theme-toggle:hover { background: #e5e7eb; color: #6B4EAF; border-color: #6B4EAF; }

/* Heading */
.register-heading { margin-bottom: 14px; }
.register-heading h2 { font-size: 22px; font-weight: 800; color: #111827; letter-spacing: -0.5px; margin-bottom: 2px; }
.register-heading p { font-size: 13px; color: #6b7280; line-height: 1.5; }

/* Step indicator */
.step-indicator { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.step { display: flex; align-items: center; gap: 8px; }
.step-dot {
    width: 26px; height: 26px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700;
    background: #f3f4f6; color: #9ca3af; border: 2px solid #e5e7eb;
    transition: all .3s;
}
.step.active .step-dot { background: #6B4EAF; color: #fff; border-color: #6B4EAF; }
.step span:last-child { font-size: 12px; font-weight: 600; color: #9ca3af; }
.step.active span:last-child { color: #6B4EAF; }
.step-line { flex: 1; height: 2px; background: #e5e7eb; }

/* Account type selector */
.account-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.account-type-card {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 12px 10px; border-radius: 12px;
    background: #f9fafb; border: 2px solid #e5e7eb;
    cursor: pointer; transition: all .25s; text-align: center;
}
.account-type-card:hover { border-color: #d1d5db; background: #f3f4f6; }
.account-type-card.active { border-color: #6B4EAF; background: rgba(107, 78, 175, 0.04); }
.type-icon { font-size: 20px; }
.type-label { font-size: 12px; font-weight: 700; color: #111827; }
.type-desc { font-size: 10px; color: #9ca3af; line-height: 1.3; }

/* Form */
.register-form { display: flex; flex-direction: column; gap: 12px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-row > .form-group { min-width: 0; }
@media (max-width: 500px) { .form-row { grid-template-columns: 1fr; } }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
.input-wrapper {
    position: relative; display: flex; align-items: center;
    border: 2px solid #e5e7eb; border-radius: 12px;
    background: #f9fafb; transition: all .25s; overflow: hidden;
}
.input-wrapper:focus-within { border-color: #6B4EAF; background: #fff; box-shadow: 0 0 0 4px rgba(107, 78, 175, 0.08); }
.input-wrapper.error { border-color: #ef4444; background: #fef2f2; }
.input-icon { display: flex; align-items: center; justify-content: center; width: 40px; flex-shrink: 0; color: #9ca3af; }
.input-wrapper:focus-within .input-icon { color: #6B4EAF; }
.input-icon svg { width: 16px; height: 16px; }
.input-wrapper input {
    flex: 1; padding: 10px 12px 10px 0; font-size: 13px; color: #111827;
    background: transparent; border: none; outline: none; font-family: inherit;
}
.input-wrapper input::placeholder { color: #9ca3af; }
.toggle-password {
    display: flex; align-items: center; justify-content: center;
    width: 40px; flex-shrink: 0; cursor: pointer; background: none; border: none; color: #9ca3af; padding: 0;
}
.toggle-password:hover { color: #6b7280; }
.toggle-password svg { width: 16px; height: 16px; }

/* Phone country selector */
.phone-wrapper {
    position: relative; display: flex; align-items: center;
    border: 2px solid #e5e7eb; border-radius: 12px;
    background: #f9fafb; transition: all .25s; overflow: visible;
}
.phone-wrapper:focus-within { border-color: #6B4EAF; background: #fff; box-shadow: 0 0 0 4px rgba(107,78,175,0.08); }
.phone-wrapper.error { border-color: #ef4444; background: #fef2f2; }
.country-selector { position: relative; flex-shrink: 0; }
.country-btn {
    display: flex; align-items: center; gap: 4px;
    padding: 0 8px 0 12px; height: 38px; border: none; background: none;
    cursor: pointer; font-family: inherit; color: #374151;
    border-right: 1px solid #e5e7eb; transition: all .2s;
}
.country-btn:hover { background: rgba(107,78,175,0.04); }
.country-flag { font-size: 18px; line-height: 1; }
.country-dial { font-size: 13px; font-weight: 600; color: #374151; }
.country-chevron { width: 12px; height: 12px; color: #9ca3af; transition: transform .2s; }
.country-chevron.open { transform: rotate(180deg); }
.country-dropdown {
    position: absolute; top: calc(100% + 6px); left: -2px; z-index: 100;
    width: 280px; max-height: 260px;
    background: #fff; border: 2px solid #e5e7eb; border-radius: 12px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.12); overflow: hidden;
    animation: dropIn .15s ease;
}
@keyframes dropIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
.country-search-wrap { padding: 8px; border-bottom: 1px solid #f3f4f6; }
.country-search {
    width: 100%; padding: 7px 10px; font-size: 12px; border: 1.5px solid #e5e7eb;
    border-radius: 8px; outline: none; font-family: inherit; background: #f9fafb; color: #111827;
}
.country-search:focus { border-color: #6B4EAF; }
.country-search::placeholder { color: #9ca3af; }
.country-list { max-height: 200px; overflow-y: auto; padding: 4px; }
.country-option {
    display: flex; align-items: center; gap: 8px; width: 100%;
    padding: 7px 10px; border: none; background: none; border-radius: 8px;
    cursor: pointer; font-family: inherit; text-align: left; transition: background .15s;
}
.country-option:hover { background: #f3f4f6; }
.country-option.active { background: rgba(107,78,175,0.08); }
.co-flag { font-size: 16px; line-height: 1; flex-shrink: 0; }
.co-name { font-size: 12px; color: #374151; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.co-dial { font-size: 12px; font-weight: 600; color: #6B4EAF; flex-shrink: 0; }
.phone-wrapper input {
    flex: 1; padding: 10px 12px; font-size: 13px; color: #111827;
    background: transparent; border: none; outline: none; font-family: inherit;
    min-width: 0;
}
.phone-wrapper input::placeholder { color: #9ca3af; }

.field-error { display: flex; align-items: center; gap: 5px; margin-top: 6px; font-size: 13px; color: #ef4444; }
.field-error svg { width: 14px; height: 14px; flex-shrink: 0; }

/* Submit */
.submit-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    width: 100%; padding: 12px 24px; margin-top: 4px;
    background: linear-gradient(135deg, #6B4EAF 0%, #5a3d96 100%);
    color: #fff; font-size: 14px; font-weight: 700;
    border: none; border-radius: 12px; cursor: pointer;
    transition: all .3s; box-shadow: 0 4px 16px rgba(107, 78, 175, 0.3);
    font-family: inherit; line-height: 1;
}
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(107, 78, 175, 0.35); }
.submit-btn:disabled { opacity: .6; cursor: not-allowed; transform: none !important; }
.submit-btn svg { width: 18px; height: 18px; }
.spinner { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Footer */
.register-footer {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 16px; padding-top: 12px; border-top: 1px solid #f3f4f6;
}
.register-footer p { font-size: 11px; color: #9ca3af; }
.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600; color: #6b7280; text-decoration: none;
}
.back-link:hover { color: #6B4EAF; }
.back-link svg { width: 14px; height: 14px; }

/* ── Right Panel ── */
.register-right { display: none; flex: 1; position: relative; overflow: hidden; }
@media (min-width: 1024px) { .register-right { display: flex; } }
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

.right-features { display: flex; flex-direction: column; gap: 14px; }
.right-feature {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 18px; border-radius: 14px;
    background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);
    backdrop-filter: blur(8px);
}
.rf-icon { font-size: 22px; }
.rf-text { font-size: 14px; color: rgba(255,255,255,0.85); font-weight: 500; }

/* ── Animation ── */
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ── Dark Mode ── */
:root.dark .register-page { background: #111827; }
:root.dark .register-left { background: #111827; }
:root.dark .register-logo-title { color: #f3f4f6; }
:root.dark .register-heading h2 { color: #f3f4f6; }
:root.dark .register-heading p { color: #9ca3af; }
:root.dark .form-group label { color: #d1d5db; }
:root.dark .input-wrapper { border-color: #374151; background: #1f2937; }
:root.dark .input-wrapper:focus-within { border-color: #8b6fcf; background: #1a1a2e; box-shadow: 0 0 0 4px rgba(139,111,207,0.15); }
:root.dark .input-wrapper input { color: #f3f4f6; }
:root.dark .input-wrapper input::placeholder { color: #6b7280; }
:root.dark .phone-wrapper { border-color: #374151; background: #1f2937; }
:root.dark .phone-wrapper:focus-within { border-color: #8b6fcf; background: #1a1a2e; box-shadow: 0 0 0 4px rgba(139,111,207,0.15); }
:root.dark .phone-wrapper input { color: #f3f4f6; }
:root.dark .phone-wrapper input::placeholder { color: #6b7280; }
:root.dark .country-btn { color: #d1d5db; border-right-color: #374151; }
:root.dark .country-dial { color: #d1d5db; }
:root.dark .country-dropdown { background: #1f2937; border-color: #374151; box-shadow: 0 12px 40px rgba(0,0,0,0.4); }
:root.dark .country-search-wrap { border-bottom-color: #374151; }
:root.dark .country-search { background: #111827; border-color: #374151; color: #f3f4f6; }
:root.dark .country-search:focus { border-color: #8b6fcf; }
:root.dark .country-option:hover { background: #374151; }
:root.dark .country-option.active { background: rgba(139,111,207,0.15); }
:root.dark .co-name { color: #d1d5db; }
:root.dark .account-type-card { background: #1f2937; border-color: #374151; }
:root.dark .account-type-card:hover { border-color: #4b5563; background: #374151; }
:root.dark .account-type-card.active { border-color: #8b6fcf; background: rgba(139,111,207,0.1); }
:root.dark .type-label { color: #f3f4f6; }
:root.dark .type-desc { color: #6b7280; }
:root.dark .step-dot { background: #1f2937; border-color: #374151; color: #6b7280; }
:root.dark .step.active .step-dot { background: #6B4EAF; color: #fff; border-color: #6B4EAF; }
:root.dark .step-line { background: #374151; }
:root.dark .register-footer { border-top-color: #1f2937; }
:root.dark .back-link { color: #9ca3af; }
:root.dark .back-link:hover { color: #a78bfa; }
:root.dark .theme-toggle { background: #1f2937; border-color: #374151; color: #9ca3af; }
:root.dark .theme-toggle:hover { background: #374151; color: #e8b77d; border-color: #e8b77d; }
:root.dark .right-bg { background: linear-gradient(135deg, #4a2d8a 0%, #2d1a5e 40%, #1a1035 100%); }
</style>
