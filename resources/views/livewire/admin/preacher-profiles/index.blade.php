<div class="p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Profils de Prédicateurs</h2>
        <p class="text-gray-600">Gérer les profils de prédicateurs indépendants</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <x-flash-message type="success" :message="session('success')" />
    @endif

    @if (session()->has('error'))
        <x-flash-message type="error" :message="session('error')" />
    @endif

    <!-- Statistics -->
    <div class="mb-6 grid grid-cols-2 md:grid-cols-3 gap-3">
        <x-stat-card
            title="Total Prédicateurs"
            :value="$totalPreachers"
            color="violet"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Actifs"
            :value="$activePreachers"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            title="Inactifs"
            :value="$inactivePreachers"
            color="white"
            size="compact">
            <x-slot name="iconSlot">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    <!-- Search and Filters -->
    <div class="mb-6 flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <x-search-input
                wire:model.live.debounce.300ms="search"
                placeholder="Rechercher par nom de ministère, nom ou email..."
            />
        </div>
        <div class="w-full md:w-64">
            <x-select-input
                wire:model.live="filterMinistryType"
                label="Type de ministère"
                :isFilter="true"
            >
                <option value="">Tous les types</option>
                @foreach($ministryTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select-input>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <x-table>
            <x-table.header>
                <x-table.head>Avatar</x-table.head>
                <x-table.head>Nom du Ministère</x-table.head>
                <x-table.head>Type</x-table.head>
                <x-table.head>Utilisateur</x-table.head>
                <x-table.head>Sermons</x-table.head>
                <x-table.head>Localisation</x-table.head>
                <x-table.head>Statut</x-table.head>
                <x-table.head>Actions</x-table.head>
            </x-table.header>

            <x-table.body>
                @forelse($preachers as $preacher)
                    <x-table.row>
                        <x-table.cell>
                            @if($preacher->avatar_url)
                                <img src="{{ asset($preacher->avatar_url) }}" alt="{{ $preacher->ministry_name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 rounded-full bg-violet-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                        </x-table.cell>
                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $preacher->ministry_name }}</div>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-violet-100 text-violet-800">
                                {{ $ministryTypes[$preacher->ministry_type] ?? $preacher->ministry_type }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">{{ $preacher->user->name }}</div>
                                <div class="text-gray-500">{{ $preacher->user->email }}</div>
                            </div>
                        </x-table.cell>
                        <x-table.cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $preacher->sermons_count }}
                            </span>
                        </x-table.cell>
                        <x-table.cell>
                            <div class="text-sm text-gray-600">
                                {{ $preacher->full_location ?? 'Non spécifié' }}
                            </div>
                        </x-table.cell>
                        <x-table.cell>
                            @if($preacher->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Actif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    Inactif
                                </span>
                            @endif
                        </x-table.cell>
                        <x-table.cell>
                            <div class="flex items-center space-x-3">
                                <x-toggle
                                    :checked="$preacher->is_active"
                                    wire:click="toggleActive({{ $preacher->id }})"
                                />
                                <a
                                    href="{{ route('preacher-profiles.show', $preacher) }}"
                                    class="text-blue-600 hover:text-blue-900 transition-colors"
                                    title="Voir les détails"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="8" class="text-center py-8">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="mt-2">Aucun profil de prédicateur trouvé</p>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table>

        <x-table.pagination :paginator="$preachers" />
    </div>
</div>
