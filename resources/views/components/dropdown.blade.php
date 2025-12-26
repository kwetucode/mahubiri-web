@props(['align' => 'right', 'direction' => 'auto'])

<div x-data="{ 
    open: false,
    position: 'bottom',
    top: 0,
    left: 0,
    calculatePosition() {
        // Toujours recalculer la position à chaque fois
        this.$nextTick(() => {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const dropdownHeight = 300;
            
            // Calculer la position
            if ('{{ $direction }}' !== 'auto') {
                this.position = '{{ $direction }}';
            } else {
                this.position = spaceBelow < dropdownHeight && spaceAbove > spaceBelow ? 'top' : 'bottom';
            }
            
            // Position fixe calculée (prend en compte le scroll)
            const scrollY = window.scrollY || window.pageYOffset;
            this.top = this.position === 'top' 
                ? rect.top + scrollY - dropdownHeight - 8
                : rect.bottom + scrollY + 8;
            this.left = '{{ $align }}' === 'left' ? rect.left : rect.right - 224; // 224px = 14rem (w-56)
        });
    },
    toggle() {
        if (!this.open) {
            this.calculatePosition();
        } else {
            this.open = false;
        }
        this.open = !this.open;
    }
}" @click.away="open = false" x-init="$watch('open', value => { if(value) calculatePosition(); })" class="relative inline-block text-left">
    <!-- Trigger Button -->
    <div>
        <button 
            x-ref="trigger"
            @click="toggle()" 
            type="button" 
            class="inline-flex items-center justify-center w-10 h-10 text-gray-700 hover:text-[#6B4EAF] bg-white hover:bg-[#E6E3F5]/50 rounded-lg border border-gray-200 hover:border-[#6B4EAF] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#6B4EAF]/20"
            aria-expanded="true" 
            aria-haspopup="true"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
            </svg>
        </button>
    </div>

    <!-- Dropdown Menu with Fixed Positioning -->
    <div x-show="open" x-cloak>
        <div 
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            :style="`top: ${top}px; left: ${left}px;`"
            class="fixed z-[9999] w-56 rounded-xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none border border-gray-100"
            role="menu" 
            aria-orientation="vertical"
        >
            {{ $slot }}
        </div>
    </div>
</div>
