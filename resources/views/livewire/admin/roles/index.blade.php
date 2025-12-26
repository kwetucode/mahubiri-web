<div class="p-6" x-data="{ showDeleteModal: false, roleToDelete: null, roleName: '' }">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Rôles</h2>
            <p class="mt-1 text-sm text-gray-600">Gérez les rôles des utilisateurs</p>
        </div>
        <x-button wire:click="openCreateModal">
            <x-icon name="plus" class="w-5 h-5 mr-2 inline-block" />
            Nouveau Rôle
        </x-button>
    </div>

    <!-- Flash Messages -->
    <x-flash-message type="success" :message="session('message')" />
    <x-flash-message type="error" :message="session('error')" />

    <!-- Search -->
    <div class="mb-6">
        <x-search-input
            wire:model.live="search"
            placeholder="Rechercher un rôle..."
        />
    </div>

    <!-- Table -->
    <x-table>
        <x-table.header>
            <x-table.head>Nom</x-table.head>
            <x-table.head>Nombre d'utilisateurs</x-table.head>
            <x-table.head>Actions</x-table.head>
        </x-table.header>

        <x-table.body>
            @forelse ($roles as $role)
                <x-table.row wire:key="role-{{ $role->id }}">
                    <x-table.cell>
                        <span class="font-medium text-gray-900">{{ $role->name }}</span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">
                            {{ $role->users_count }} {{ $role->users_count > 1 ? 'utilisateurs' : 'utilisateur' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <div class="flex items-center space-x-3">
                            <button
                                wire:click="openEditModal({{ $role->id }})"
                                class="text-violet-600 hover:text-violet-900 transition-colors p-2 rounded-lg hover:bg-violet-50"
                                title="Modifier"
                            >
                                <x-icon name="edit" />
                            </button>
                            @if($role->users_count == 0)
                                <button
                                    type="button"
                                    @click="showDeleteModal = true; roleToDelete = {{ $role->id }}; roleName = '{{ addslashes($role->name) }}'"
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
                <x-table.empty colspan="4">
                    Aucun rôle trouvé
                </x-table.empty>
            @endforelse
        </x-table.body>

        <x-table.pagination :paginator="$roles" />
    </x-table>

    <!-- Modal -->
    <x-modal name="role-modal" :show="$showModal">
        <div class="bg-linear-to-r from-[#6B4EAF] to-[#9C7DC7] px-6 py-4">
            <h3 class="text-xl font-semibold text-white">
                {{ $roleId ? 'Modifier le rôle' : 'Nouveau rôle' }}
            </h3>
        </div>

        <form wire:submit.prevent="save" class="p-6">
            <div class="space-y-4">
                <x-text-input
                    wire:model="name"
                    label="Nom du rôle"
                    placeholder="Ex: admin, moderator, user..."
                    :error="$errors->first('name')"
                />
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <x-button variant="secondary" type="button" wire:click="closeModal">
                    Annuler
                </x-button>
                <x-button type="submit">
                    {{ $roleId ? 'Mettre à jour' : 'Créer' }}
                </x-button>
            </div>
        </form>
    </x-modal>

    <!-- Delete Confirmation Modal -->
    <x-delete-confirmation-modal
        show="showDeleteModal"
        itemName="roleName"
        itemType="le rôle"
        onConfirm="$wire.delete(roleToDelete); showDeleteModal = false; roleToDelete = null; roleName = ''"
        onCancel="showDeleteModal = false; roleToDelete = null; roleName = ''"
    />
</div>
