<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Analytiques Utilisateurs</h2>
            <p class="mt-1 text-sm text-gray-600">Métriques de rétention et engagement des utilisateurs</p>
        </div>
        
        <div class="flex gap-2">
            <button wire:click="$set('period', 7)" 
                    class="px-4 py-2 rounded-lg transition-colors {{ $period === 7 ? 'bg-[#6B4EAF] text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300' }}">
                7 Jours
            </button>
            <button wire:click="$set('period', 14)" 
                    class="px-4 py-2 rounded-lg transition-colors {{ $period === 14 ? 'bg-[#6B4EAF] text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300' }}">
                14 Jours
            </button>
            <button wire:click="$set('period', 30)" 
                    class="px-4 py-2 rounded-lg transition-colors {{ $period === 30 ? 'bg-[#6B4EAF] text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300' }}">
                30 Jours
            </button>
            <button wire:click="$set('period', 90)" 
                    class="px-4 py-2 rounded-lg transition-colors {{ $period === 90 ? 'bg-[#6B4EAF] text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300' }}">
                90 Jours
            </button>
        </div>
    </div>

    <!-- Retention Metrics -->
    <div class="mb-8">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Métriques de Rétention</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <x-stat-card
                title="Rétention J+1"
                :value="number_format($retentionMetrics['retention_d1'], 1) . '%'"
                color="white"
                size="large">
                <x-slot name="iconSlot">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </x-slot>
                <x-slot name="footer">
                    <span class="text-xs text-gray-500">{{ $retentionMetrics['returning_d1'] }}/{{ $retentionMetrics['new_users'] }} utilisateurs</span>
                </x-slot>
            </x-stat-card>

            <x-stat-card
                title="Rétention J+7"
                :value="number_format($retentionMetrics['retention_d7'], 1) . '%'"
                color="white"
                size="large">
                <x-slot name="iconSlot">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot>
                <x-slot name="footer">
                    <span class="text-xs text-gray-500">{{ $retentionMetrics['returning_d7'] }}/{{ $retentionMetrics['new_users'] }} utilisateurs</span>
                </x-slot>
            </x-stat-card>

            <x-stat-card
                title="Rétention J+30"
                :value="number_format($retentionMetrics['retention_d30'], 1) . '%'"
                color="white"
                size="large">
                <x-slot name="iconSlot">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </x-slot>
                <x-slot name="footer">
                    <span class="text-xs text-gray-500">{{ $retentionMetrics['returning_d30'] }}/{{ $retentionMetrics['new_users'] }} utilisateurs</span>
                </x-slot>
            </x-stat-card>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="mb-8">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Métriques d'Engagement</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <x-stat-card
                title="Utilisateurs Actifs"
                :value="number_format($engagementMetrics['active_users'])"
                color="white"
                size="compact">
                <x-slot name="iconSlot">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </x-slot>
            </x-stat-card>

            <x-stat-card
                title="Sessions/Utilisateur"
                :value="number_format($engagementMetrics['avg_sessions'], 1)"
                color="white"
                size="compact">
                <x-slot name="iconSlot">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </x-slot>
            </x-stat-card>

            <x-stat-card
                title="Écoutes/Session"
                :value="number_format($engagementMetrics['avg_plays_per_session'], 1)"
                color="white"
                size="compact">
                <x-slot name="iconSlot">
                    <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot>
            </x-stat-card>

            <x-stat-card
                title="Taux de Favoris"
                :value="number_format($engagementMetrics['favorite_rate'], 1) . '%'"
                color="white"
                size="compact">
                <x-slot name="iconSlot">
                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </x-slot>
            </x-stat-card>
        </div>
    </div>

    <!-- Conversion Stats -->
    <div class="mb-8">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Statistiques de Conversion</h3>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div>
                    <p class="mb-2 text-sm text-gray-600">Nouveaux Utilisateurs</p>
                    <p class="mb-1 text-3xl font-bold text-gray-900">{{ number_format($conversionStats['new_users']) }}</p>
                    <p class="text-xs text-gray-500">Inscrits dans la période</p>
                </div>
                <div>
                    <p class="mb-2 text-sm text-gray-600">Écoute Premier Jour</p>
                    <p class="mb-1 text-3xl font-bold text-gray-900">{{ number_format($conversionStats['first_day_listeners']) }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($conversionStats['first_day_listener_rate'], 1) }}% des nouveaux utilisateurs</p>
                </div>
                <div>
                    <p class="mb-2 text-sm text-gray-600">Églises Activées</p>
                    <p class="mb-1 text-3xl font-bold text-gray-900">{{ number_format($conversionStats['activated_churches']) }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($conversionStats['church_activation_rate'], 1) }}% ont uploadé un sermon</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Segmentation -->
    <div class="mb-8">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Activité par Rôle</h3>
        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Utilisateurs</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actifs</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Écoutes Moy.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Favoris Moy.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($roleSegmentation as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $role['role_name'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ number_format($role['total_users']) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                {{ number_format($role['active_users']) }}
                                <span class="text-xs text-gray-400">({{ number_format($role['activity_rate'], 1) }}%)</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ number_format($role['avg_plays'], 1) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ number_format($role['avg_favorites'], 1) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Aucune donnée disponible</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Users -->
    <div>
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Top 10 Utilisateurs les Plus Actifs</h3>
        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Rôle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Écoutes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Favoris</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Dernière Activité</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($topUsers as $index => $user)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $user->role?->name ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ number_format($user->views_count) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ number_format($user->favorites_count) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $user->last_view_at ? \Carbon\Carbon::parse($user->last_view_at)->diffForHumans() : 'Jamais' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Aucun utilisateur trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
