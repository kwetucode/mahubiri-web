<div>
    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <x-stat-card
            title="Total Églises"
            :value="$stats['churches']"
            color="violet"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Prédicateurs"
            :value="$stats['preachers']"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Utilisateurs"
            :value="$stats['users']"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Total Sermons"
            :value="$stats['sermons']"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Total Lectures"
            :value="number_format($stats['total_plays'])"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Total Favoris"
            :value="number_format($stats['total_favorites'])"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sermons Growth Chart -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Croissance des Sermons (6 derniers mois)
            </h3>
            <div class="h-64 flex items-end justify-between space-x-2">
                @php
                    $maxSermons = !empty($monthly_sermons) ? max($monthly_sermons) : 1;
                    $months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                @endphp
                @for($i = 5; $i >= 0; $i--)
                    @php
                        $date = now()->subMonths($i);
                        $monthKey = $date->format('Y-m');
                        $count = $monthly_sermons[$monthKey] ?? 0;
                        $height = $maxSermons > 0 ? ($count / $maxSermons) * 100 : 0;
                        // Minimum height for visibility
                        $displayHeight = $height > 0 ? max($height, 5) : 0;
                    @endphp
                    <div class="flex-1 flex flex-col items-center">
                        @if($displayHeight > 0)
                            <div class="w-full bg-gradient-to-t from-violet-500 to-violet-400 rounded-t-lg hover:from-violet-600 hover:to-violet-500 transition-all duration-300 relative group"
                                 style="height: {{ $displayHeight }}%">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                    {{ $count }} sermon{{ $count > 1 ? 's' : '' }}
                                </div>
                            </div>
                        @else
                            <div class="w-full bg-gray-200 rounded-t-lg" style="height: 5%">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                    0 sermon
                                </div>
                            </div>
                        @endif
                        <p class="text-xs text-gray-600 mt-2">{{ $months[$date->month - 1] }}</p>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Top Preachers Chart -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Top 5 Prédicateurs (par nombre de sermons)
            </h3>
            <div class="space-y-3">
                @forelse($top_preachers as $preacher)
                    @php
                        $maxCount = $top_preachers->first()->sermons_count ?? 1;
                        $percentage = ($preacher->sermons_count / $maxCount) * 100;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 truncate">{{ Str::limit($preacher->ministry_name, 25) }}</span>
                            <span class="text-sm font-bold text-[#6B4EAF]">{{ $preacher->sermons_count }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-gradient-to-r from-violet-500 to-violet-600 h-2.5 rounded-full transition-all duration-300"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-8">Aucun prédicateur avec des sermons</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Content Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Preachers -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Prédicateurs Récents
                </h3>
                <a href="{{ route('admin.preacher-profiles.index') }}" class="text-sm text-[#6B4EAF] hover:text-[#5A3D94] font-medium">
                    Voir tout →
                </a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recent_preachers as $preacher)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center flex-1 min-w-0">
                                @if($preacher->avatar_url)
                                    <img class="h-10 w-10 rounded-full object-cover shrink-0" src=" {{ $preacher->avatar_url }}" alt="">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-linear-to-br from-violet-500 to-violet-600 flex items-center justify-center shrink-0">
                                        <span class="text-white font-semibold text-sm">{{ substr($preacher->ministry_name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $preacher->ministry_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $preacher->user->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="ml-3 flex items-center space-x-2 shrink-0">
                                @if($preacher->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Actif
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactif
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">
                        Aucun prédicateur trouvé
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Churches -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Églises Récentes
                </h3>
                <a href="{{ route('admin.churches.index') }}" class="text-sm text-[#6B4EAF] hover:text-[#5A3D94] font-medium">
                    Voir tout →
                </a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recent_churches as $church)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center flex-1 min-w-0">
                                @if($church->logo_url)
                                    <img class="h-10 w-10 rounded-full object-cover shrink-0" src="{{ $church->logo_url }}" alt="">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center shrink-0">
                                        <span class="text-gray-500 font-semibold text-sm">{{ substr($church->name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $church->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $church->city ? $church->city . ', ' : '' }}{{ $church->country_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="ml-3 flex items-center space-x-2 shrink-0">
                                @if($church->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-500">
                        Aucune église trouvée
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
