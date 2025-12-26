<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Mahubiri' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 overflow-hidden" 
      x-data="{ 
        sidebarOpen: false, 
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        toggleSidebar() {
          this.sidebarCollapsed = !this.sidebarCollapsed;
          localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
        }
      }">
    <div class="h-screen flex">
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-20 lg:hidden"></div>

        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="flex-1 ml-0 h-screen overflow-y-auto flex flex-col transition-all duration-300"
             :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'">
            <!-- Header -->
            <header class="bg-white shadow-sm sticky top-0 z-10">
                <div class="px-4 lg:px-8 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Hamburger Button -->
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div>
                            @if(isset($title))
                                <h2 class="text-lg lg:text-xl font-semibold text-gray-800">{{ $title }}</h2>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 lg:space-x-4">
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name ?? 'Utilisateur' }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email ?? '' }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <!-- Notifications Dropdown -->
                            @livewire('admin.notification-dropdown')
                            
                            <a href="{{ route('admin.logout') }}" class="p-2 rounded-full hover:bg-gray-100 transition-colors" title="Déconnexion">
                                <x-icon name="logout" class="w-5 h-5 text-gray-600" />
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 lg:p-8 flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="px-8 py-6">
                    <div class="flex justify-between items-center text-sm text-gray-600">
                        <p>&copy; {{ date('Y') }} Mahubiri. Tous droits réservés.</p>
                        <div class="flex space-x-4">
                            <a href="/terms" class="hover:text-[#6B4EAF]">Conditions</a>
                            <a href="/privacy" class="hover:text-[#6B4EAF]">Confidentialité</a>
                            <a href="/contact" class="hover:text-[#6B4EAF]">Contact</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @livewireScripts
</body>
</html>
