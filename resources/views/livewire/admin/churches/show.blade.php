<div>
    <!-- Header avec informations principales -->
    <div class="bg-white rounded-xl shadow-lg mb-6 border border-gray-100 overflow-hidden">
        <div class="bg-linear-to-r from-[#6B4EAF] to-[#5A3D94] px-6 py-8">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-6">
                    @if($church->logo_url)
                        <img src="{{ $church->logo_url }}" alt="{{ $church->name }}" class="h-24 w-24 rounded-xl object-cover border-4 border-white shadow-lg">
                    @else
                        <div class="h-24 w-24 rounded-xl bg-white flex items-center justify-center border-4 border-white shadow-lg">
                            <span class="text-[#6B4EAF] font-bold text-3xl">{{ substr($church->name, 0, 1) }}</span>
                        </div>
                    @endif

                    <div class="text-white">
                        <h1 class="text-3xl font-bold mb-2">{{ $church->name }}</h1>
                        @if($church->abbreviation)
                            <p class="text-[#E6E3F5] text-lg mb-2">{{ $church->abbreviation }}</p>
                        @endif
                        @if($church->visionary_name)
                            <p class="text-[#E6E3F5] flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Visionnaire: {{ $church->visionary_name }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Localisation -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Localisation
                    </h3>
                    <div class="text-sm text-gray-600 space-y-1">
                        @if($church->address)
                            <p>{{ $church->address }}</p>
                        @endif
                        <p>{{ $church->city ?? '-' }}, {{ $church->country_name ?? '-' }}</p>
                    </div>
                </div>

                <!-- Créé par -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Créé par
                    </h3>
                    <div class="text-sm text-gray-600">
                        <p class="font-medium text-gray-900">{{ $church->createdBy->name }}</p>
                        <p>{{ $church->createdBy->email }}</p>
                        <p class="text-xs text-gray-500 mt-1">Le {{ $church->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <x-stat-card
            title="Total Sermons"
            :value="number_format($stats['total_sermons'])"
            color="violet">
            <x-slot name="iconSlot">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Sermons Actifs"
            :value="number_format($stats['active_sermons'])"
            color="green">
            <x-slot name="iconSlot">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Total Lectures"
            :value="number_format($stats['total_plays'])"
            color="blue">
            <x-slot name="iconSlot">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Total Favoris"
            :value="number_format($stats['total_favorites'])"
            color="red">
            <x-slot name="iconSlot">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    <!-- Liste des sermons -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">Sermons Récents</h2>
            <a href="{{ route('admin.churches.index') }}" class="text-sm text-[#6B4EAF] hover:text-[#5A3D94] font-medium">
                ← Retour à la liste
            </a>
        </div>

        @if($sermons->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($sermons as $sermon)
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $sermon->title }}</h3>
                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                    @if($sermon->category)
                                        <span class="px-2 py-1 bg-[#E6E3F5] text-[#6B4EAF] rounded">
                                            {{ $sermon->category->name }}
                                        </span>
                                    @endif
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $sermon->created_at->format('d/m/Y') }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ number_format($sermon->views_count ?? 0) }} lectures
                                    </span>
                                    @if($sermon->audio_format || $sermon->mime_type)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            {{ strtoupper($sermon->audio_format ?? ($sermon->mime_type ? str_replace('audio/', '', $sermon->mime_type) : 'N/A')) }}
                                        </span>
                                    @endif
                                    @if($sermon->size)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                            </svg>
                                            {{ number_format($sermon->size / 1048576, 2) }} MB
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sermon->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $sermon->is_published ? 'Publié' : 'Brouillon' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
                <p class="text-lg font-medium text-gray-600">Aucun sermon disponible</p>
                <p class="text-sm text-gray-500 mt-1">Cette église n'a pas encore de sermons enregistrés.</p>
            </div>
        @endif
    </div>
</div>
