<div class="p-6" x-data="{ showDeleteModal: false, categoryToDelete: null, categoryName: '' }">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Catégories de Sermons</h2>
            <p class="mt-1 text-sm text-gray-600">Gérez les catégories des sermons</p>
        </div>
        <x-button wire:click="openCreateModal">
            <x-icon name="plus" class="w-5 h-5 mr-2 inline-block" />
            Nouvelle Catégorie
        </x-button>
    </div>

    <!-- Flash Messages -->
    <x-flash-message type="success" :message="session('message')" />
    <x-flash-message type="error" :message="session('error')" />

    <!-- Search -->
    <div class="mb-6">
        <x-search-input
            wire:model.live="search"
            placeholder="Rechercher une catégorie..."
        />
    </div>

    <!-- Table -->
    <x-table>
        <x-table.header>
            <x-table.head>Nom</x-table.head>
            <x-table.head>Nombre de Sermons</x-table.head>
            <x-table.head>Date de création</x-table.head>
            <x-table.head>Actions</x-table.head>
        </x-table.header>

        <x-table.body>
            @forelse ($categories as $category)
                <x-table.row wire:key="category-{{ $category->id }}">
                    <x-table.cell>
                        <span class="font-medium text-gray-900">{{ $category->name }}</span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">
                            {{ $category->sermons_count }} {{ $category->sermons_count > 1 ? 'sermons' : 'sermon' }}
                        </span>
                    </x-table.cell>
                    <x-table.cell>
                        <span class="text-sm text-gray-600">{{ $category->created_at->format('d/m/Y') }}</span>
                    </x-table.cell>
                    <x-table.cell>
                        <div class="flex items-center space-x-3">
                            <button
                                wire:click="openEditModal({{ $category->id }})"
                                class="text-violet-600 hover:text-violet-900 transition-colors p-2 rounded-lg hover:bg-violet-50"
                                title="Modifier"
                            >
                                <x-icon name="edit" />
                            </button>
                            @if($category->sermons_count == 0)
                                <button
                                    type="button"
                                    @click="showDeleteModal = true; categoryToDelete = {{ $category->id }}; categoryName = '{{ addslashes($category->name) }}'"
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
                    Aucune catégorie trouvée
                </x-table.empty>
            @endforelse
        </x-table.body>

        <x-table.pagination :paginator="$categories" />
    </x-table>

    <!-- Modal -->
    <x-modal name="category-modal" :show="$showModal">
        <div class="bg-linear-to-r from-[#6B4EAF] to-[#9C7DC7] px-6 py-4">
            <h3 class="text-xl font-semibold text-white">
                {{ $categoryId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
            </h3>
        </div>

        <form wire:submit.prevent="save" class="p-6">
            <div class="space-y-4">
                <x-text-input
                    wire:model="name"
                    label="Nom de la catégorie"
                    placeholder="Ex: Doctrine, Foi, Espérance..."
                    :error="$errors->first('name')"
                />
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <x-button variant="secondary" type="button" wire:click="closeModal">
                    Annuler
                </x-button>
                <x-button type="submit">
                    {{ $categoryId ? 'Mettre à jour' : 'Créer' }}
                </x-button>
            </div>
        </form>
    </x-modal>

    <!-- Delete Confirmation Modal -->
    <x-delete-confirmation-modal
        show="showDeleteModal"
        itemName="categoryName"
        itemType="la catégorie"
        onConfirm="$wire.delete(categoryToDelete); showDeleteModal = false; categoryToDelete = null; categoryName = ''"
        onCancel="showDeleteModal = false; categoryToDelete = null; categoryName = ''"
    />
</div>
