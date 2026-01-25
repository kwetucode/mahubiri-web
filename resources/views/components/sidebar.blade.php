<aside class="bg-linear-to-b from-[#6B4EAF] to-[#5A3D94] text-white fixed h-screen shadow-xl overflow-y-auto z-30 lg:translate-x-0 transition-all duration-300"
       :class="{ 
         '-translate-x-full': !sidebarOpen, 
         'translate-x-0': sidebarOpen,
         'w-64': !sidebarCollapsed,
         'w-20': sidebarCollapsed
       }"
       class="lg:translate-x-0!"
       x-cloak>
    <!-- Logo & Brand -->
    <div class="p-6 border-b border-[#9C7DC7]">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3" :class="{ 'justify-center w-full': sidebarCollapsed }">
                <img src="{{ asset('logo.png') }}" alt="Mahubiri Logo" class="w-12 h-12 object-contain flex-shrink-0">
                <div x-show="!sidebarCollapsed" x-transition>
                    <h1 class="text-xl font-bold">Mahubiri</h1>
                    <p class="text-xs text-[#E6E3F5]">La Parole en Action</p>
                </div>
            </div>
            <!-- Close button for mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden p-2 rounded-lg hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <!-- Collapse Toggle Button (Desktop only) -->
        <button @click="toggleSidebar()" 
                class="hidden lg:flex mt-4 w-full items-center p-2 rounded-lg hover:bg-white/10 transition-colors"
                :class="{ 'justify-center': sidebarCollapsed, 'justify-center': !sidebarCollapsed }">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                 :class="{ 'rotate-180': sidebarCollapsed }">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
            <span x-show="!sidebarCollapsed" x-transition class="ml-2 text-sm">Réduire</span>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="mt-6 px-4">
        <div class="space-y-2">
            <x-sidebar-link
                href="{{ route('dashboard') }}"
                :active="request()->routeIs('dashboard')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </x-slot>
                Tableau de bord
            </x-sidebar-link>
            <x-sidebar-link
                href="/churches"
                :active="request()->is('churches*')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </x-slot>
                Églises
            </x-sidebar-link>
             <x-sidebar-link
                href="{{ route('preacher-profiles.index') }}"
                :active="request()->routeIs('preacher-profiles.*')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                    </svg>
                </x-slot>
                Prédicateurs
            </x-sidebar-link>

            <x-sidebar-link
                href="{{ route('categories.index') }}"
                :active="request()->routeIs('categories.*')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </x-slot>
                Catégories
            </x-sidebar-link>

            <x-sidebar-link
                href="{{ route('roles.index') }}"
                :active="request()->routeIs('roles.*')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </x-slot>
                Rôles
            </x-sidebar-link>

            <x-sidebar-link
                href="{{ route('users.index') }}"
                :active="request()->routeIs('users.*')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </x-slot>
                Utilisateurs
            </x-sidebar-link>

            <!-- Divider -->
            <div class="my-4 border-t border-[#9C7DC7]" x-show="!sidebarCollapsed"></div>
            <div x-show="!sidebarCollapsed" x-transition class="mb-2 px-3 text-xs font-semibold text-[#E6E3F5] uppercase tracking-wider">
                Monitoring & Analytics
            </div>

            <x-sidebar-link
                href="{{ route('monitoring.realtime') }}"
                :active="request()->routeIs('monitoring.realtime')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </x-slot>
                Temps Réel
            </x-sidebar-link>

            <x-sidebar-link
                href="{{ route('analytics.users') }}"
                :active="request()->routeIs('analytics.users')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </x-slot>
                Analytiques Utilisateurs
            </x-sidebar-link>

            <x-sidebar-link
                href="{{ route('storage.monitor') }}"
                :active="request()->routeIs('storage.monitor')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                </x-slot>
                Stockage
            </x-sidebar-link>

            <x-sidebar-link
                href="{{ route('logs.api') }}"
                :active="request()->routeIs('logs.api')"
            >
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </x-slot>
                Logs API
            </x-sidebar-link>

        </div>

        <!-- User Section -->
        @auth
        <div class="mt-8 pt-6 border-t border-[#9C7DC7]">
            <x-sidebar-link href="/profile" :active="request()->is('profile*')">
                <x-slot name="icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </x-slot>
                Paramètres
            </x-sidebar-link>
        </div>
        @endauth
    </nav>
</aside>
