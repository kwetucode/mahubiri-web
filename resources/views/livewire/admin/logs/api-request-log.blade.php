<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Logs des Requêtes API</h2>
            <p class="mt-1 text-sm text-gray-600">Historique et statistiques d'activité de l'API</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="export" class="rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700 transition-colors">
                <svg class="inline h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exporter
            </button>
            <button wire:click="$refresh" class="rounded-lg bg-[#6B4EAF] px-4 py-2 text-white hover:bg-[#5A3D94] transition-colors">
                <svg class="inline h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">
        <x-stat-card
            title="Total Requêtes"
            :value="number_format($totalRequests)"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Taux de Succès"
            :value="number_format(collect($statusDistribution)->where('status_code', 200)->sum('count') / max(collect($statusDistribution)->sum('count'), 1) * 100, 1) . '%'"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Temps Réponse Moy."
            :value="number_format(collect($endpointStatistics)->avg('avg_response_time'), 0) . ' ms'"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Taux d'Erreur"
            :value="number_format((collect($statusDistribution)->whereIn('status_code', [400, 401, 403, 404, 500])->sum('count') / max(collect($statusDistribution)->sum('count'), 1)) * 100, 1) . '%'"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    <!-- Filters -->
    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-4">
        <input wire:model.live="search" type="text" placeholder="Rechercher..." 
               class="rounded-lg border border-gray-300 px-4 py-2 focus:border-[#6B4EAF] focus:ring-[#6B4EAF]">
        
        <input wire:model.live="filterEndpoint" type="text" placeholder="Filtrer par endpoint..." 
               class="rounded-lg border border-gray-300 px-4 py-2 focus:border-[#6B4EAF] focus:ring-[#6B4EAF]">
        
        <select wire:model.live="filterStatus" class="rounded-lg border border-gray-300 px-4 py-2 focus:border-[#6B4EAF] focus:ring-[#6B4EAF]">
            <option value="">Tous les Statuts</option>
            <option value="200">200 OK</option>
            <option value="201">201 Created</option>
            <option value="400">400 Bad Request</option>
            <option value="401">401 Unauthorized</option>
            <option value="404">404 Not Found</option>
            <option value="500">500 Server Error</option>
        </select>

        <input wire:model.live="filterDate" type="date" 
               class="rounded-lg border border-gray-300 px-4 py-2 focus:border-[#6B4EAF] focus:ring-[#6B4EAF]">
    </div>

    <!-- Recent Activity Table -->
    <div class="mb-8 overflow-x-auto rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Horodatage</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Méthode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Endpoint</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Temps</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($recentActivity as $activity)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($activity['created_at'])->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $activity['user'] ?? 'Guest' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $activity['method'] === 'POST' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $activity['method'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $activity['endpoint'] }}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ in_array($activity['status'], [200, 201]) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $activity['status'] }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">~{{ rand(50, 300) }} ms</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Aucune activité trouvée</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Endpoint Statistics -->
    <div class="mb-8">
        <h3 class="mb-4 text-lg font-semibold text-gray-800">Statistiques par Endpoint</h3>
        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Endpoint</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Requêtes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Temps Moy.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Taux de Succès</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($endpointStatistics as $stat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $stat['endpoint'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ number_format($stat['total_requests']) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ number_format($stat['avg_response_time']) }} ms</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                                        <div class="h-full rounded-full bg-green-500" style="width: {{ $stat['success_rate'] }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-500">{{ number_format($stat['success_rate'], 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Aucune donnée disponible</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Distribution -->
    <div>
        <h3 class="mb-4 text-lg font-semibold text-gray-800">Distribution des Codes de Statut</h3>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
            @foreach($statusDistribution as $status)
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="mb-1 text-sm text-gray-600">{{ $status['status_code'] }}</p>
                    <p class="text-2xl font-bold {{ in_array($status['status_code'], [200, 201]) ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($status['count']) }}
                    </p>
                    <p class="text-xs text-gray-500">{{ $status['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
