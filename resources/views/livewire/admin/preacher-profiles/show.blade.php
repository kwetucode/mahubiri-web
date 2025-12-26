<div>
    <!-- Header avec informations principales -->
    <div class="bg-white rounded-xl shadow-lg mb-6 border border-gray-100 overflow-hidden">
        <div class="bg-linear-to-r from-[#6B4EAF] to-[#5A3D94] px-6 py-8">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-6">
                    @if($preacherProfile->avatar_url)
                        <img src="{{ asset($preacherProfile->avatar_url) }}" alt="{{ $preacherProfile->ministry_name }}" class="h-24 w-24 rounded-xl object-cover border-4 border-white shadow-lg">
                    @else
                        <div class="h-24 w-24 rounded-xl bg-white flex items-center justify-center border-4 border-white shadow-lg">
                            <span class="text-[#6B4EAF] font-bold text-3xl">{{ substr($preacherProfile->ministry_name, 0, 1) }}</span>
                        </div>
                    @endif

                    <div class="text-white">
                        <h1 class="text-3xl font-bold mb-2">{{ $preacherProfile->ministry_name }}</h1>
                        <p class="text-[#E6E3F5] text-lg mb-2">{{ $preacherProfile->ministry_type_description ?? ucfirst($preacherProfile->ministry_type) }}</p>
                        @if($preacherProfile->user)
                            <p class="text-[#E6E3F5] flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Prédicateur: {{ $preacherProfile->user->name }}
                            </p>
                        @endif
                    </div>
                </div>

                <div>
                    @if($preacherProfile->is_active)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800 border-2 border-white shadow">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Actif
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800 border-2 border-white shadow">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            Inactif
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                        <p>{{ $preacherProfile->full_location ?? 'Non spécifié' }}</p>
                    </div>
                </div>

                <!-- Utilisateur -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Compte utilisateur
                    </h3>
                    <div class="text-sm text-gray-600">
                        <p class="font-medium text-gray-900">{{ $preacherProfile->user->name }}</p>
                        <p>{{ $preacherProfile->user->email }}</p>
                        @if($preacherProfile->user->phone)
                            <p>{{ $preacherProfile->user->phone }}</p>
                        @endif
                    </div>
                </div>

                <!-- Membre depuis -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Membre depuis
                    </h3>
                    <div class="text-sm text-gray-600">
                        <p class="font-medium text-gray-900">{{ $preacherProfile->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Créé le {{ $preacherProfile->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Social Links -->
            @if($preacherProfile->social_links && count($preacherProfile->social_links) > 0)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#6B4EAF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Liens Sociaux
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach($preacherProfile->social_links as $platform => $url)
                            @if($url)
                                <a href="{{ $url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm text-gray-700 transition-colors">
                                    <span class="capitalize font-medium">{{ $platform }}</span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
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
            <a href="{{ route('preacher-profiles.index') }}" class="text-sm text-[#6B4EAF] hover:text-[#5A3D94] font-medium">
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
                                    @if($sermon->preacher_name)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ $sermon->preacher_name }}
                                        </span>
                                    @endif
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
                                    @if($sermon->duration)
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ gmdate('H:i:s', $sermon->duration) }}
                                        </span>
                                    @endif
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
                <p class="text-sm text-gray-500 mt-1">Ce prédicateur n'a pas encore de sermons enregistrés.</p>
            </div>
        @endif
    </div>
</div>
