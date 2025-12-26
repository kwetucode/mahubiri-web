<div>
    <!-- Flash Messages -->
    <x-flash-message type="success" :message="session('message')" />
    <x-flash-message type="error" :message="session('error')" />

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-lg mb-6 border border-gray-100">
        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-search-input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Nom, abréviation, ville..."
                    label="Rechercher"
                />
                <x-select-input
                    wire:model.live="filterActive"
                    label="Statut"
                    placeholder="Tous"
                    :isFilter="true"
                >
                    <option value="1">Actif</option>
                    <option value="0">Inactif</option>
                </x-select-input>

                <x-select-input
                    wire:model.live="perPage"
                    label="Par page"
                    placeholder="10"
                >
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </x-select-input>
            </div>
        </div>
    </div>

    <!-- Churches Table -->
    <x-table.table>
        <x-table.header>
            <tr>
                <x-table.head>Église</x-table.head>
                <x-table.head>Visionnaire</x-table.head>
                <x-table.head>Localisation</x-table.head>
                <x-table.head>Créé par</x-table.head>
                <x-table.head>Statut</x-table.head>
                <x-table.head>Actions</x-table.head>
            </tr>
        </x-table.header>

        <x-table.body>
            @forelse($churches as $church)
                <x-table.row>
                    <x-table.cell>
                        <div class="flex items-center">
                            @if($church->logo_url)
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $church->logo_url }}" alt="">
                            @else
                                <div class="h-10 w-10 rounded-full bg-linear-to-br from-[#6B4EAF] to-[#9C7DC7] flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">{{ substr($church->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="ml-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $church->name }}</div>
                                @if($church->abbreviation)
                                    <div class="text-xs text-gray-500">{{ $church->abbreviation }}</div>
                                @endif
                            </div>
                        </div>
                    </x-table.cell>

                    <x-table.cell>
                        {{ $church->visionary_name ?? '-' }}
                    </x-table.cell>

                    <x-table.cell>
                        {{ $church->city ? $church->city . ', ' : '' }}{{ $church->country_name ?? '-' }}
                    </x-table.cell>

                    <x-table.cell>
                        <div class="text-sm font-medium text-gray-900">{{ $church->createdBy->name }}</div>
                        <div class="text-xs text-gray-500">{{ $church->createdBy->email }}</div>
                    </x-table.cell>

                    <x-table.cell>
                        <div class="flex items-center space-x-3">
                            <x-toggle
                                :checked="$church->is_active"
                                wire:click="toggleActive({{ $church->id }})"
                            />
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $church->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $church->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                    </x-table.cell>

                    <x-table.cell>
                        <div class="flex items-center space-x-3">
                            <a
                                href="{{ route('admin.churches.show', $church) }}"
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
                <x-table.empty colspan="6" message="Aucune église trouvée">
                    <x-slot name="icon">
                        <svg class="w-16 h-16 text-[#6B4EAF]/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot>
                    Aucun résultat ne correspond à votre recherche
                </x-table.empty>
            @endforelse
        </x-table.body>

        <x-table.pagination :paginator="$churches" />
    </x-table.table>
</div>
