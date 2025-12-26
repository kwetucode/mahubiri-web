<div class="min-h-screen flex">
    <!-- Left Side - Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-[#6B4EAF] to-[#9C7DC7] rounded-2xl mb-4 p-3 shadow-lg">
                    <img src="{{ asset('logo.png') }}" alt="Mahubiri Logo" class="w-full h-full object-contain">
                </div>
                <h2 class="text-3xl font-bold text-gray-900">
                    Connexion Admin
                </h2>
                <p class="text-gray-600 mt-2">
                    Accédez au panel d'administration
                </p>
            </div>

            <!-- Flash Messages -->
            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <x-icon name="x" class="w-5 h-5 text-red-500 mr-2 flex-shrink-0" />
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if (session('message'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-center">
                        <x-icon name="check" class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" />
                        <p class="text-sm text-green-700">{{ session('message') }}</p>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form wire:submit.prevent="login" class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Adresse email
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200">
                            <x-icon name="user" class="w-5 h-5 text-gray-400 group-focus-within:text-violet-600" />
                        </div>
                        <input
                            type="email"
                            id="email"
                            wire:model="email"
                            class="pl-12 pr-4 py-3 block w-full rounded-xl border-2 border-gray-200 bg-gray-50 shadow-sm 
                                   focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-100 
                                   transition-all duration-200 
                                   @error('email') border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-100 @enderror"
                            placeholder="admin@mahubiri.com"
                            required
                            autofocus
                        >
                    </div>
                    @error('email')
                        <div class="mt-2 flex items-center text-sm text-red-600">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Mot de passe
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors duration-200">
                            <svg class="w-5 h-5 text-gray-400 group-focus-within:text-violet-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            type="password"
                            id="password"
                            wire:model="password"
                            class="pl-12 pr-4 py-3 block w-full rounded-xl border-2 border-gray-200 bg-gray-50 shadow-sm 
                                   focus:border-violet-500 focus:bg-white focus:ring-4 focus:ring-violet-100 
                                   transition-all duration-200
                                   @error('password') border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-100 @enderror"
                            placeholder="••••••••"
                            required
                        >
                    </div>
                    @error('password')
                        <div class="mt-2 flex items-center text-sm text-red-600">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input
                        type="checkbox"
                        id="remember"
                        wire:model="remember"
                        class="h-4 w-4 text-violet-600 focus:ring-violet-500 border-gray-300 rounded transition-colors"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-700 cursor-pointer select-none">
                        Se souvenir de moi
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-base font-semibold text-white 
                               bg-gradient-to-r from-[#6B4EAF] to-[#9C7DC7] 
                               hover:from-[#5a3f94] hover:to-[#8b6cb6] hover:shadow-xl hover:scale-[1.02]
                               active:scale-[0.98]
                               focus:outline-none focus:ring-4 focus:ring-violet-200 
                               transition-all duration-200 
                               disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:scale-100"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>
                            Se connecter
                        </span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Connexion...
                        </span>
                    </button>
                </div>
            </form>

            <!-- Footer Note -->
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500">
                    🔒 Accès réservé aux administrateurs uniquement
                </p>
            </div>
        </div>
    </div>

    <!-- Right Side - Description -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-[#6B4EAF] to-[#9C7DC7] items-center justify-center p-12 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-1/2 translate-y-1/2"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 max-w-lg text-white">
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 backdrop-blur-sm rounded-2xl p-4 mb-6">
                    <img src="{{ asset('logo.png') }}" alt="Mahubiri Logo" class="w-full h-full object-contain">
                </div>
            </div>
            
            <h1 class="text-4xl font-bold mb-6">
                Panel d'Administration Mahubiri
            </h1>
            
            <p class="text-xl text-violet-100 mb-8">
                Gérez votre plateforme de prédications en toute simplicité
            </p>

            <div class="space-y-6">
                <!-- Feature 1 -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <x-icon name="users" class="w-6 h-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-1">Gestion des Utilisateurs</h3>
                        <p class="text-violet-100 text-sm">
                            Administrez les comptes utilisateurs, églises et prédicateurs
                        </p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <x-icon name="bell" class="w-6 h-6 text-white" />
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-1">Notifications en Temps Réel</h3>
                        <p class="text-violet-100 text-sm">
                            Recevez des alertes instantanées sur les nouvelles inscriptions
                        </p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-1">Statistiques Détaillées</h3>
                        <p class="text-violet-100 text-sm">
                            Suivez l'évolution de votre plateforme avec des rapports complets
                        </p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg mb-1">Sécurité Renforcée</h3>
                        <p class="text-violet-100 text-sm">
                            Contrôle d'accès basé sur les rôles et authentification sécurisée
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="mt-12 grid grid-cols-3 gap-4">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold">24/7</div>
                    <div class="text-sm text-violet-100 mt-1">Disponibilité</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold">100%</div>
                    <div class="text-sm text-violet-100 mt-1">Sécurisé</div>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold">∞</div>
                    <div class="text-sm text-violet-100 mt-1">Évolutif</div>
                </div>
            </div>
        </div>
    </div>
</div>

