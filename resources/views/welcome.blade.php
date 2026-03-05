<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('welcome.title') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            :root {
                --primary: #6B4EAF;
                --primary-light: #9C7DC7;
                --accent: #E8B77D;
                --bg: #FAF9FE;
                --text: #1a1a2e;
                --muted: #6b7280;
                --card: #ffffff;
                --border: rgba(107, 78, 175, 0.08);
            }

            body {
                font-family: 'Instrument Sans', system-ui, -apple-system, sans-serif;
                background: var(--bg);
                color: var(--text);
                min-height: 100vh;
                overflow-x: hidden;
                -webkit-font-smoothing: antialiased;
            }

            /* ── Animated background blobs ── */
            .bg-blobs {
                position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden;
            }
            .blob {
                position: absolute; border-radius: 50%; filter: blur(80px); opacity: .35;
                animation: blobFloat 20s ease-in-out infinite alternate;
            }
            .blob-1 { width: 500px; height: 500px; background: var(--primary); top: -10%; left: -8%; animation-delay: 0s; }
            .blob-2 { width: 400px; height: 400px; background: var(--accent); bottom: -5%; right: -5%; animation-delay: -7s; }
            .blob-3 { width: 300px; height: 300px; background: var(--primary-light); top: 40%; right: 20%; animation-delay: -14s; opacity: .2; }

            @keyframes blobFloat {
                0%   { transform: translate(0, 0) scale(1); }
                33%  { transform: translate(30px, -40px) scale(1.05); }
                66%  { transform: translate(-20px, 20px) scale(0.95); }
                100% { transform: translate(10px, -10px) scale(1.02); }
            }

            /* ── Layout ── */
            .page { position: relative; z-index: 1; }
            .container { max-width: 1100px; margin: 0 auto; padding: 0 24px; }

            /* ── Nav ── */
            .nav {
                display: flex; align-items: center; justify-content: space-between;
                padding: 20px 0;
            }
            .nav-logo {
                display: flex; align-items: center; gap: 10px;
                font-size: 22px; font-weight: 700; color: var(--primary);
                text-decoration: none;
            }
            .nav-logo svg { width: 32px; height: 32px; }
            .nav-badge {
                display: inline-flex; align-items: center; gap: 6px;
                padding: 5px 14px; border-radius: 999px; font-size: 11px; font-weight: 600;
                background: rgba(107, 78, 175, 0.08); color: var(--primary);
                letter-spacing: 0.5px; text-transform: uppercase;
            }
            .nav-actions { display: flex; align-items: center; gap: 12px; }
            .btn {
                display: inline-flex; align-items: center; gap: 8px;
                padding: 10px 22px; border-radius: 12px; font-size: 14px; font-weight: 600;
                text-decoration: none; transition: all 0.3s cubic-bezier(.4,0,.2,1);
                cursor: pointer; border: none; line-height: 1;
            }
            .btn-primary {
                background: var(--primary); color: #fff;
                box-shadow: 0 4px 14px rgba(107, 78, 175, 0.3);
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 24px rgba(107, 78, 175, 0.35);
            }
            .btn-ghost {
                background: transparent; color: var(--muted);
                border: 1.5px solid rgba(107, 78, 175, 0.15);
            }
            .btn-ghost:hover {
                border-color: var(--primary); color: var(--primary);
                background: rgba(107, 78, 175, 0.04);
            }
            .btn svg { width: 16px; height: 16px; flex-shrink: 0; }

            /* ── Hero ── */
            .hero {
                text-align: center; padding: 80px 0 60px;
            }
            .hero-pill {
                display: inline-flex; align-items: center; gap: 8px;
                padding: 6px 18px; border-radius: 999px;
                background: rgba(107, 78, 175, 0.07); color: var(--primary);
                font-size: 13px; font-weight: 600; margin-bottom: 28px;
                animation: fadeUp .8s ease both;
            }
            .hero-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); }
            .hero h1 {
                font-size: clamp(40px, 7vw, 72px); font-weight: 700;
                line-height: 1.08; letter-spacing: -1.5px; margin-bottom: 20px;
                animation: fadeUp .8s ease .1s both;
            }
            .hero h1 .gradient {
                background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 50%, var(--accent) 100%);
                -webkit-background-clip: text; -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .hero p {
                font-size: 18px; color: var(--muted); max-width: 540px; margin: 0 auto 36px;
                line-height: 1.65; animation: fadeUp .8s ease .2s both;
            }
            .hero-cta {
                display: flex; align-items: center; justify-content: center; gap: 14px; flex-wrap: wrap;
                animation: fadeUp .8s ease .35s both;
            }

            /* ── Stats ribbon ── */
            .stats {
                display: grid; grid-template-columns: repeat(3, 1fr);
                gap: 1px; background: var(--border); border-radius: 20px;
                overflow: hidden; margin: 60px auto 0; max-width: 680px;
                box-shadow: 0 1px 3px rgba(0,0,0,.04);
                animation: fadeUp .8s ease .5s both;
            }
            .stat {
                background: var(--card); padding: 28px 20px; text-align: center;
            }
            .stat-val { font-size: 28px; font-weight: 700; color: var(--primary); }
            .stat-label { font-size: 12px; color: var(--muted); margin-top: 4px; font-weight: 500; }

            /* ── Features ── */
            .features { padding: 100px 0 80px; }
            .section-header { text-align: center; margin-bottom: 56px; }
            .section-header h2 { font-size: 32px; font-weight: 700; margin-bottom: 12px; letter-spacing: -0.5px; }
            .section-header p { font-size: 16px; color: var(--muted); max-width: 480px; margin: 0 auto; }

            .features-grid {
                display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
            }
            .feature-card {
                padding: 32px 28px; border-radius: 20px;
                background: var(--card); border: 1px solid var(--border);
                transition: all .35s cubic-bezier(.4,0,.2,1);
                position: relative; overflow: hidden;
            }
            .feature-card::after {
                content: ''; position: absolute; inset: 0;
                background: linear-gradient(135deg, rgba(107,78,175,.03), transparent);
                opacity: 0; transition: opacity .35s;
            }
            .feature-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 20px 40px rgba(107, 78, 175, 0.08);
                border-color: rgba(107, 78, 175, 0.15);
            }
            .feature-card:hover::after { opacity: 1; }
            .feature-icon {
                width: 48px; height: 48px; border-radius: 14px; margin-bottom: 20px;
                display: flex; align-items: center; justify-content: center;
                font-size: 24px; position: relative; z-index: 1;
            }
            .feature-card h3 {
                font-size: 17px; font-weight: 700; margin-bottom: 8px;
                position: relative; z-index: 1;
            }
            .feature-card p {
                font-size: 14px; color: var(--muted); line-height: 1.65;
                position: relative; z-index: 1;
            }

            /* ── Hero Image ── */
            .hero-image-section { padding: 0 0 100px; }
            .hero-image-wrapper {
                max-width: 960px; margin: 0 auto;
                border-radius: 24px; overflow: hidden;
                transition: transform .5s cubic-bezier(.4,0,.2,1);
            }
            .hero-image-wrapper:hover {
                transform: translateY(-6px);
            }
            .hero-image-wrapper img {
                width: 100%; height: auto; display: block;
                object-fit: cover;
            }

            /* ── CTA ── */
            .cta-section {
                padding: 80px 0;
            }
            .cta-box {
                text-align: center; padding: 60px 40px;
                border-radius: 28px; position: relative; overflow: hidden;
                background: linear-gradient(135deg, var(--primary), #8B6FCF);
                color: #fff;
            }
            .cta-box::before {
                content: ''; position: absolute; inset: 0;
                background: radial-gradient(circle at 30% 50%, rgba(255,255,255,0.1), transparent 60%);
            }
            .cta-box h2 { font-size: 30px; font-weight: 700; margin-bottom: 14px; position: relative; }
            .cta-box p { font-size: 16px; opacity: .85; margin-bottom: 32px; max-width: 420px; margin-left: auto; margin-right: auto; position: relative; }
            .btn-white {
                background: #fff; color: var(--primary); font-weight: 700;
                box-shadow: 0 4px 14px rgba(0,0,0,.1);
            }
            .btn-white:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }

            /* ── Contact ── */
            .contact { padding: 0 0 80px; }
            .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 700px; margin: 0 auto; }
            .contact-card {
                display: flex; align-items: center; gap: 16px;
                padding: 24px; border-radius: 18px;
                background: var(--card); border: 1px solid var(--border);
                text-decoration: none; color: inherit;
                transition: all .3s cubic-bezier(.4,0,.2,1);
            }
            .contact-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 12px 32px rgba(107, 78, 175, 0.08);
                border-color: rgba(107, 78, 175, 0.2);
            }
            .contact-icon {
                width: 48px; height: 48px; border-radius: 14px;
                display: flex; align-items: center; justify-content: center;
                background: rgba(107, 78, 175, 0.08); color: var(--primary);
                transition: all .3s;
            }
            .contact-card:hover .contact-icon { background: var(--primary); color: #fff; }
            .contact-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
            .contact-value { font-size: 15px; font-weight: 600; margin-top: 2px; }

            /* ── Footer ── */
            .footer {
                text-align: center; padding: 40px 0; font-size: 13px; color: var(--muted);
                border-top: 1px solid var(--border);
            }
            .footer span { font-weight: 600; color: var(--primary); }
            .footer .sub { font-size: 11px; margin-top: 8px; opacity: .7; }

            /* ── Animations ── */
            @keyframes fadeUp {
                from { opacity: 0; transform: translateY(24px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* ── Responsive ── */
            @media (max-width: 768px) {
                .hero { padding: 50px 0 40px; }
                .stats { grid-template-columns: repeat(3, 1fr); max-width: 100%; }
                .stat { padding: 20px 12px; }
                .stat-val { font-size: 22px; }
                .hero-image-wrapper { border-radius: 16px; }
                .contact-grid { grid-template-columns: 1fr; }
                .nav-badge { display: none; }
                .cta-box { padding: 40px 24px; }
                .features-grid { grid-template-columns: 1fr; }
            }

            /* ── Lang switcher ── */
            .lang-switcher {
                display: flex; align-items: center; gap: 4px;
                padding: 3px; border-radius: 10px;
                background: rgba(107, 78, 175, 0.06); border: 1px solid rgba(107, 78, 175, 0.1);
            }
            .lang-btn {
                padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;
                color: var(--muted); text-decoration: none; transition: all .25s;
                border: none; background: transparent; cursor: pointer;
            }
            .lang-btn:hover { color: var(--primary); }
            .lang-btn.active {
                background: var(--primary); color: #fff;
                box-shadow: 0 2px 8px rgba(107, 78, 175, 0.25);
            }
        </style>
    </head>
    <body>
        <!-- Animated background -->
        <div class="bg-blobs">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>
        </div>

        <div class="page">
            <div class="container">

                {{-- ── Nav ── --}}
                <nav class="nav">
                    <a href="/" class="nav-logo">
                        <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="32" height="32" rx="8" fill="currentColor" opacity=".1"/>
                            <path d="M16 6L10 11v10h4v-6h4v6h4V11L16 6z" fill="currentColor"/>
                            <circle cx="16" cy="14" r="2" fill="white"/>
                        </svg>
                        Mahubiri
                    </a>

                    <div class="nav-actions">
                        {{-- Language switcher --}}
                        <div class="lang-switcher">
                            <a href="?lang=fr" class="lang-btn {{ app()->getLocale() === 'fr' ? 'active' : '' }}">FR</a>
                            <a href="?lang=en" class="lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                            <a href="?lang=sw" class="lang-btn {{ app()->getLocale() === 'sw' ? 'active' : '' }}">SW</a>
                        </div>

                        <span class="nav-badge">
                            <span style="width:5px;height:5px;border-radius:50%;background:var(--accent);"></span>
                            {{ __('welcome.badge') }}
                        </span>
                        <a href="/admin/login" class="btn btn-primary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                                <polyline points="10 17 15 12 10 7"/>
                                <line x1="15" y1="12" x2="3" y2="12"/>
                            </svg>
                            {{ __('welcome.login') }}
                        </a>
                    </div>
                </nav>

                {{-- ── Hero ── --}}
                <section class="hero">
                    <div class="hero-pill">
                        <span class="dot"></span>
                        {{ __('welcome.heroPill') }}
                    </div>
                    <h1>
                        {{ __('welcome.heroTitle1') }}<br><span class="gradient">{{ __('welcome.heroTitle2') }}</span>
                    </h1>
                    <p>
                        {{ __('welcome.heroDescription') }}
                    </p>
                    <div class="hero-cta">
                        <a href="/admin/login" class="btn btn-primary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                            {{ __('welcome.adminSpace') }}
                        </a>
                        <a href="#features" class="btn btn-ghost">
                            {{ __('welcome.learnMore') }}
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </a>
                    </div>

                    {{-- Stats ribbon --}}
                    <div class="stats">
                        <div class="stat">
                            <div class="stat-val">1000+</div>
                            <div class="stat-label">{{ __('welcome.statSermons') }}</div>
                        </div>
                        <div class="stat">
                            <div class="stat-val">50+</div>
                            <div class="stat-label">{{ __('welcome.statPreachers') }}</div>
                        </div>
                        <div class="stat">
                            <div class="stat-val">3</div>
                            <div class="stat-label">{{ __('welcome.statLanguages') }}</div>
                        </div>
                    </div>
                </section>

                {{-- ── Features ── --}}
                <section class="features" id="features">
                    <div class="section-header">
                        <h2>{{ __('welcome.featuresTitle') }}</h2>
                        <p>{{ __('welcome.featuresSubtitle') }}</p>
                    </div>

                    <div class="features-grid">
                        @php
                            $features = [
                                ['icon' => '🎧', 'title' => __('welcome.featureListenTitle'), 'desc' => __('welcome.featureListenDesc'), 'bg' => 'rgba(139,111,207,.1)'],
                                ['icon' => '⭐', 'title' => __('welcome.featureFavTitle'), 'desc' => __('welcome.featureFavDesc'), 'bg' => 'rgba(232,183,125,.15)'],
                                ['icon' => '👤', 'title' => __('welcome.featureProfileTitle'), 'desc' => __('welcome.featureProfileDesc'), 'bg' => 'rgba(59,130,246,.1)'],
                                ['icon' => '🔔', 'title' => __('welcome.featureNotifTitle'), 'desc' => __('welcome.featureNotifDesc'), 'bg' => 'rgba(244,63,94,.08)'],
                                ['icon' => '🔍', 'title' => __('welcome.featureSearchTitle'), 'desc' => __('welcome.featureSearchDesc'), 'bg' => 'rgba(16,185,129,.1)'],
                                ['icon' => '🌐', 'title' => __('welcome.featureLangTitle'), 'desc' => __('welcome.featureLangDesc'), 'bg' => 'rgba(99,102,241,.1)'],
                            ];
                        @endphp
                        @foreach ($features as $f)
                            <div class="feature-card">
                                <div class="feature-icon" style="background: {{ $f['bg'] }}">{{ $f['icon'] }}</div>
                                <h3>{{ $f['title'] }}</h3>
                                <p>{{ $f['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ── Hero Image ── --}}
                <section class="hero-image-section">
                    <div class="section-header">
                        <h2>{{ __('welcome.previewTitle') }}</h2>
                        <p>{{ __('welcome.previewSubtitle') }}</p>
                    </div>
                    <div class="hero-image-wrapper">
                        <img src="{{ asset('hero.png') }}" alt="Mahubiri"
                             onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:320px;color:#9ca3af;font-size:14px;\'>{{ __('welcome.previewUnavailable') }}</div>'">
                    </div>
                </section>

                {{-- ── CTA Admin ── --}}
                <section class="cta-section">
                    <div class="cta-box">
                        <h2>{{ __('welcome.ctaTitle') }}</h2>
                        <p>{{ __('welcome.ctaDescription') }}</p>
                        <a href="/admin/login" class="btn btn-white" style="position:relative;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            {{ __('welcome.ctaButton') }}
                        </a>
                    </div>
                </section>

                {{-- ── Contact ── --}}
                <section class="contact">
                    <div class="section-header">
                        <h2>{{ __('welcome.contactTitle') }}</h2>
                        <p>{{ __('welcome.contactSubtitle') }}</p>
                    </div>
                    <div class="contact-grid">
                        <a href="mailto:kwetucode@gmail.com" class="contact-card">
                            <div class="contact-icon">
                                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="contact-label">{{ __('welcome.contactEmail') }}</div>
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
                                <div class="contact-label">{{ __('welcome.contactPhone') }}</div>
                                <div class="contact-value">+243 971 330 007</div>
                            </div>
                        </a>
                    </div>
                </section>
            </div>

            {{-- ── Footer ── --}}
            <footer class="footer">
                <div class="container">
                    <p>&copy; {{ date('Y') }} <span>Mahubiri</span>. {{ __('welcome.footerRights') }}</p>
                    <p class="sub">{{ __('welcome.footerBeta') }}</p>
                </div>
            </footer>
        </div>
    </body>
</html>
