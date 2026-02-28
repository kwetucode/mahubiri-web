<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

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

const features = [
    { icon: 'sermons', title: 'Gestion des prédications', desc: 'Publiez, organisez et partagez vos sermons audio avec votre communauté.' },
    { icon: 'users', title: 'Communauté connectée', desc: 'Gérez vos fidèles, prédicateurs et églises depuis un seul tableau de bord.' },
    { icon: 'analytics', title: 'Statistiques en temps réel', desc: 'Suivez l\'engagement et les écoutes avec des analytics détaillés.' },
    { icon: 'notifications', title: 'Notifications push', desc: 'Informez votre communauté des nouveaux contenus instantanément.' },
];
</script>

<template>
    <div class="h-screen flex bg-white overflow-hidden">
        <!-- Left Side - Login Form -->
        <div class="w-full lg:w-[480px] xl:w-[540px] flex flex-col justify-center px-8 sm:px-12 lg:px-14 py-8 relative z-10">
            <!-- Logo -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <img src="/logo.png" alt="Mahubiri" class="w-10 h-10 object-contain rounded-full" />
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Mahubiri</h1>
                        <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Administration</p>
                    </div>
                </div>

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Bon retour parmi nous</h2>
                <p class="text-gray-500 mt-1 text-sm">Connectez-vous pour accéder à votre tableau de bord.</p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Adresse email
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            autofocus
                            class="block w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50/50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-primary focus:bg-white transition-all duration-200 text-sm"
                            :class="{ 'border-red-400 focus:border-red-500 bg-red-50/30': form.errors.email }"
                            placeholder="admin@mahubiri.com"
                        />
                    </div>
                    <p v-if="form.errors.email" class="mt-2 text-sm text-red-600 flex items-center gap-1">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Mot de passe
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400 group-focus-within:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            required
                            class="block w-full pl-12 pr-14 py-3 border-2 border-gray-200 rounded-xl bg-gray-50/50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-primary focus:bg-white transition-all duration-200 text-sm"
                            :class="{ 'border-red-400 focus:border-red-500 bg-red-50/30': form.errors.password }"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="mt-2 text-sm text-red-600 flex items-center gap-1">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none group">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4.5 h-4.5 rounded-md border-2 border-gray-300 text-primary focus:ring-primary/30 focus:ring-offset-0 cursor-pointer transition-colors"
                        />
                        <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">Se souvenir de moi</span>
                    </label>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 py-3 px-6 bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary text-white font-bold rounded-xl shadow-xl shadow-primary/25 hover:shadow-2xl hover:shadow-primary/30 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 text-sm"
                >
                    <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-if="form.processing">Connexion en cours...</span>
                    <span v-else>Se connecter</span>
                    <svg v-if="!form.processing" class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </form>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-400 mt-6">
                &copy; {{ new Date().getFullYear() }} Mahubiri. Tous droits réservés.
            </p>
        </div>

        <!-- Right Side - Hero / Description -->
        <div class="hidden lg:flex flex-1 relative overflow-hidden">
            <!-- Login background image -->
            <img src="/login.png" alt="" class="absolute inset-0 w-full h-full object-cover" />
            <!-- Gradient overlay -->
            <div class="absolute inset-0 bg-linear-to-br from-primary/90 via-primary-dark/85 to-[#3a2570]/90"></div>

            <!-- Decorative patterns -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 right-0 w-125 h-125 rounded-full bg-white/20 -translate-y-1/2 translate-x-1/4 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full bg-accent-warm/30 translate-y-1/3 -translate-x-1/4 blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 w-[300px] h-[300px] rounded-full bg-white/10 -translate-x-1/2 -translate-y-1/2 blur-2xl"></div>
            </div>

            <!-- Decorative grid dots -->
            <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 30px 30px;"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-10 xl:px-16 py-8 w-full">
                <!-- Top badge -->
                <div class="mb-5">
                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/15 backdrop-blur-sm text-white/90 text-sm font-medium border border-white/10">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                        Plateforme active
                    </span>
                </div>

                <h2 class="text-3xl xl:text-4xl font-extrabold text-white leading-tight mb-3">
                    Gérez vos<br>
                    <span class="text-accent-warm">prédications</span><br>
                    en toute simplicité
                </h2>
                <p class="text-base text-white/70 mb-8 max-w-md leading-relaxed">
                    La plateforme tout-en-un pour publier, organiser et diffuser vos sermons auprès de votre communauté.
                </p>

                <!-- Feature cards -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="group flex items-start gap-3 p-3 rounded-xl bg-white/[0.07] backdrop-blur-sm border border-white/10 hover:bg-white/12 transition-all duration-300"
                    >
                        <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-white/15 shrink-0 group-hover:bg-white/20 transition-colors">
                            <!-- Sermons icon -->
                            <svg v-if="feature.icon === 'sermons'" class="w-5 h-5 text-accent-warm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                            <!-- Users icon -->
                            <svg v-if="feature.icon === 'users'" class="w-5 h-5 text-accent-warm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <!-- Analytics icon -->
                            <svg v-if="feature.icon === 'analytics'" class="w-5 h-5 text-accent-warm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <!-- Notifications icon -->
                            <svg v-if="feature.icon === 'notifications'" class="w-5 h-5 text-accent-warm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-semibold text-white mb-0.5">{{ feature.title }}</h4>
                            <p class="text-xs text-white/50 leading-relaxed">{{ feature.desc }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="flex items-center gap-8 mt-8 pt-6 border-t border-white/10">
                    <div>
                        <p class="text-2xl font-bold text-white">1000+</p>
                        <p class="text-xs text-white/50">Prédications</p>
                    </div>
                    <div class="w-px h-10 bg-white/10"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">50+</p>
                        <p class="text-xs text-white/50">Églises</p>
                    </div>
                    <div class="w-px h-10 bg-white/10"></div>
                    <div>
                        <p class="text-2xl font-bold text-white">5000+</p>
                        <p class="text-xs text-white/50">Utilisateurs</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth entrance animation */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
form > div, form > button, form > label {
    animation: fadeInUp 0.5s ease-out both;
}
form > div:nth-child(1) { animation-delay: 0.1s; }
form > div:nth-child(2) { animation-delay: 0.2s; }
form > div:nth-child(3) { animation-delay: 0.3s; }
form > button { animation-delay: 0.4s; }
</style>
