<div>
    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <form wire:submit.prevent="save">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Informations de l'Église</h3>
            </div>

            <div class="p-6 space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nom de l'église <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 shadow-sm focus:border-[#6B4EAF] focus:ring-2 focus:ring-[#6B4EAF]/20 transition-all duration-200 @error('name') border-red-500 @enderror"
                        placeholder="Entrez le nom de l'église"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Abbreviation & Visionary Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="abbreviation" class="block text-sm font-semibold text-gray-700 mb-2">
                            Abréviation
                        </label>
                        <input
                            type="text"
                            id="abbreviation"
                            wire:model="abbreviation"
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 shadow-sm focus:border-[#6B4EAF] focus:ring-2 focus:ring-[#6B4EAF]/20 transition-all duration-200"
                            placeholder="Ex: CADC"
                        >
                        @error('abbreviation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="visionary_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Nom du Visionnaire
                        </label>
                        <input
                            type="text"
                            id="visionary_name"
                            wire:model="visionary_name"
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 shadow-sm focus:border-[#6B4EAF] focus:ring-2 focus:ring-[#6B4EAF]/20 transition-all duration-200"
                            placeholder="Nom complet du visionnaire"
                        >
                        @error('visionary_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>



                <!-- Location Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="country_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Pays
                        </label>
                        <input
                            type="text"
                            id="country_name"
                            wire:model="country_name"
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 shadow-sm focus:border-[#6B4EAF] focus:ring-2 focus:ring-[#6B4EAF]/20 transition-all duration-200"
                            placeholder="Ex: RDC"
                        >
                        @error('country_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="country_code" class="block text-sm font-semibold text-gray-700 mb-2">
                            Code Pays
                        </label>
                        <input
                            type="text"
                            id="country_code"
                            wire:model="country_code"
                            placeholder="Ex: CD"
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 shadow-sm focus:border-[#6B4EAF] focus:ring-2 focus:ring-[#6B4EAF]/20 transition-all duration-200 uppercase"
                        >
                        @error('country_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                            Ville
                        </label>
                        <input
                            type="text"
                            id="city"
                            wire:model="city"
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 shadow-sm focus:border-[#6B4EAF] focus:ring-2 focus:ring-[#6B4EAF]/20 transition-all duration-200"
                            placeholder="Ex: Kinshasa"
                        >
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                        Adresse complète
                    </label>
                    <input
                        type="text"
                        id="address"
                        wire:model="address"
                        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 shadow-sm focus:border-[#6B4EAF] focus:ring-2 focus:ring-[#6B4EAF]/20 transition-all duration-200"
                        placeholder="Numéro, rue, commune, ville"
                    >
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="flex items-center p-4 bg-[#E6E3F5] rounded-lg">
                    <input
                        type="checkbox"
                        id="is_active"
                        wire:model="is_active"
                        class="h-5 w-5 text-[#6B4EAF] focus:ring-[#6B4EAF] focus:ring-2 border-gray-300 rounded transition-all cursor-pointer"
                    >
                    <label for="is_active" class="ml-3 block text-sm font-medium text-gray-900 cursor-pointer">
                        Église active
                    </label>
                </div>

                <!-- Description (éditeur de texte riche) -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Description
                    </label>
                    <div wire:ignore>
                        <textarea
                            id="description"
                            wire:model="description"
                            rows="8"
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 shadow-sm focus:border-[#6B4EAF] focus:ring-2 focus:ring-[#6B4EAF]/20 transition-all duration-200"
                            placeholder="Décrivez brièvement l'église, sa mission et ses activités..."
                        ></textarea>
                    </div>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        Vous pouvez utiliser du texte enrichi pour formater la description
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                <a
                    href="{{ route('admin.churches.index') }}"
                    class="px-6 py-3 border-2 border-gray-300 rounded-lg shadow-sm text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6B4EAF] transition-all duration-200"
                >
                    Annuler
                </a>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-[#6B4EAF] hover:bg-[#5A3D94] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6B4EAF] disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center"
                >
                    <span wire:loading.remove wire:target="save" class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Enregistrer
                    </span>
                    <span wire:loading wire:target="save" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enregistrement...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
