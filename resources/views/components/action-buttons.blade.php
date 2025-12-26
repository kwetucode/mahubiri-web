@props([
    'editAction' => null,
    'deleteAction' => null,
    'viewAction' => null,
    'customActions' => null
])

<div class="flex items-center gap-2">
    @if($viewAction)
        <button
            type="button"
            wire:click="{{ $viewAction }}"
            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
            title="Voir les détails"
        >
            <x-icon name="eye" />
        </button>
    @endif

    @if($editAction)
        <button
            type="button"
            wire:click="{{ $editAction }}"
            class="p-2 text-violet-600 hover:bg-violet-50 rounded-lg transition-colors"
            title="Modifier"
        >
            <x-icon name="edit" />
        </button>
    @endif

    @if($deleteAction)
        <button
            type="button"
            {{ $attributes->merge(['class' => 'p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors']) }}
            title="Supprimer"
        >
            <x-icon name="delete" />
        </button>
    @endif

    @if($customActions)
        {{ $customActions }}
    @endif
</div>
