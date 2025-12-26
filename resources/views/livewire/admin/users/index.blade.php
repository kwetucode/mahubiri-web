<div class="p-6" x-data="{ showDeleteModal: false, userToDelete: null, userName: '' }">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Utilisateurs</h2>
            <p class="mt-1 text-sm text-gray-600">Gérez les utilisateurs de la plateforme</p>
        </div>
        <x-button wire:click="openCreateModal">
            <x-icon name="plus" class="w-5 h-5 mr-2 inline-block" />
            Nouvel Utilisateur
        </x-button>
    </div>

    <!-- Flash Messages -->
    <x-flash-message type="success" :message="session('message')" />
    <x-flash-message type="error" :message="session('error')" />

    <!-- Statistics -->
    <div class="mb-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-{{ $usersByRole->count() + 1 }} gap-3">
        <!-- Total Users Card -->
        <x-stat-card
            title="Total Utilisateurs"
            :value="$totalUsers"
            color="violet"
            size="compact"
        >
            <x-slot name="iconSlot">
                <x-icon name="users" class="w-5 h-5" />
            </x-slot>
        </x-stat-card>

        <!-- Users by Role Cards -->
        @foreach($usersByRole as $role)
            <x-stat-card
                :title="$role->name"
                :value="$role->users_count"
                color="white"
                size="compact"
                class="hover:-translate-y-0.5"
            >
                <x-slot name="iconSlot">
                    <x-icon name="user" class="w-5 h-5" />
                </x-slot>
            </x-stat-card>
        @endforeach
    </div>

    <!-- Search and Filters -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Search -->
        <div>
            <x-search-input
                wire:model.live="search"
                placeholder="Rechercher un utilisateur..."
            />
        </div>

        <!-- Filter by Role -->
        <div>
            <x-select-input
                wire:model.live="filterRole"
                label="Filtrer par rôle"
                placeholder="Tous les rôles"
                :isFilter="true"
            >
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </x-select-input>
        </div>
    </div>

    <!-- Table -->
    <x-table>
        <x-table.header>
            <x-table.head>Nom</x-table.head>
            <x-table.head>Email</x-table.head>
            <x-table.head>Téléphone</x-table.head>
            <x-table.head>Rôle</x-table.head>
            <x-table.head>Date d'inscription</x-table.head>
            <x-table.head>Actions</x-table.head>
        </x-table.header>

        <x-table.body>
            @forelse ($users as $user)
                <x-table.row wire:key="user-{{ $user->id }}">
                    <x-table.cell>
                        <span class="font-medium text-gray-900">{{ $user->name }}</span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-sm text-gray-600">{{ $user->email }}</span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-sm text-gray-600">{{ $user->phone ?? '-' }}</span>
                    </x-table.cell>
                    <x-table.cell>
                        @if($user->role)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">
                                {{ $user->role->name }}
                            </span>
                        @else
                            <span class="text-sm text-gray-400">Aucun rôle</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-sm text-right text-gray-600">{{ $user->created_at->format('d/m/Y') }}</span>
                    </x-table.cell>
                    <x-table.cell>
                        <div class="flex items-center space-x-3">
                            <button
                                wire:click="openEditModal({{ $user->id }})"
                                class="text-violet-600 hover:text-violet-900 transition-colors p-2 rounded-lg hover:bg-violet-50"
                                title="Modifier"
                            >
                                <x-icon name="edit" />
                            </button>
                            @if($user->id !== auth()->id())
                                <button
                                    type="button"
                                    @click="showDeleteModal = true; userToDelete = {{ $user->id }}; userName = '{{ addslashes($user->name) }}'"
                                    class="text-red-600 hover:text-red-900 transition-colors p-2 rounded-lg hover:bg-red-50"
                                    title="Supprimer"
                                >
                                    <x-icon name="delete" />
                                </button>
                            @endif
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty colspan="6">
                    Aucun utilisateur trouvé
                </x-table.empty>
            @endforelse
        </x-table.body>

        <x-table.pagination :paginator="$users" />
    </x-table>

    <!-- Create/Edit Modal -->
    <x-modal name="user-modal" :show="$showModal">
        <div class="bg-linear-to-r from-[#6B4EAF] to-[#9C7DC7] px-6 py-4">
            <h3 class="text-xl font-semibold text-white">
                {{ $userId ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
            </h3>
        </div>

        <form wire:submit.prevent="save" class="p-6">
            <div class="space-y-4">
                <x-text-input
                    wire:model="name"
                    label="Nom complet"
                    placeholder="Ex: Jean Dupont"
                    :error="$errors->first('name')"
                />

                <x-text-input
                    wire:model="email"
                    type="email"
                    label="Email"
                    placeholder="Ex: jean.dupont@example.com"
                    :error="$errors->first('email')"
                />

                <x-text-input
                    wire:model="phone"
                    label="Téléphone"
                    placeholder="Ex: +33 6 12 34 56 78"
                    :error="$errors->first('phone')"
                />

                <x-select-input
                    wire:model="role_id"
                    label="Rôle"
                    placeholder="Sélectionner un rôle"
                    :error="$errors->first('role_id')"
                >
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </x-select-input>

                @if(!$userId)
                    <!-- Message pour la création -->
                    <div class="bg-violet-50 border border-violet-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-violet-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-violet-900">
                                    Mot de passe par défaut
                                </p>
                                <p class="text-sm text-violet-700 mt-1">
                                    L'utilisateur sera créé avec le mot de passe : <span class="font-bold">password123</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <x-button variant="secondary" type="button" wire:click="closeModal">
                    Annuler
                </x-button>
                <x-button type="submit">
                    {{ $userId ? 'Mettre à jour' : 'Créer' }}
                </x-button>
            </div>
        </form>
    </x-modal>

    <!-- Delete Confirmation Modal -->
    <x-delete-confirmation-modal
        show="showDeleteModal"
        itemName="userName"
        itemType="l'utilisateur"
        onConfirm="$wire.delete(userToDelete); showDeleteModal = false; userToDelete = null; userName = ''"
        onCancel="showDeleteModal = false; userToDelete = null; userName = ''"
    />
</div>
