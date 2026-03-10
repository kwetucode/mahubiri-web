<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';

const { t } = useI18n();
const { theme, toggleTheme } = useTheme();
const page = usePage();
const email = page.props.email;

const form = useForm({ code: '' });
const digits = ref(['', '', '', '', '', '']);
const inputRefs = ref([]);
const resending = ref(false);
const resendSuccess = ref(false);

const setRef = (el, i) => { if (el) inputRefs.value[i] = el; };

const focusInput = (i) => { nextTick(() => inputRefs.value[i]?.focus()); };

const onInput = (i) => {
    const val = digits.value[i];
    if (val && val.length >= 1) {
        digits.value[i] = val.slice(-1);
        if (i < 5) focusInput(i + 1);
    }
    form.code = digits.value.join('');
};

const onKeydown = (e, i) => {
    if (e.key === 'Backspace' && !digits.value[i] && i > 0) {
        focusInput(i - 1);
    }
};

const onPaste = (e) => {
    e.preventDefault();
    const pasted = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
    for (let i = 0; i < 6; i++) digits.value[i] = pasted[i] || '';
    form.code = digits.value.join('');
    focusInput(Math.min(pasted.length, 5));
};

const submit = () => {
    form.post('/admin/email/verify', {
        preserveScroll: true,
    });
};

const resendCode = () => {
    resending.value = true;
    resendSuccess.value = false;
    router.post('/admin/email/resend', {}, {
        preserveScroll: true,
        onSuccess: () => { resendSuccess.value = true; },
        onFinish: () => { resending.value = false; },
    });
};

onMounted(() => focusInput(0));
</script>

<template>
    <div class="verify-page">
        <!-- Left Side - Form -->
        <div class="verify-left">
            <div class="verify-form-wrapper">
                <!-- Logo -->
                <div class="verify-logo">
                    <img src="/logo.png" alt="Mahubiri" class="verify-logo-icon" />
                    <div>
                        <h1 class="verify-logo-title">Mahubiri</h1>
                        <p class="verify-logo-sub">{{ t('verification.subtitle') }}</p>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="verify-toolbar">
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

                <!-- Step indicator -->
                <div class="step-indicator">
                    <div class="step done">
                        <div class="step-dot">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <span>{{ t('register.step1') }}</span>
                    </div>
                    <div class="step-line active-line"></div>
                    <div class="step active">
                        <div class="step-dot">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span>{{ t('verification.stepLabel') }}</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step">
                        <div class="step-dot">3</div>
                        <span>{{ t('register.step2') }}</span>
                    </div>
                </div>

                <!-- Heading -->
                <div class="verify-heading">
                    <div class="verify-icon-wrapper">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h2>{{ t('verification.title') }}</h2>
                    <p>{{ t('verification.description', { email }) }}</p>
                </div>

                <!-- Code input -->
                <form @submit.prevent="submit" class="verify-form">
                    <div class="code-inputs">
                        <input
                            v-for="(d, i) in digits"
                            :key="i"
                            :ref="el => setRef(el, i)"
                            v-model="digits[i]"
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            class="code-digit"
                            :class="{ error: form.errors.code }"
                            @input="onInput(i)"
                            @keydown="onKeydown($event, i)"
                            @paste="onPaste"
                            autocomplete="one-time-code"
                        />
                    </div>
                    <p v-if="form.errors.code" class="field-error">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        {{ form.errors.code }}
                    </p>

                    <!-- Submit -->
                    <button type="submit" :disabled="form.processing || form.code.length < 6" class="submit-btn">
                        <svg v-if="form.processing" class="spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity=".25"/><path fill="currentColor" opacity=".75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span v-if="form.processing">{{ t('verification.verifying') }}</span>
                        <template v-else>
                            <span>{{ t('verification.verify') }}</span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        </template>
                    </button>
                </form>

                <!-- Resend -->
                <div class="resend-section">
                    <p>{{ t('verification.noCode') }}</p>
                    <button @click="resendCode" :disabled="resending" class="resend-btn">
                        <svg v-if="resending" class="spinner-small" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" opacity=".25"/><path fill="currentColor" opacity=".75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        {{ resending ? t('verification.sending') : t('verification.resend') }}
                    </button>
                    <p v-if="resendSuccess" class="resend-success">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ t('verification.codeSent') }}
                    </p>
                </div>

                <!-- Footer -->
                <div class="verify-footer">
                    <a href="/admin/login" class="back-link" @click.prevent="router.post('/admin/logout')">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        {{ t('verification.logout') }}
                    </a>
                    <p>&copy; {{ new Date().getFullYear() }} Mahubiri</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Hero -->
        <div class="verify-right">
            <div class="right-bg"></div>
            <div class="right-pattern"></div>
            <div class="right-glow right-glow-1"></div>
            <div class="right-glow right-glow-2"></div>

            <div class="right-content">
                <div class="right-badge">
                    <span class="pulse-dot"></span>
                    {{ t('verification.heroBadge') }}
                </div>
                <h2 class="right-title">
                    {{ t('verification.heroTitle1') }}<br>
                    <span>{{ t('verification.heroTitle2') }}</span>
                </h2>
                <p class="right-desc">{{ t('verification.heroDesc') }}</p>

                <div class="right-steps">
                    <div class="right-step done">
                        <div class="rs-dot">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div>
                            <h4>{{ t('verification.heroStep1') }}</h4>
                            <p>{{ t('verification.heroStep1Desc') }}</p>
                        </div>
                    </div>
                    <div class="right-step active">
                        <div class="rs-dot active-dot">2</div>
                        <div>
                            <h4>{{ t('verification.heroStep2') }}</h4>
                            <p>{{ t('verification.heroStep2Desc') }}</p>
                        </div>
                    </div>
                    <div class="right-step">
                        <div class="rs-dot">3</div>
                        <div>
                            <h4>{{ t('verification.heroStep3') }}</h4>
                            <p>{{ t('verification.heroStep3Desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ── Page layout ── */
.verify-page {
    display: flex; height: 100vh; background: #ffffff;
    overflow: hidden; font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
}

/* ── Left Panel ── */
.verify-left {
    width: 100%; max-width: 520px;
    display: flex; flex-direction: column; justify-content: center;
    padding: 40px 48px; position: relative; z-index: 10; background: #ffffff;
}
@media (min-width: 1024px) { .verify-left { width: 520px; flex-shrink: 0; } }
@media (max-width: 1023px) { .verify-left { max-width: 100%; margin: 0 auto; padding: 32px 24px; } }

.verify-form-wrapper {
    width: 100%; max-width: 400px; margin: 0 auto;
    animation: fadeUp .6s ease both;
}

/* Logo */
.verify-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
.verify-logo-icon { width: 44px; height: 44px; border-radius: 14px; object-fit: contain; box-shadow: 0 4px 12px rgba(107, 78, 175, 0.3); }
.verify-logo-title { font-size: 20px; font-weight: 700; color: #111827; letter-spacing: -0.3px; line-height: 1.2; }
.verify-logo-sub { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

/* Toolbar */
.verify-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.theme-toggle {
    display: flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 12px;
    background: #f3f4f6; border: 2px solid #e5e7eb; color: #6b7280; cursor: pointer; transition: all .25s;
}
.theme-toggle:hover { background: #e5e7eb; color: #6B4EAF; border-color: #6B4EAF; }

/* Step indicator */
.step-indicator { display: flex; align-items: center; gap: 8px; margin-bottom: 28px; }
.step { display: flex; align-items: center; gap: 6px; }
.step-dot {
    width: 26px; height: 26px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700;
    background: #f3f4f6; color: #9ca3af; border: 2px solid #e5e7eb; transition: all .3s;
}
.step-dot svg { width: 14px; height: 14px; }
.step.active .step-dot { background: #6B4EAF; color: #fff; border-color: #6B4EAF; }
.step.done .step-dot { background: #10b981; color: #fff; border-color: #10b981; }
.step span:last-child { font-size: 11px; font-weight: 600; color: #9ca3af; }
.step.active span:last-child { color: #6B4EAF; }
.step.done span:last-child { color: #10b981; }
.step-line { flex: 1; height: 2px; background: #e5e7eb; }
.step-line.active-line { background: #10b981; }

/* Heading */
.verify-heading { text-align: center; margin-bottom: 32px; }
.verify-icon-wrapper {
    display: inline-flex; align-items: center; justify-content: center;
    width: 56px; height: 56px; border-radius: 16px;
    background: linear-gradient(135deg, rgba(107,78,175,0.1) 0%, rgba(107,78,175,0.05) 100%);
    margin-bottom: 16px;
}
.verify-icon-wrapper svg { width: 28px; height: 28px; color: #6B4EAF; }
.verify-heading h2 { font-size: 22px; font-weight: 800; color: #111827; letter-spacing: -0.5px; margin-bottom: 8px; }
.verify-heading p { font-size: 14px; color: #6b7280; line-height: 1.6; }

/* Code inputs */
.verify-form { display: flex; flex-direction: column; gap: 16px; align-items: center; }
.code-inputs { display: flex; gap: 10px; justify-content: center; }
.code-digit {
    width: 48px; height: 56px; text-align: center;
    font-size: 22px; font-weight: 700; color: #111827;
    border: 2px solid #e5e7eb; border-radius: 14px;
    background: #f9fafb; outline: none; font-family: inherit;
    transition: all .25s;
}
.code-digit:focus { border-color: #6B4EAF; background: #fff; box-shadow: 0 0 0 4px rgba(107, 78, 175, 0.08); }
.code-digit.error { border-color: #ef4444; background: #fef2f2; }
.field-error { display: flex; align-items: center; gap: 5px; font-size: 13px; color: #ef4444; }
.field-error svg { width: 14px; height: 14px; flex-shrink: 0; }

/* Submit */
.submit-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    width: 100%; max-width: 320px; padding: 14px 24px;
    background: linear-gradient(135deg, #6B4EAF 0%, #5a3d96 100%);
    color: #fff; font-size: 14px; font-weight: 700;
    border: none; border-radius: 14px; cursor: pointer;
    transition: all .3s; box-shadow: 0 4px 16px rgba(107, 78, 175, 0.3);
    font-family: inherit; line-height: 1;
}
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(107, 78, 175, 0.35); }
.submit-btn:disabled { opacity: .5; cursor: not-allowed; transform: none !important; }
.submit-btn svg { width: 18px; height: 18px; }
.spinner { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Resend */
.resend-section { text-align: center; margin-top: 28px; }
.resend-section > p { font-size: 13px; color: #6b7280; margin-bottom: 8px; }
.resend-btn {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600; color: #6B4EAF;
    background: none; border: none; cursor: pointer; font-family: inherit;
    transition: color .2s;
}
.resend-btn:hover { color: #5a3d96; text-decoration: underline; }
.resend-btn:disabled { opacity: .5; cursor: not-allowed; }
.spinner-small { width: 14px; height: 14px; animation: spin .8s linear infinite; }
.resend-success {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    margin-top: 8px; font-size: 13px; color: #10b981; font-weight: 500;
}
.resend-success svg { width: 16px; height: 16px; }

/* Footer */
.verify-footer {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 32px; padding-top: 20px; border-top: 1px solid #f3f4f6;
}
.verify-footer p { font-size: 11px; color: #9ca3af; }
.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600; color: #6b7280; text-decoration: none; cursor: pointer;
}
.back-link:hover { color: #6B4EAF; }
.back-link svg { width: 14px; height: 14px; }

/* ── Right Panel ── */
.verify-right { display: none; flex: 1; position: relative; overflow: hidden; }
@media (min-width: 1024px) { .verify-right { display: flex; } }
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
.right-desc { font-size: 15px; color: rgba(255,255,255,0.6); line-height: 1.65; max-width: 440px; margin-bottom: 36px; }

/* Right steps */
.right-steps { display: flex; flex-direction: column; gap: 18px; }
.right-step {
    display: flex; align-items: flex-start; gap: 16px;
    padding: 16px 20px; border-radius: 14px;
    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);
    backdrop-filter: blur(8px);
}
.right-step.active { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.15); }
.right-step.done { opacity: .6; }
.rs-dot {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.08); border: 2px solid rgba(255,255,255,0.12);
}
.rs-dot svg { width: 16px; height: 16px; color: #fff; }
.right-step.done .rs-dot { background: rgba(16,185,129,0.3); border-color: #10b981; }
.rs-dot.active-dot { background: rgba(232,183,125,0.2); border-color: #E8B77D; color: #E8B77D; }
.right-step h4 { font-size: 14px; font-weight: 700; color: rgba(255,255,255,0.9); margin-bottom: 2px; }
.right-step p { font-size: 12px; color: rgba(255,255,255,0.5); line-height: 1.4; }

/* ── Animation ── */
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ── Dark Mode ── */
:root.dark .verify-page { background: #111827; }
:root.dark .verify-left { background: #111827; }
:root.dark .verify-logo-title { color: #f3f4f6; }
:root.dark .verify-heading h2 { color: #f3f4f6; }
:root.dark .verify-heading p { color: #9ca3af; }
:root.dark .verify-icon-wrapper { background: linear-gradient(135deg, rgba(139,111,207,0.15) 0%, rgba(139,111,207,0.08) 100%); }
:root.dark .code-digit { background: #1f2937; border-color: #374151; color: #f3f4f6; }
:root.dark .code-digit:focus { border-color: #8b6fcf; background: #1a1a2e; box-shadow: 0 0 0 4px rgba(139,111,207,0.15); }
:root.dark .step-dot { background: #1f2937; border-color: #374151; color: #6b7280; }
:root.dark .step.active .step-dot { background: #6B4EAF; color: #fff; border-color: #6B4EAF; }
:root.dark .step.done .step-dot { background: #10b981; color: #fff; border-color: #10b981; }
:root.dark .step-line { background: #374151; }
:root.dark .resend-section > p { color: #9ca3af; }
:root.dark .resend-btn { color: #a78bfa; }
:root.dark .resend-btn:hover { color: #c4b5fd; }
:root.dark .verify-footer { border-top-color: #1f2937; }
:root.dark .back-link { color: #9ca3af; }
:root.dark .back-link:hover { color: #a78bfa; }
:root.dark .theme-toggle { background: #1f2937; border-color: #374151; color: #9ca3af; }
:root.dark .theme-toggle:hover { background: #374151; color: #e8b77d; border-color: #e8b77d; }
:root.dark .right-bg { background: linear-gradient(135deg, #4a2d8a 0%, #2d1a5e 40%, #1a1035 100%); }
:root.dark .submit-btn { background: linear-gradient(135deg, #7c5fc4 0%, #6B4EAF 100%); box-shadow: 0 4px 16px rgba(124, 95, 196, 0.35); }
</style>
