<div>
    <style>
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>

    <div class="animate-pulse">
        <!-- Skeleton loader -->
        <div class="flex items-center justify-center py-8">
            <div class="text-center">
                <!-- Spinner animé -->
                <div class="inline-block relative w-12 h-12">
                    <div class="absolute border-4 border-violet-200 rounded-full w-12 h-12"></div>
                    <div class="absolute border-4 border-violet-600 border-t-transparent rounded-full w-12 h-12 animate-spin"></div>
                </div>
                
                <!-- Texte de chargement -->
                <p class="mt-4 text-sm text-gray-500 font-medium">
                    Chargement en cours...
                </p>
                
                <!-- Barre de progression optionnelle -->
                <div class="mt-3 w-48 mx-auto">
                    <div class="h-1 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-linear-to-r from-violet-400 via-violet-600 to-violet-400 animate-[loading_1.5s_ease-in-out_infinite] bg-size-[200%_100%]"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
