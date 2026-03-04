<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Mahubiri') }} - Application Mobile de prédications</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Custom Styles -->
        <style>
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(30px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to   { opacity: 1; }
            }
            @keyframes slideInLeft {
                from { opacity: 0; transform: translateX(-40px); }
                to   { opacity: 1; transform: translateX(0); }
            }
            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(40px); }
                to   { opacity: 1; transform: translateX(0); }
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50%      { transform: translateY(-8px); }
            }
            @keyframes scaleIn {
                from { opacity: 0; transform: scale(0.92); }
                to   { opacity: 1; transform: scale(1); }
            }
            @keyframes shimmer {
                0%   { background-position: -200% 0; }
                100% { background-position: 200% 0; }
            }

            .animate-fadeInUp    { animation: fadeInUp 0.8s ease-out both; }
            .animate-fadeIn      { animation: fadeIn 1s ease-out both; }
            .animate-slideInLeft { animation: slideInLeft 0.8s ease-out both; }
            .animate-slideInRight{ animation: slideInRight 0.8s ease-out both; }
            .animate-float       { animation: float 4s ease-in-out infinite; }
            .animate-scaleIn     { animation: scaleIn 0.6s ease-out both; }

            .animate-delay-100 { animation-delay: 0.1s; }
            .animate-delay-200 { animation-delay: 0.2s; }
            .animate-delay-300 { animation-delay: 0.3s; }
            .animate-delay-400 { animation-delay: 0.4s; }
            .animate-delay-500 { animation-delay: 0.5s; }
            .animate-delay-600 { animation-delay: 0.6s; }

            .gradient-text {
                background: linear-gradient(135deg, #6B4EAF 0%, #9C7DC7 50%, #E8B77D 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .hero-gradient {
                background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 30%, #e6e3f5 60%, #faf5ff 100%);
            }

            .card-shine {
                position: relative;
                overflow: hidden;
            }
            .card-shine::before {
                content: '';
                position: absolute;
                top: 0; left: -100%; width: 50%; height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
                transition: left 0.6s ease;
                z-index: 1;
            }
            .card-shine:hover::before {
                left: 100%;
            }

            .badge-shimmer {
                background: linear-gradient(90deg, #6B4EAF, #9C7DC7, #6B4EAF);
                background-size: 200% 100%;
                animation: shimmer 3s ease-in-out infinite;
            }
        </style>
    </head>
    <body class="font-sans min-h-screen text-gray-800" style="background: linear-gradient(180deg, #f8f7fc 0%, #ffffff 40%, #f5f3ff 100%);">

        {{-- ── Bannière Beta ── --}}
        <div class="badge-shimmer text-white text-center py-3 px-4 font-semibold text-sm tracking-wider uppercase shadow-md">
            ⚠️ Application en Mode Test — Version Bêta ⚠️
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- ── Hero ── --}}
            <header class="hero-gradient rounded-3xl py-16 md:py-24 px-6 text-center mb-16 shadow-sm">
                <h1 class="text-5xl md:text-7xl font-bold mb-5 gradient-text animate-fadeInUp leading-tight">
                    Mahubiri
                </h1>
                <p class="text-lg md:text-2xl text-gray-600 mb-8 font-medium animate-fadeInUp animate-delay-200 max-w-2xl mx-auto">
                    Écoutez des Sermons Inspirants Où Que Vous Soyez
                </p>
                <span class="inline-flex items-center gap-2 bg-primary text-white px-7 py-3 rounded-full text-sm font-semibold shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 transition-all duration-300 transform hover:scale-105 animate-scaleIn animate-delay-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Version 1.0.0 (Beta)
                </span>
            </header>

            {{-- ── Aperçu de l'Application ── --}}
            <section class="mb-20">
                <h2 class="text-3xl md:text-4xl font-bold text-center mb-4 text-gray-800">
                    Aperçu de l'Application
                </h2>
                <p class="text-center text-gray-500 mb-12 max-w-xl mx-auto">
                    Découvrez l'interface élégante et intuitive de Mahubiri
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 max-w-5xl mx-auto">
                    {{-- Screenshot 1 --}}
                    <div class="group card-shine bg-white rounded-3xl p-6 md:p-8 shadow-lg hover:shadow-2xl hover:shadow-primary/10 transition-all duration-500 transform hover:-translate-y-3 border border-gray-100 animate-slideInLeft">
                        <div class="relative overflow-hidden rounded-2xl mb-6 bg-gradient-to-br from-purple-50 to-indigo-50 p-3">
                            <img src="{{ asset('home.png') }}"
                                 alt="Page d'accueil Mahubiri"
                                 class="w-full h-auto rounded-xl shadow-xl group-hover:scale-[1.03] transition-transform duration-500"
                                 onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-80 rounded-xl\'><div class=\'text-center\'><svg class=\'w-16 h-16 mx-auto mb-4 text-primary/40\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg><p class=\'text-gray-400 text-sm\'>Aperçu non disponible</p></div></div>'">
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary/10 text-xl">🏠</span>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Page d'Accueil</h3>
                                <p class="text-gray-500 text-sm">Sermons récents et contenus populaires</p>
                            </div>
                        </div>
                    </div>

                    {{-- Screenshot 2 --}}
                    <div class="group card-shine bg-white rounded-3xl p-6 md:p-8 shadow-lg hover:shadow-2xl hover:shadow-primary/10 transition-all duration-500 transform hover:-translate-y-3 border border-gray-100 animate-slideInRight">
                        <div class="relative overflow-hidden rounded-2xl mb-6 bg-gradient-to-br from-purple-50 to-indigo-50 p-3">
                            <img src="{{ asset('login.png') }}"
                                 alt="Page de connexion Mahubiri"
                                 class="w-full h-auto rounded-xl shadow-xl group-hover:scale-[1.03] transition-transform duration-500"
                                 onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-80 rounded-xl\'><div class=\'text-center\'><svg class=\'w-16 h-16 mx-auto mb-4 text-primary/40\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'></path></svg><p class=\'text-gray-400 text-sm\'>Aperçu non disponible</p></div></div>'">
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-primary/10 text-xl">🔐</span>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Connexion</h3>
                                <p class="text-gray-500 text-sm">Authentification simple et sécurisée</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ── Description ── --}}
            <section class="max-w-4xl mx-auto mb-20">
                <div class="bg-white rounded-3xl shadow-lg p-8 md:p-12 border border-gray-100 animate-fadeInUp relative overflow-hidden">
                    {{-- Accent bar --}}
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary via-primary-light to-accent-warm"></div>
                    <p class="text-lg md:text-xl text-gray-700 mb-6 leading-relaxed">
                        <strong class="text-primary">Mahubiri</strong> est votre application mobile de référence pour découvrir, écouter et partager
                        des sermons et enseignements spirituels inspirants. Connectez-vous avec des prédicateurs du monde entier
                        et enrichissez votre vie spirituelle quotidienne.
                    </p>
                    <p class="text-base md:text-lg text-gray-500 leading-relaxed">
                        Disponible sur Android et iOS, Mahubiri vous offre une expérience intuitive et fluide pour accéder
                        à des milliers de sermons, créer vos playlists favorites et suivre vos prédicateurs préférés.
                    </p>
                </div>
            </section>

            {{-- ── Fonctionnalités ── --}}
            <section class="mb-20">
                <h2 class="text-3xl md:text-4xl font-bold text-center mb-4 text-gray-800 animate-fadeInUp">
                    Fonctionnalités Principales
                </h2>
                <p class="text-center text-gray-500 mb-12 max-w-xl mx-auto">
                    Tout ce dont vous avez besoin pour une expérience spirituelle enrichissante
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    @php
                        $features = [
                            ['icon' => '🎧', 'title' => 'Écoute Illimitée', 'desc' => 'Accédez à une vaste bibliothèque de sermons et enseignements en streaming ou en téléchargement.', 'color' => 'from-violet-500/10 to-purple-500/10'],
                            ['icon' => '⭐', 'title' => 'Favoris & Playlists', 'desc' => 'Créez vos propres playlists et sauvegardez vos sermons préférés pour y accéder facilement.', 'color' => 'from-amber-500/10 to-orange-500/10'],
                            ['icon' => '👤', 'title' => 'Profils de Prédicateurs', 'desc' => 'Découvrez des profils de prédicateurs détaillés et suivez vos prédicateurs favoris.', 'color' => 'from-blue-500/10 to-cyan-500/10'],
                            ['icon' => '🔔', 'title' => 'Notifications', 'desc' => 'Recevez des notifications push pour les nouveaux sermons de vos prédicateurs suivis.', 'color' => 'from-rose-500/10 to-pink-500/10'],
                            ['icon' => '🔍', 'title' => 'Recherche Avancée', 'desc' => 'Trouvez facilement des sermons par thème, prédicateur, église ou date de publication.', 'color' => 'from-emerald-500/10 to-teal-500/10'],
                            ['icon' => '🌐', 'title' => 'Multi-langues', 'desc' => 'Interface disponible en français, anglais, swahili et d\'autres langues à venir.', 'color' => 'from-indigo-500/10 to-violet-500/10'],
                        ];
                    @endphp

                    @foreach ($features as $i => $feature)
                        <div class="group bg-white rounded-2xl p-6 border border-gray-100 hover:border-primary/30 transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl hover:shadow-primary/5 animate-fadeInUp animate-delay-{{ ($i + 1) * 100 }}">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $feature['color'] }} flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform duration-300">
                                {{ $feature['icon'] }}
                            </div>
                            <h4 class="text-lg font-bold mb-2 text-gray-800 group-hover:text-primary transition-colors duration-300">
                                {{ $feature['title'] }}
                            </h4>
                            <p class="text-gray-500 leading-relaxed text-sm">
                                {{ $feature['desc'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ── Contact ── --}}
            <section class="mb-20">
                <div class="max-w-4xl mx-auto bg-gradient-to-br from-primary/5 via-white to-primary-light/5 rounded-3xl shadow-lg p-8 md:p-12 border border-primary/10 animate-scaleIn">
                    <h2 class="text-3xl md:text-4xl font-bold text-center mb-3 text-gray-800">
                        Contactez-nous
                    </h2>
                    <p class="text-center text-gray-500 mb-10">
                        Une question ou une suggestion ? N'hésitez pas à nous écrire.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Email --}}
                        <a href="mailto:kwetucode@gmail.com" class="group flex items-center gap-4 bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg hover:shadow-primary/5 transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                                <svg class="w-6 h-6 text-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Email</p>
                                <p class="text-base font-semibold text-gray-700 group-hover:text-primary transition-colors duration-300">
                                    kwetucode@gmail.com
                                </p>
                            </div>
                        </a>

                        {{-- Téléphone --}}
                        <a href="tel:+243971330007" class="group flex items-center gap-4 bg-white rounded-2xl p-6 shadow-sm hover:shadow-lg hover:shadow-primary/5 transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                                <svg class="w-6 h-6 text-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Téléphone</p>
                                <p class="text-base font-semibold text-gray-700 group-hover:text-primary transition-colors duration-300">
                                    +243 971 330 007
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            {{-- ── Footer ── --}}
            <footer class="text-center py-10 mt-8 border-t border-gray-200">
                <p class="text-gray-500 mb-1">
                    &copy; {{ date('Y') }} <span class="font-semibold text-primary">Mahubiri</span>. Tous droits réservés.
                </p>
                <p class="text-xs text-gray-400 mt-2">
                    Cette application est actuellement en phase de test. Certaines fonctionnalités peuvent être instables.
                </p>
            </footer>
        </div>
    </body>
</html>
