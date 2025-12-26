<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Mahubiri') }} - Application Mobile de prédications</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Tailwind CSS -->
          @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui'],
                        },
                        colors: {
                            primary: {
                                DEFAULT: '#6B4EAF',
                                light: '#9C7DC7',
                                dark: '#5A3D94',
                            },
                            accent: {
                                DEFAULT: '#2C3E50',
                                warm: '#E8B77D',
                                lavender: '#E6E3F5',
                            }
                        }
                    }
                }
            }
        </script>

        <!-- Custom Styles -->
        <style>
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.9; }
            }
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideInLeft {
                from {
                    opacity: 0;
                    transform: translateX(-50px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(50px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
            }
            @keyframes scaleIn {
                from {
                    opacity: 0;
                    transform: scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-5px); }
            }

            .animate-pulse-custom {
                animation: pulse 2s ease-in-out infinite;
            }
            .animate-fadeInUp {
                animation: fadeInUp 0.8s ease-out forwards;
            }
            .animate-fadeIn {
                animation: fadeIn 1s ease-out forwards;
            }
            .animate-slideInLeft {
                animation: slideInLeft 0.8s ease-out forwards;
            }
            .animate-slideInRight {
                animation: slideInRight 0.8s ease-out forwards;
            }
            .animate-float {
                animation: float 3s ease-in-out infinite;
            }
            .animate-scaleIn {
                animation: scaleIn 0.6s ease-out forwards;
            }
            .animate-delay-100 { animation-delay: 0.1s; }
            .animate-delay-200 { animation-delay: 0.2s; }
            .animate-delay-300 { animation-delay: 0.3s; }
            .animate-delay-400 { animation-delay: 0.4s; }
            .animate-delay-500 { animation-delay: 0.5s; }
            .animate-delay-600 { animation-delay: 0.6s; }

            .gradient-text {
                background: linear-gradient(135deg, #6B4EAF 0%, #9C7DC7 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            @media (prefers-color-scheme: dark) {
                .gradient-text {
                    background: linear-gradient(135deg, #9C7DC7 0%, #E6E3F5 100%);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    background-clip: text;
                }
            }

            .hover-bounce:hover {
                animation: bounce 0.5s ease-in-out;
            }
        </style>
    </head>
    <body class="font-sans bg-linear-to-br from-gray-50 to-gray-200 dark:from-gray-950 dark:to-gray-900 min-h-screen text-gray-900 dark:text-gray-100">
        <!-- Banner de test -->
        <div class="bg-linear-to-r from-primary to-primary-light dark:from-primary dark:to-primary-light text-white text-center py-4 px-4 font-bold text-sm md:text-base tracking-widest uppercase shadow-lg animate-pulse-custom">
            ⚠️ Application en Mode Test - Version Bêta ⚠️
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- En-tête -->
            <header class="text-center py-12 md:py-16">
                <h1 class="text-5xl md:text-7xl font-bold mb-4 gradient-text animate-fadeInUp">
                    Mahubiri
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 mb-6 font-medium animate-fadeInUp animate-delay-200">
                    Écoutez des Sermons Inspirants Où Que Vous Soyez
                </p>
                <span class="inline-block bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-full text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-scaleIn animate-delay-400">
                    Version 1.0.0 (Beta)
                </span>
            </header>

            <!-- Captures d'écran -->
            <div class="mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-800 dark:text-gray-100">
                    Aperçu de l'Application
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 max-w-5xl mx-auto">
                    <!-- Screenshot 1 -->
                    <div class="group bg-white dark:bg-gray-800 rounded-3xl p-6 md:p-8 shadow-2xl hover:shadow-primary/30 dark:hover:shadow-primary-light/30 transition-all duration-300 transform hover:-translate-y-3 hover:scale-[1.02] border-t-4 border-primary dark:border-primary-light md:rotate-[-3deg] animate-slideInLeft animate-float">
                        <div class="relative overflow-hidden rounded-2xl mb-6" style="perspective: 1000px;">
                            <img src="{{ asset('home.png') }}"
                                 alt="Page d'accueil Mahubiri"
                                 class="w-full h-auto rounded-xl shadow-2xl group-hover:scale-105 transition-transform duration-300"
                                 style="transform: rotateY(8deg);"
                                 onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-96 bg-gradient-to-br from-primary/10 to-primary-light/10 dark:from-primary/20 dark:to-primary-light/20 rounded-xl\'><div class=\'text-center\'><svg class=\'w-20 h-20 mx-auto mb-4 text-primary dark:text-primary-light\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg><p class=\'text-gray-600 dark:text-gray-400 font-medium\'>Image en cours de chargement</p></div></div>'">
                        </div>
                        <h3 class="text-2xl font-bold text-center text-primary dark:text-primary-light">
                            🏠 Page d'Accueil
                        </h3>
                        <p class="text-center text-gray-600 dark:text-gray-400 mt-2">
                            Explorez les derniers sermons et contenus populaires
                        </p>
                    </div>

                    <!-- Screenshot 2 -->
                    <div class="group bg-white dark:bg-gray-800 rounded-3xl p-6 md:p-8 shadow-2xl hover:shadow-primary/30 dark:hover:shadow-primary-light/30 transition-all duration-300 transform hover:-translate-y-3 hover:scale-[1.02] border-t-4 border-primary dark:border-primary-light md:rotate-[3deg] animate-slideInRight animate-float">
                        <div class="relative overflow-hidden rounded-2xl mb-6" style="perspective: 1000px;">
                            <img src="{{ asset('login.png') }}"
                                 alt="Page de connexion Mahubiri"
                                 class="w-full h-auto rounded-xl shadow-2xl group-hover:scale-105 transition-transform duration-300"
                                 style="transform: rotateY(-8deg);"
                                 onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-96 bg-gradient-to-br from-primary/10 to-primary-light/10 dark:from-primary/20 dark:to-primary-light/20 rounded-xl\'><div class=\'text-center\'><svg class=\'w-20 h-20 mx-auto mb-4 text-primary dark:text-primary-light\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'></path></svg><p class=\'text-gray-600 dark:text-gray-400 font-medium\'>Image en cours de chargement</p></div></div>'">
                        </div>
                        <h3 class="text-2xl font-bold text-center text-primary dark:text-primary-light">
                            🔐 Connexion
                        </h3>
                        <p class="text-center text-gray-600 dark:text-gray-400 mt-2">
                            Interface d'authentification simple et sécurisée
                        </p>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="max-w-4xl mx-auto mb-16">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg rounded-2xl shadow-2xl p-8 md:p-12 border border-gray-200 dark:border-gray-700 animate-fadeInUp">
                    <p class="text-lg md:text-xl text-gray-700 dark:text-gray-300 mb-6 leading-relaxed">
                        <strong class="text-primary dark:text-primary-light">Mahubiri</strong> est votre application mobile de référence pour découvrir, écouter et partager
                        des sermons et enseignements spirituels inspirants. Connectez-vous avec des prédicateurs du monde entier
                        et enrichissez votre vie spirituelle quotidienne.
                    </p>
                    <p class="text-base md:text-lg text-gray-600 dark:text-gray-400 leading-relaxed">
                        Disponible sur Android et iOS, Mahubiri vous offre une expérience intuitive et fluide pour accéder
                        à des milliers de sermons, créer vos playlists favorites et suivre vos prédicateurs préférés.
                    </p>
                </div>
            </div>

            <!-- Fonctionnalités -->
            <section class="mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-center mb-12 text-gray-800 dark:text-gray-100 animate-fadeInUp">
                    Fonctionnalités Principales
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    <!-- Feature 1 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border-2 border-gray-200 dark:border-gray-700 hover:border-primary dark:hover:border-primary-light transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl animate-fadeInUp animate-delay-100">
                        <div class="text-4xl mb-4 hover-bounce">🎧</div>
                        <h4 class="text-xl font-bold mb-3 text-primary dark:text-primary-light">
                            Écoute Illimitée
                        </h4>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Accédez à une vaste bibliothèque de sermons et enseignements en streaming ou en téléchargement.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border-2 border-gray-200 dark:border-gray-700 hover:border-primary dark:hover:border-primary-light transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl animate-fadeInUp animate-delay-200">
                        <div class="text-4xl mb-4 hover-bounce">⭐</div>
                        <h4 class="text-xl font-bold mb-3 text-primary dark:text-primary-light">
                            Favoris & Playlists
                        </h4>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Créez vos propres playlists et sauvegardez vos sermons préférés pour y accéder facilement.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border-2 border-gray-200 dark:border-gray-700 hover:border-primary dark:hover:border-primary-light transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl animate-fadeInUp animate-delay-300">
                        <div class="text-4xl mb-4 hover-bounce">👤</div>
                        <h4 class="text-xl font-bold mb-3 text-primary dark:text-primary-light">
                            Profils de Prédicateurs
                        </h4>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Découvrez des profils de prédicateurs détaillés et suivez vos prédicateurs favoris.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border-2 border-gray-200 dark:border-gray-700 hover:border-primary dark:hover:border-primary-light transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl animate-fadeInUp animate-delay-400">
                        <div class="text-4xl mb-4 hover-bounce">🔔</div>
                        <h4 class="text-xl font-bold mb-3 text-primary dark:text-primary-light">
                            Notifications
                        </h4>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Recevez des notifications push pour les nouveaux sermons de vos prédicateurs suivis.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border-2 border-gray-200 dark:border-gray-700 hover:border-primary dark:hover:border-primary-light transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl animate-fadeInUp animate-delay-500">
                        <div class="text-4xl mb-4 hover-bounce">🔍</div>
                        <h4 class="text-xl font-bold mb-3 text-primary dark:text-primary-light">
                            Recherche Avancée
                        </h4>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Trouvez facilement des sermons par thème, prédicateur, église ou date de publication.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border-2 border-gray-200 dark:border-gray-700 hover:border-primary dark:hover:border-primary-light transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl animate-fadeInUp animate-delay-600">
                        <div class="text-4xl mb-4 hover-bounce">🌐</div>
                        <h4 class="text-xl font-bold mb-3 text-primary dark:text-primary-light">
                            Multi-langues
                        </h4>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                            Interface disponible en français, anglais, swahili et d'autres langues à venir.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Section Contact -->
            <section class="mb-16">
                <div class="max-w-4xl mx-auto bg-gradient-to-br from-primary/10 to-primary-light/10 dark:from-primary/20 dark:to-primary-light/20 rounded-3xl shadow-2xl p-8 md:p-12 border-2 border-primary/30 dark:border-primary-light/30 animate-scaleIn">
                    <h2 class="text-3xl md:text-4xl font-bold text-center mb-8 text-gray-800 dark:text-gray-100">
                        Contactez-nous
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email -->
                        <div class="flex items-center justify-center gap-4 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-primary dark:text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Email</p>
                                <a href="mailto:kwetucode@gmail.com" class="text-lg font-semibold text-primary dark:text-primary-light hover:underline">
                                    kwetucode@gmail.com
                                </a>
                            </div>
                        </div>

                        <!-- Téléphone -->
                        <div class="flex items-center justify-center gap-4 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-primary dark:text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Téléphone</p>
                                <a href="tel:+243971330007" class="text-lg font-semibold text-primary dark:text-primary-light hover:underline">
                                    +243 971 330 007
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pied de page -->
            <footer class="text-center py-12 mt-16 bg-white/50 dark:bg-gray-800/50 backdrop-blur-md rounded-2xl border border-gray-200 dark:border-gray-700">
                <p class="text-gray-600 dark:text-gray-400 mb-2">
                    &copy; {{ date('Y') }} <span class="font-semibold text-primary dark:text-primary-light">Mahubiri</span>. Tous droits réservés.
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                    Cette application est actuellement en phase de test. Certaines fonctionnalités peuvent être instables.
                </p>
            </footer>
        </div>
    </body>
</html>
