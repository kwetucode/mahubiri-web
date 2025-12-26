<?php

namespace App\Livewire\Admin\PreacherProfiles;

use App\Models\PreacherProfile;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterMinistryType = '';

    protected $listeners = ['refreshPreacherProfiles' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterMinistryType()
    {
        $this->resetPage();
    }

    public function toggleActive($preacherId)
    {
        $preacher = PreacherProfile::findOrFail($preacherId);
        $preacher->is_active = !$preacher->is_active;
        $preacher->save();

        $status = $preacher->is_active ? 'activé' : 'désactivé';
        session()->flash('success', "Le profil de prédicateur a été {$status} avec succès.");
    }
    public function mount() {}
    public function render()
    {
        $preachers = PreacherProfile::query()
            ->with(['user.role'])
            ->withCount('sermons')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('ministry_name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filterMinistryType, function ($query) {
                $query->where('ministry_type', $this->filterMinistryType);
            })
            ->latest()
            ->paginate(10);

        $totalPreachers = PreacherProfile::count();
        $activePreachers = PreacherProfile::where('is_active', true)->count();
        $inactivePreachers = PreacherProfile::where('is_active', false)->count();

        $ministryTypes = [
            'pasteur' => 'Pasteur',
            'apotre' => 'Apôtre',
            'evangeliste' => 'Évangéliste',
            'prophete' => 'Prophète',
            'enseignant' => 'Enseignant',
            'docteur' => 'Docteur',
        ];

        return view('livewire.admin.preacher-profiles.index', [
            'preachers' => $preachers,
            'totalPreachers' => $totalPreachers,
            'activePreachers' => $activePreachers,
            'inactivePreachers' => $inactivePreachers,
            'ministryTypes' => $ministryTypes,
        ]);
    }
}
