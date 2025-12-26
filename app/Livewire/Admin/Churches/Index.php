<?php

namespace App\Livewire\Admin\Churches;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Church;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterActive = '';
    public $perPage = 10;

    protected $queryString = ['search', 'filterActive'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterActive()
    {
        $this->resetPage();
    }

    public function toggleActive($churchId)
    {
        $church = Church::findOrFail($churchId);
        $church->is_active = !$church->is_active;
        $church->save();

        session()->flash('message', 'Statut de l\'église mis à jour avec succès.');
    }

    public function deleteChurch($churchId)
    {
        $church = Church::findOrFail($churchId);
        $church->delete();

        session()->flash('message', 'Église supprimée avec succès.');
    }

    public function mount() {}

    public function render()
    {
        $churches = Church::with('createdBy')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('abbreviation', 'like', '%' . $this->search . '%')
                    ->orWhere('visionary_name', 'like', '%' . $this->search . '%')
                    ->orWhere('city', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterActive !== '', function ($query) {
                $query->where('is_active', $this->filterActive === '1');
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.churches.index', [
            'churches' => $churches,
        ]);
    }
}
