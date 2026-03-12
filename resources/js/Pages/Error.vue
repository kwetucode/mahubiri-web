<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';

const { t } = useI18n();
const { theme, toggleTheme } = useTheme();

const props = defineProps({
    status: { type: Number, default: 404 },
});

const errorData = computed(() => {
    const map = {
        403: { icon: 'shield', key: '403' },
        404: { icon: 'search', key: '404' },
        419: { icon: 'clock', key: '419' },
        500: { icon: 'alert', key: '500' },
        503: { icon: 'tool', key: '503' },
    };
    return map[props.status] || map[404];
});
</script>

<template>
    <div class="error-page">
        <!-- Left Side -->
        <div class="error-left">
            <div class="error-wrapper">
                <!-- Logo -->
                <div class="error-logo">
                    <img src="/logo.png" alt="Mahubiri" class="error-logo-icon" />
                    <div>
                        <h1 class="error-logo-title">Mahubiri</h1>
                        <p class="error-logo-sub">Administration</p>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="error-toolbar">
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

                <!-- Error Code -->
                <div class="error-code-display">
                    <span class="error-code-number">{{ status }}</span>
                </div>

                <!-- Icon -->
                <div class="error-icon-wrapper">
                    <!-- 403 Shield -->
                    <svg v-if="errorData.icon === 'shield'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/>
                    </svg>
                    <!-- 404 Search -->
                    <svg v-else-if="errorData.icon === 'search'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/>
                    </svg>
                    <!-- 419 Clock -->
                    <svg v-else-if="errorData.icon === 'clock'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <!-- 500 Alert -->
                    <svg v-else-if="errorData.icon === 'alert'" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <!-- 503 Tool -->
                    <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>

                <!-- Heading -->
                <div class="error-heading">
                    <h2>{{ t(`errors.${errorData.key}.title`) }}</h2>
                    <p>{{ t(`errors.${errorData.key}.description`) }}</p>
                </div>

                <!-- Actions -->
                <div class="error-actions">
                    <a href="/admin/dashboard" class="action-btn primary-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span>{{ t('errors.goHome') }}</span>
                    </a>
                    <button @click="$inertia?.visit(window.history.length > 1 ? undefined : '/admin/dashboard') || window.history.back()" class="action-btn secondary-btn" onclick="window.history.back()">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                        </svg>
                        <span>{{ t('errors.goBack') }}</span>
                    </button>
                </div>

                <!-- Footer -->
                <div class="error-footer">
                    <p>&copy; {{ new Date().getFullYear() }} Mahubiri</p>
                </div>
            </div>
        </div>

        <!-- Right Side - Visual -->
        <div class="error-right">
            <div class="right-bg"></div>
            <div class="right-pattern"></div>
            <div class="right-glow right-glow-1"></div>
            <div class="right-glow right-glow-2"></div>

            <div class="right-content">
                <div class="right-badge">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ t(`errors.${errorData.key}.badge`) }}
                </div>

                <div class="right-big-code">{{ status }}</div>

                <h2 class="right-title">
                    {{ t(`errors.${errorData.key}.heroTitle1`) }}<br>
                    <span>{{ t(`errors.${errorData.key}.heroTitle2`) }}</span>
                </h2>
                <p class="right-desc">{{ t(`errors.${errorData.key}.heroDesc`) }}</p>

                <!-- Suggestions -->
                <div class="suggestions-list">
                    <div class="suggestion-item" v-for="i in 3" :key="i">
                        <div class="suggestion-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p>{{ t(`errors.${errorData.key}.suggestion${i}`) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:root { --primary: #6B4EAF; --primary-dark: #5a3d96; --accent: #E8B77D; }

.error-page { display: flex; height: 100vh; background: #fff; overflow: hidden; font-family: 'Instrument Sans', system-ui, sans-serif; }

/* Left */
.error-left { width: 100%; max-width: 520px; display: flex; flex-direction: column; padding: 40px 48px; background: #fff; overflow-y: auto; z-index: 10; }
@media (min-width: 1024px) { .error-left { width: 520px; flex-shrink: 0; } }
@media (max-width: 1023px) { .error-left { max-width: 100%; margin: 0 auto; padding: 32px 24px; } }
.error-wrapper { width: 100%; max-width: 400px; margin: auto; animation: fadeUp .6s ease both; }

/* Logo */
.error-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 36px; }
.error-logo-icon { width: 44px; height: 44px; border-radius: 14px; object-fit: contain; box-shadow: 0 4px 12px rgba(107,78,175,0.3); }
.error-logo-title { font-size: 20px; font-weight: 700; color: #111827; letter-spacing: -0.3px; line-height: 1.2; }
.error-logo-sub { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 600; }

/* Toolbar */
.error-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; }
.error-toolbar :deep(.relative > div[class*="absolute"]) { right: auto; left: 0; }
.theme-toggle { display: flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 12px; background: #f3f4f6; border: 2px solid #e5e7eb; color: #6b7280; cursor: pointer; transition: all .25s; }
.theme-toggle:hover { background: #e5e7eb; color: #6B4EAF; border-color: #6B4EAF; }
.theme-toggle svg { width: 18px; height: 18px; }

/* Error Code */
.error-code-display { margin-bottom: 20px; }
.error-code-number { font-size: 72px; font-weight: 900; letter-spacing: -3px; line-height: 1; background: linear-gradient(135deg, #6B4EAF 0%, #E8B77D 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

/* Icon */
.error-icon-wrapper { width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, rgba(107,78,175,0.1), rgba(107,78,175,0.05)); display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
.error-icon-wrapper svg { width: 28px; height: 28px; color: #6B4EAF; }

/* Heading */
.error-heading { margin-bottom: 32px; }
.error-heading h2 { font-size: 26px; font-weight: 800; color: #111827; letter-spacing: -0.5px; margin-bottom: 8px; }
.error-heading p { font-size: 14px; color: #6b7280; line-height: 1.7; }

/* Actions */
.error-actions { display: flex; flex-direction: column; gap: 12px; }
.action-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 14px 24px; font-size: 14px; font-weight: 700; border: none; border-radius: 14px; cursor: pointer; transition: all .3s; font-family: inherit; line-height: 1; text-decoration: none; }
.action-btn svg { width: 18px; height: 18px; }
.primary-btn { background: linear-gradient(135deg, #6B4EAF 0%, #5a3d96 100%); color: #fff; box-shadow: 0 4px 16px rgba(107,78,175,0.3); }
.primary-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(107,78,175,0.35); }
.secondary-btn { background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; }
.secondary-btn:hover { background: #e5e7eb; border-color: #d1d5db; }

/* Footer */
.error-footer { display: flex; justify-content: center; margin-top: 32px; padding-top: 20px; border-top: 1px solid #f3f4f6; }
.error-footer p { font-size: 11px; color: #9ca3af; }

/* ── Right Panel ── */
.error-right { display: none; flex: 1; position: relative; overflow: hidden; }
@media (min-width: 1024px) { .error-right { display: flex; } }

.right-bg { position: absolute; inset: 0; background: linear-gradient(135deg, #6B4EAF 0%, #4a2d8a 40%, #3a2070 100%); }
.right-pattern { position: absolute; inset: 0; opacity: .03; background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 28px 28px; }
.right-glow { position: absolute; border-radius: 50%; filter: blur(100px); }
.right-glow-1 { width: 500px; height: 500px; background: rgba(232,183,125,0.2); top: -15%; right: -10%; animation: floatGlow 15s ease-in-out infinite alternate; }
.right-glow-2 { width: 400px; height: 400px; background: rgba(156,125,199,0.25); bottom: -10%; left: -5%; animation: floatGlow 18s ease-in-out infinite alternate-reverse; }
@keyframes floatGlow { 0% { transform: translate(0,0) scale(1); } 50% { transform: translate(20px,-30px) scale(1.1); } 100% { transform: translate(-10px,15px) scale(.95); } }

.right-content { position: relative; z-index: 10; display: flex; flex-direction: column; justify-content: center; padding: 48px 52px; width: 100%; animation: fadeUp .8s ease .2s both; }

.right-badge { display: inline-flex; align-items: center; gap: 8px; align-self: flex-start; padding: 7px 16px; border-radius: 999px; background: rgba(255,255,255,0.1); backdrop-filter: blur(12px); color: rgba(255,255,255,0.9); font-size: 12px; font-weight: 600; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 28px; }

.right-big-code { font-size: clamp(100px, 12vw, 160px); font-weight: 900; color: rgba(255,255,255,0.06); line-height: 1; letter-spacing: -6px; margin-bottom: -20px; user-select: none; }

.right-title { font-size: clamp(28px,3vw,38px); font-weight: 800; color: #fff; line-height: 1.15; letter-spacing: -0.5px; margin-bottom: 14px; }
.right-title span { color: #E8B77D; }
.right-desc { font-size: 15px; color: rgba(255,255,255,0.6); line-height: 1.65; max-width: 440px; margin-bottom: 32px; }

/* Suggestions */
.suggestions-list { display: flex; flex-direction: column; gap: 12px; }
.suggestion-item { display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 14px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); transition: all .3s; }
.suggestion-item:hover { background: rgba(255,255,255,0.1); transform: translateX(4px); }
.suggestion-icon { width: 24px; height: 24px; flex-shrink: 0; }
.suggestion-icon svg { width: 20px; height: 20px; color: #34d399; }
.suggestion-item p { font-size: 13px; color: rgba(255,255,255,0.7); line-height: 1.4; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ── Dark Mode ── */
:root.dark .error-page { background: #111827; }
:root.dark .error-left { background: #111827; }
:root.dark .error-logo-title { color: #f3f4f6; }
:root.dark .error-logo-sub { color: #6b7280; }
:root.dark .error-code-number { background: linear-gradient(135deg, #a78bfa 0%, #E8B77D 100%); -webkit-background-clip: text; background-clip: text; }
:root.dark .error-icon-wrapper { background: linear-gradient(135deg, rgba(139,111,207,0.2), rgba(139,111,207,0.1)); }
:root.dark .error-icon-wrapper svg { color: #a78bfa; }
:root.dark .error-heading h2 { color: #f3f4f6; }
:root.dark .error-heading p { color: #9ca3af; }
:root.dark .primary-btn { background: linear-gradient(135deg, #7c5fc4 0%, #6B4EAF 100%); box-shadow: 0 4px 16px rgba(124,95,196,0.35); }
:root.dark .primary-btn:hover { box-shadow: 0 8px 24px rgba(124,95,196,0.4); }
:root.dark .secondary-btn { background: #1f2937; color: #d1d5db; border-color: #374151; }
:root.dark .secondary-btn:hover { background: #374151; border-color: #4b5563; }
:root.dark .error-footer { border-top-color: #1f2937; }
:root.dark .error-footer p { color: #6b7280; }
:root.dark .theme-toggle { background: #1f2937; border-color: #374151; color: #9ca3af; }
:root.dark .theme-toggle:hover { background: #374151; color: #e8b77d; border-color: #e8b77d; }
:root.dark .right-bg { background: linear-gradient(135deg, #4a2d8a 0%, #2d1a5e 40%, #1a1035 100%); }
</style>
