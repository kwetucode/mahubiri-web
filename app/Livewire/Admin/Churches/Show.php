<?php

namespace App\Livewire\Admin\Churches;

use App\Models\Church;
use Livewire\Component;

class Show extends Component
{
    public Church $church;
    
    public function mount(Church $church)
    {
        $this->church = $church;
    }

    public function render()
    {
        // Charger les statistiques de l'église
        $stats = [
            'total_sermons' => $this->church->sermons()->count(),
            'total_plays' => $this->church->sermons()->withCount('views')->get()->sum('views_count'),
            'total_favorites' => $this->church->sermons()->withCount('favoritedBy')->get()->sum('favorited_by_count'),
            'active_sermons' => $this->church->sermons()->where('is_published', true)->count(),
        ];

        // Charger les sermons récents de l'église
        $sermons = $this->church->sermons()
            ->with(['category'])
            ->withCount('views')
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.admin.churches.show', [
            'stats' => $stats,
            'sermons' => $sermons,
        ])->layout('components.layouts.app', ['title' => $this->church->name]);
    }
}
