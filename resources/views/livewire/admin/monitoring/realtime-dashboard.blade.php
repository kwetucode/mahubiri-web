<div class="p-6" x-data="{ refreshing: false }" wire:poll.30s>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Monitoring en Temps Réel</h2>
            <p class="mt-1 text-sm text-gray-600">Activité de l'application Flutter en temps réel</p>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-500">
            <svg class="w-4 h-4 animate-pulse text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <circle cx="10" cy="10" r="8"/>
            </svg>
            <span>Actualisation automatique (30s)</span>
        </div>
    </div>

    <!-- Stats en temps réel - Dernières 24h -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <x-stat-card
            title="Utilisateurs Actifs"
            :value="$realtimeStats['active_users_today']"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Nouveaux Inscrits"
            :value="$realtimeStats['new_users_today']"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Sermons Uploadés"
            :value="$realtimeStats['sermons_uploaded_today']"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Total Écoutes"
            :value="$realtimeStats['total_plays_today']"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Églises Actives"
            :value="$realtimeStats['active_churches_today']"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    <!-- Pics d'activité -->
    @if($peakActivity['peak_hour_today'] || $peakActivity['most_active_church'] || $peakActivity['most_listened_sermon'])
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @if($peakActivity['peak_hour_today'])
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium opacity-90">Heure de Pic</h3>
                <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-3xl font-bold">{{ $peakActivity['peak_hour_today']['hour'] }}</p>
            <p class="text-sm opacity-90">{{ $peakActivity['peak_hour_today']['count'] }} écoutes</p>
        </div>
        @endif

        @if($peakActivity['most_active_church'])
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium opacity-90">Église la Plus Active</h3>
                <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <p class="text-lg font-bold truncate">{{ $peakActivity['most_active_church']['name'] }}</p>
            <p class="text-sm opacity-90">{{ $peakActivity['most_active_church']['play_count'] }} écoutes</p>
        </div>
        @endif

        @if($peakActivity['most_listened_sermon'])
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium opacity-90">Sermon Tendance</h3>
                <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <p class="text-lg font-bold truncate">{{ $peakActivity['most_listened_sermon']['title'] }}</p>
            <p class="text-sm opacity-90">{{ $peakActivity['most_listened_sermon']['play_count'] }} écoutes</p>
        </div>
        @endif
    </div>
    @endif

    <!-- Activités récentes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Inscriptions récentes -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Inscriptions Récentes
                </h3>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                @forelse($recentActivity['recent_registrations'] as $user)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-600 font-semibold text-sm">{{ substr($user['name'], 0, 2) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $user['role'] }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $user['time_ago'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-8">Aucune inscription récente</p>
                @endforelse
            </div>
        </div>

        <!-- Uploads récents -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Uploads Récents
                </h3>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                @forelse($recentActivity['recent_uploads'] as $sermon)
                    <div class="py-3 border-b border-gray-100 last:border-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $sermon['title'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $sermon['church_name'] }} • {{ $sermon['size_mb'] }} MB</p>
                            </div>
                            <span class="text-xs text-gray-500 ml-2">{{ $sermon['time_ago'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-8">Aucun upload récent</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Écoutes en direct -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Écoutes en Direct
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($recentActivity['recent_plays'] as $play)
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3 flex-1">
                            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $play['sermon_title'] }}</p>
                                <p class="text-xs text-gray-500">par {{ $play['user_name'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 text-xs text-gray-500">
                            @if($play['duration_formatted'])
                                <span class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $play['duration_formatted'] }}
                                </span>
                            @endif
                            <span>{{ $play['time_ago'] }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-8">Aucune écoute en cours</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
