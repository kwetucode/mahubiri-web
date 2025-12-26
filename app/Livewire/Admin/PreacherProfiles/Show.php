<?php

namespace App\Livewire\Admin\PreacherProfiles;

use App\Models\PreacherProfile;
use Livewire\Component;

class Show extends Component
{
    public PreacherProfile $preacherProfile;

    public function mount(PreacherProfile $preacherProfile)
    {
        $this->preacherProfile = $preacherProfile;
    }

    public function render()
    {
        // Charger les statistiques du prédicateur
        $stats = [
            'total_sermons' => $this->preacherProfile->sermons()->count(),
            'total_plays' => $this->preacherProfile->sermons()->withCount('views')->get()->sum('views_count'),
            'total_favorites' => $this->preacherProfile->sermons()->withCount('favoritedBy')->get()->sum('favorited_by_count'),
            'active_sermons' => $this->preacherProfile->sermons()->where('is_published', true)->count(),
        ];

        // Charger les sermons récents du prédicateur
        $sermons = $this->preacherProfile->sermons()
            ->with(['category'])
            ->withCount('views')
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.admin.preacher-profiles.show', [
            'stats' => $stats,
            'sermons' => $sermons,
        ])->layout('components.layouts.app', ['title' => $this->preacherProfile->ministry_name]);
    }
}
