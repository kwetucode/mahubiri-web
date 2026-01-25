<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Monitoring du Stockage</h2>
            <p class="mt-1 text-sm text-gray-600">Surveillance du quota de 3 GB par église</p>
        </div>
        <button wire:click="$refresh" class="rounded-lg bg-[#6B4EAF] px-4 py-2 text-white hover:bg-[#5A3D94] transition-colors">
            <svg class="inline h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Actualiser
        </button>
    </div>

    <!-- Global Overview -->
    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">
        <x-stat-card
            title="Total Églises"
            :value="count($churchStorageUsage)"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Stockage Total Utilisé"
            :value="number_format(collect($churchStorageUsage)->sum('used_bytes') / 1024 / 1024 / 1024, 2) . ' GB'"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Églises à Risque"
            :value="collect($churchStorageUsage)->where('status', 'critical')->count()"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </x-slot>
            <x-slot name="footer">
                <span class="text-xs text-gray-500">&gt;90% quota utilisé</span>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Utilisation Moyenne"
            :value="number_format(collect($churchStorageUsage)->avg('percentage_used'), 1) . '%'"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    <!-- Filters -->
    <div class="mb-4 flex gap-4">
        <input wire:model.live="search" type="text" placeholder="Rechercher une église..." 
               class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:border-[#6B4EAF] focus:ring-[#6B4EAF]">
        
        <select wire:model.live="filterStatus" class="rounded-lg border border-gray-300 px-4 py-2 focus:border-[#6B4EAF] focus:ring-[#6B4EAF]">
            <option value="">Tous les Statuts</option>
            <option value="normal">Normal</option>
            <option value="warning">Avertissement</option>
            <option value="critical">Critique</option>
        </select>

        <select wire:model.live="sortBy" class="rounded-lg border border-gray-300 px-4 py-2 focus:border-[#6B4EAF] focus:ring-[#6B4EAF]">
            <option value="usage_desc">Utilisation (Élevée → Faible)</option>
            <option value="usage_asc">Utilisation (Faible → Élevée)</option>
            <option value="name_asc">Nom (A-Z)</option>
            <option value="sermons_desc">Sermons (Plus)</option>
        </select>
    </div>

    <!-- Church Storage Table -->
    <div class="mb-8 overflow-x-auto rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Église</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sermons</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Utilisé</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Quota</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Utilisation %</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Restant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($churchStorageUsage as $church)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $church['church_name'] }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ number_format($church['sermon_count']) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ number_format($church['used_bytes'] / 1024 / 1024, 2) }} MB
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ number_format($church['quota_bytes'] / 1024 / 1024 / 1024, 1) }} GB
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full rounded-full {{ $church['status'] === 'critical' ? 'bg-red-600' : ($church['status'] === 'warning' ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                         style="width: {{ min($church['percentage_used'], 100) }}%"></div>
                                </div>
                                <span class="text-sm {{ $church['status'] === 'critical' ? 'text-red-600 font-bold' : ($church['status'] === 'warning' ? 'text-yellow-600 font-semibold' : 'text-gray-500') }}">
                                    {{ number_format($church['percentage_used'], 1) }}%
                                </span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ number_format($church['remaining_bytes'] / 1024 / 1024, 2) }} MB
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $church['status'] === 'critical' ? 'bg-red-100 text-red-800' : ($church['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ ucfirst($church['status']) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Aucune église trouvée</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Saturation Forecast -->
    @if(count($saturationForecast) > 0)
        <div class="mb-8">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Prévision de Saturation</h3>
            <div class="rounded-lg bg-white p-6 shadow">
                <div class="space-y-3">
                    @foreach($saturationForecast as $forecast)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $forecast['church_name'] }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ number_format($forecast['avg_daily_upload'] / 1024 / 1024, 2) }} MB/jour en moyenne
                                </p>
                            </div>
                            <div class="text-right">
                                @if($forecast['days_until_full'] !== null)
                                    <p class="text-lg font-bold {{ $forecast['days_until_full'] < 30 ? 'text-red-600' : ($forecast['days_until_full'] < 90 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ $forecast['days_until_full'] }} jours
                                    </p>
                                    <p class="text-xs text-gray-500">avant saturation</p>
                                @else
                                    <p class="text-sm text-gray-500">Aucune prévision disponible</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Largest Sermons -->
    <div>
        <h3 class="mb-4 text-lg font-semibold text-gray-900">Top 10 Plus Gros Sermons</h3>
        <div class="overflow-x-auto rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sermon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Église</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Taille</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Uploadé</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($largestSermons as $index => $sermon)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ Str::limit($sermon->title, 50) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $sermon->church->name ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ number_format($sermon->size / 1024 / 1024, 2) }} MB
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $sermon->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Aucun sermon trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
