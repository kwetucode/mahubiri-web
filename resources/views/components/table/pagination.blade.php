@props(['paginator'])

<div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
    {{ $paginator->links('vendor.livewire.tailwind') }}
</div>
