<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/composables/useTheme';
import LocaleSwitcher from '@/Components/LocaleSwitcher.vue';

const { t } = useI18n();
const { theme, toggleTheme } = useTheme();

const applyWelcomeClass = () => {
    if (theme.value === 'dark') {
        document.body.classList.add('welcome-dark');
    } else {
        document.body.classList.remove('welcome-dark');
    }
};

onMounted(() => {
    document.body.classList.add('welcome-body');
    applyWelcomeClass();
});
onUnmounted(() => {
    document.body.classList.remove('welcome-body', 'welcome-dark');
});

watch(theme, () => {
    applyWelcomeClass();
});

const features = computed(() => [
    { icon: '🎧', title: t('welcome.featureListenTitle'), desc: t('welcome.featureListenDesc'), bg: 'rgba(139,111,207,.1)' },
    { icon: '⭐', title: t('welcome.featureFavTitle'), desc: t('welcome.featureFavDesc'), bg: 'rgba(232,183,125,.15)' },
    { icon: '👤', title: t('welcome.featureProfileTitle'), desc: t('welcome.featureProfileDesc'), bg: 'rgba(59,130,246,.1)' },
    { icon: '🔔', title: t('welcome.featureNotifTitle'), desc: t('welcome.featureNotifDesc'), bg: 'rgba(244,63,94,.08)' },
    { icon: '🔍', title: t('welcome.featureSearchTitle'), desc: t('welcome.featureSearchDesc'), bg: 'rgba(16,185,129,.1)' },
    { icon: '🌐', title: t('welcome.featureLangTitle'), desc: t('welcome.featureLangDesc'), bg: 'rgba(99,102,241,.1)' },
]);

const handleImageError = (e) => {
    const img = e.target;
    if (img.parentElement) {
        img.parentElement.innerHTML = `<div style="display:flex;align-items:center;justify-content:center;height:320px;color:#9ca3af;font-size:14px;">${t('welcome.previewUnavailable')}</div>`;
    }
};
</script>

<template>
    <Head :title="t('welcome.title')" />

    <div class="welcome-page">
        <!-- Animated background -->
        <div class="bg-blobs">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>
        </div>

        <div class="page">
        <div class="container">

            <!-- Nav -->
            <nav class="nav">
                <a href="/" class="nav-logo">
                    <img src="/logo.png" alt="Mahubiri" style="width:32px;height:32px;border-radius:8px;object-fit:contain;" />
                    Mahubiri
                </a>

                <div class="nav-actions">
                    <!-- Language switcher -->
                    <LocaleSwitcher />

                    <!-- Theme toggle -->
                    <button @click="toggleTheme" class="theme-toggle" :title="theme === 'dark' ? 'Mode clair' : 'Mode sombre'">
                        <!-- Sun icon (shown in dark mode) -->
                        <svg v-if="theme === 'dark'" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <!-- Moon icon (shown in light mode) -->
                        <svg v-else width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                        </svg>
                    </button>

                    <span class="nav-badge">
                        <span style="width:5px;height:5px;border-radius:50%;background:var(--accent);"></span>
                        {{ t('welcome.badge') }}
                    </span>
                    <Link href="/admin/login" class="btn btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        {{ t('welcome.login') }}
                    </Link>
                </div>
            </nav>

            <!-- Hero -->
            <section class="hero">
                <div class="hero-pill">
                    <span class="dot"></span>
                    {{ t('welcome.heroPill') }}
                </div>
                <h1>
                    {{ t('welcome.heroTitle1') }}<br><span class="gradient">{{ t('welcome.heroTitle2') }}</span>
                </h1>
                <p>{{ t('welcome.heroDescription') }}</p>
                <div class="hero-cta">
                    <Link href="/admin/login" class="btn btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        {{ t('welcome.adminSpace') }}
                    </Link>
                    <a href="#features" class="btn btn-ghost">
                        {{ t('welcome.learnMore') }}
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>

                <!-- Stats ribbon -->
                <div class="stats">
                    <div class="stat">
                        <div class="stat-val">1000+</div>
                        <div class="stat-label">{{ t('welcome.statSermons') }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-val">50+</div>
                        <div class="stat-label">{{ t('welcome.statPreachers') }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-val">3</div>
                        <div class="stat-label">{{ t('welcome.statLanguages') }}</div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section class="features" id="features">
                <div class="section-header">
                    <h2>{{ t('welcome.featuresTitle') }}</h2>
                    <p>{{ t('welcome.featuresSubtitle') }}</p>
                </div>

                <div class="features-grid">
                    <div v-for="(f, i) in features" :key="i" class="feature-card">
                        <div class="feature-icon" :style="{ background: f.bg }">{{ f.icon }}</div>
                        <h3>{{ f.title }}</h3>
                        <p>{{ f.desc }}</p>
                    </div>
                </div>
            </section>

            <!-- Hero Image -->
            <section class="hero-image-section">
                <div class="section-header">
                    <h2>{{ t('welcome.previewTitle') }}</h2>
                    <p>{{ t('welcome.previewSubtitle') }}</p>
                </div>
                <div class="hero-image-wrapper">
                    <img src="/hero.png" alt="Mahubiri"
                         @error="handleImageError">
                </div>
            </section>

            <!-- CTA Admin -->
            <section class="cta-section">
                <div class="cta-box">
                    <h2>{{ t('welcome.ctaTitle') }}</h2>
                    <p>{{ t('welcome.ctaDescription') }}</p>
                    <Link href="/admin/login" class="btn btn-white" style="position:relative;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        {{ t('welcome.ctaButton') }}
                    </Link>
                </div>
            </section>

            <!-- Contact -->
            <section class="contact">
                <div class="section-header">
                    <h2>{{ t('welcome.contactTitle') }}</h2>
                    <p>{{ t('welcome.contactSubtitle') }}</p>
                </div>
                <div class="contact-grid">
                    <a href="mailto:kwetucode@gmail.com" class="contact-card">
                        <div class="contact-icon">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="contact-label">{{ t('welcome.contactEmail') }}</div>
                            <div class="contact-value">kwetucode@gmail.com</div>
                        </div>
                    </a>
                    <a href="tel:+243971330007" class="contact-card">
                        <div class="contact-icon">
                            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="contact-label">{{ t('welcome.contactPhone') }}</div>
                            <div class="contact-value">+243 971 330 007</div>
                        </div>
                    </a>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <p>&copy; {{ new Date().getFullYear() }} <span>Mahubiri</span>. {{ t('welcome.footerRights') }}</p>
                <p class="sub">{{ t('welcome.footerBeta') }} — v1.0.16</p>
            </div>
        </footer>
    </div>
    </div>
</template>

<style>
    /* Body styles — applied/removed via lifecycle hooks */
    body.welcome-body {
        font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
        background: #FAF9FE;
        color: #1a1a2e;
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
        transition: background .3s, color .3s;
    }

    body.welcome-dark {
        background: #0f0e17;
        color: #e0dde7;
    }

    .welcome-page {
        --wp-primary: #6B4EAF;
        --wp-primary-light: #9C7DC7;
        --wp-accent: #E8B77D;
        --wp-bg: #FAF9FE;
        --wp-text: #1a1a2e;
        --wp-muted: #6b7280;
        --wp-card: #ffffff;
        --wp-border: rgba(107, 78, 175, 0.08);
    }

    body.welcome-dark .welcome-page {
        --wp-primary: #9C7DC7;
        --wp-primary-light: #b89ddb;
        --wp-accent: #E8B77D;
        --wp-bg: #0f0e17;
        --wp-text: #e0dde7;
        --wp-muted: #9ca3af;
        --wp-card: #1a1928;
        --wp-border: rgba(156, 125, 199, 0.12);
    }

    body.welcome-dark .welcome-page .hero h1 { color: #f0ecf7; }
    body.welcome-dark .welcome-page .section-header h2 { color: #f0ecf7; }
    body.welcome-dark .welcome-page .feature-card h3 { color: #f0ecf7; }
    body.welcome-dark .welcome-page .contact-value { color: #f0ecf7; }
    body.welcome-dark .welcome-page .stat-val { color: var(--wp-primary); }
    body.welcome-dark .welcome-page .nav-logo { color: var(--wp-primary); }
    body.welcome-dark .welcome-page .btn-primary { background: var(--wp-primary); box-shadow: 0 4px 14px rgba(156, 125, 199, 0.3); }
    body.welcome-dark .welcome-page .btn-primary:hover { box-shadow: 0 8px 24px rgba(156, 125, 199, 0.35); }
    body.welcome-dark .welcome-page .btn-ghost { color: var(--wp-muted); border-color: rgba(156, 125, 199, 0.2); }
    body.welcome-dark .welcome-page .btn-ghost:hover { border-color: var(--wp-primary); color: var(--wp-primary); background: rgba(156, 125, 199, 0.06); }
    body.welcome-dark .welcome-page .btn-white { background: #1a1928; color: var(--wp-primary); }
    body.welcome-dark .welcome-page .blob { opacity: .15; }
    body.welcome-dark .welcome-page .footer { border-top-color: var(--wp-border); }

    .welcome-page .theme-toggle {
        display: flex; align-items: center; justify-content: center;
        width: 38px; height: 38px; border-radius: 12px; border: 1.5px solid var(--wp-border);
        background: var(--wp-card); color: var(--wp-muted); cursor: pointer;
        transition: all .3s cubic-bezier(.4,0,.2,1);
    }
    .welcome-page .theme-toggle:hover {
        border-color: var(--wp-primary); color: var(--wp-primary);
        background: rgba(107, 78, 175, 0.06);
    }

    .welcome-page .bg-blobs { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
    .welcome-page .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: .35; animation: wpBlobFloat 20s ease-in-out infinite alternate; }
    .welcome-page .blob-1 { width: 500px; height: 500px; background: var(--wp-primary); top: -10%; left: -8%; animation-delay: 0s; }
    .welcome-page .blob-2 { width: 400px; height: 400px; background: var(--wp-accent); bottom: -5%; right: -5%; animation-delay: -7s; }
    .welcome-page .blob-3 { width: 300px; height: 300px; background: var(--wp-primary-light); top: 40%; right: 20%; animation-delay: -14s; opacity: .2; }

    @keyframes wpBlobFloat {
        0%   { transform: translate(0, 0) scale(1); }
        33%  { transform: translate(30px, -40px) scale(1.05); }
        66%  { transform: translate(-20px, 20px) scale(0.95); }
        100% { transform: translate(10px, -10px) scale(1.02); }
    }

    .welcome-page .page { position: relative; z-index: 1; }
    .welcome-page .container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

    .welcome-page .nav { display: flex; align-items: center; justify-content: space-between; padding: 20px 0; }
    .welcome-page .nav-logo { display: flex; align-items: center; gap: 10px; font-size: 22px; font-weight: 700; color: var(--wp-primary); text-decoration: none; }
    .welcome-page .nav-logo img { width: 32px; height: 32px; }
    .welcome-page .nav-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 999px; font-size: 11px; font-weight: 600; background: rgba(107, 78, 175, 0.08); color: var(--wp-primary); letter-spacing: 0.5px; text-transform: uppercase; }
    .welcome-page .nav-actions { display: flex; align-items: center; gap: 12px; }

    .welcome-page .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; transition: all 0.3s cubic-bezier(.4,0,.2,1); cursor: pointer; border: none; line-height: 1; }
    .welcome-page .btn-primary { background: var(--wp-primary); color: #fff; box-shadow: 0 4px 14px rgba(107, 78, 175, 0.3); }
    .welcome-page .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(107, 78, 175, 0.35); }
    .welcome-page .btn-ghost { background: transparent; color: var(--wp-muted); border: 1.5px solid rgba(107, 78, 175, 0.15); }
    .welcome-page .btn-ghost:hover { border-color: var(--wp-primary); color: var(--wp-primary); background: rgba(107, 78, 175, 0.04); }
    .welcome-page .btn svg { width: 16px; height: 16px; flex-shrink: 0; }

    .welcome-page .hero { text-align: center; padding: 80px 0 60px; }
    .welcome-page .hero-pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 18px; border-radius: 999px; background: rgba(107, 78, 175, 0.07); color: var(--wp-primary); font-size: 13px; font-weight: 600; margin-bottom: 28px; animation: wpFadeUp .8s ease both; }
    .welcome-page .hero-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--wp-accent); }
    .welcome-page .hero h1 { font-size: clamp(40px, 7vw, 72px); font-weight: 700; line-height: 1.08; letter-spacing: -1.5px; margin-bottom: 20px; animation: wpFadeUp .8s ease .1s both; }
    .welcome-page .hero h1 .gradient { background: linear-gradient(135deg, var(--wp-primary) 0%, var(--wp-primary-light) 50%, var(--wp-accent) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .welcome-page .hero p { font-size: 18px; color: var(--wp-muted); max-width: 540px; margin: 0 auto 36px; line-height: 1.65; animation: wpFadeUp .8s ease .2s both; }
    .welcome-page .hero-cta { display: flex; align-items: center; justify-content: center; gap: 14px; flex-wrap: wrap; animation: wpFadeUp .8s ease .35s both; }

    .welcome-page .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: var(--wp-border); border-radius: 20px; overflow: hidden; margin: 60px auto 0; max-width: 680px; box-shadow: 0 1px 3px rgba(0,0,0,.04); animation: wpFadeUp .8s ease .5s both; }
    .welcome-page .stat { background: var(--wp-card); padding: 28px 20px; text-align: center; }
    .welcome-page .stat-val { font-size: 28px; font-weight: 700; color: var(--wp-primary); }
    .welcome-page .stat-label { font-size: 12px; color: var(--wp-muted); margin-top: 4px; font-weight: 500; }

    .welcome-page .features { padding: 100px 0 80px; }
    .welcome-page .section-header { text-align: center; margin-bottom: 56px; }
    .welcome-page .section-header h2 { font-size: 32px; font-weight: 700; margin-bottom: 12px; letter-spacing: -0.5px; }
    .welcome-page .section-header p { font-size: 16px; color: var(--wp-muted); max-width: 480px; margin: 0 auto; }

    .welcome-page .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    .welcome-page .feature-card { padding: 32px 28px; border-radius: 20px; background: var(--wp-card); border: 1px solid var(--wp-border); transition: all .35s cubic-bezier(.4,0,.2,1); position: relative; overflow: hidden; }
    .welcome-page .feature-card::after { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(107,78,175,.03), transparent); opacity: 0; transition: opacity .35s; }
    .welcome-page .feature-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(107, 78, 175, 0.08); border-color: rgba(107, 78, 175, 0.15); }
    .welcome-page .feature-card:hover::after { opacity: 1; }
    .welcome-page .feature-icon { width: 48px; height: 48px; border-radius: 14px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; font-size: 24px; position: relative; z-index: 1; }
    .welcome-page .feature-card h3 { font-size: 17px; font-weight: 700; margin-bottom: 8px; position: relative; z-index: 1; }
    .welcome-page .feature-card p { font-size: 14px; color: var(--wp-muted); line-height: 1.65; position: relative; z-index: 1; }

    .welcome-page .hero-image-section { padding: 0 0 100px; }
    .welcome-page .hero-image-wrapper { max-width: 960px; margin: 0 auto; border-radius: 24px; overflow: hidden; transition: all .5s cubic-bezier(.4,0,.2,1); background: var(--wp-card); border: 1px solid var(--wp-border); box-shadow: 0 4px 24px rgba(0,0,0,.06); }
    .welcome-page .hero-image-wrapper:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,.1); }
    body.welcome-dark .welcome-page .hero-image-wrapper { box-shadow: 0 4px 24px rgba(0,0,0,.3); }
    body.welcome-dark .welcome-page .hero-image-wrapper:hover { box-shadow: 0 16px 40px rgba(0,0,0,.4); }
    .welcome-page .hero-image-wrapper img { width: 100%; height: auto; display: block; object-fit: cover; }

    .welcome-page .cta-section { padding: 80px 0; }
    .welcome-page .cta-box { text-align: center; padding: 60px 40px; border-radius: 28px; position: relative; overflow: hidden; background: linear-gradient(135deg, var(--wp-primary), #8B6FCF); color: #fff; }
    .welcome-page .cta-box::before { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.1), transparent 60%); }
    .welcome-page .cta-box h2 { font-size: 30px; font-weight: 700; margin-bottom: 14px; position: relative; }
    .welcome-page .cta-box p { font-size: 16px; opacity: .85; margin-bottom: 32px; max-width: 420px; margin-left: auto; margin-right: auto; position: relative; }
    .welcome-page .btn-white { background: #fff; color: var(--wp-primary); font-weight: 700; box-shadow: 0 4px 14px rgba(0,0,0,.1); }
    .welcome-page .btn-white:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }

    .welcome-page .contact { padding: 0 0 80px; }
    .welcome-page .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 700px; margin: 0 auto; }
    .welcome-page .contact-card { display: flex; align-items: center; gap: 16px; padding: 24px; border-radius: 18px; background: var(--wp-card); border: 1px solid var(--wp-border); text-decoration: none; color: inherit; transition: all .3s cubic-bezier(.4,0,.2,1); }
    .welcome-page .contact-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(107, 78, 175, 0.08); border-color: rgba(107, 78, 175, 0.2); }
    .welcome-page .contact-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: rgba(107, 78, 175, 0.08); color: var(--wp-primary); transition: all .3s; }
    .welcome-page .contact-card:hover .contact-icon { background: var(--wp-primary); color: #fff; }
    .welcome-page .contact-label { font-size: 11px; color: var(--wp-muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
    .welcome-page .contact-value { font-size: 15px; font-weight: 600; margin-top: 2px; }

    .welcome-page .footer { text-align: center; padding: 40px 0; font-size: 13px; color: var(--wp-muted); border-top: 1px solid var(--wp-border); }
    .welcome-page .footer span { font-weight: 600; color: var(--wp-primary); }
    .welcome-page .footer .sub { font-size: 11px; margin-top: 8px; opacity: .7; }

    @keyframes wpFadeUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .welcome-page .hero { padding: 50px 0 40px; }
        .welcome-page .stats { grid-template-columns: repeat(3, 1fr); max-width: 100%; }
        .welcome-page .stat { padding: 20px 12px; }
        .welcome-page .stat-val { font-size: 22px; }
        .welcome-page .hero-image-wrapper { border-radius: 16px; }
        .welcome-page .contact-grid { grid-template-columns: 1fr; }
        .welcome-page .nav-badge { display: none; }
        .welcome-page .cta-box { padding: 40px 24px; }
        .welcome-page .features-grid { grid-template-columns: 1fr; }
    }
</style>
