<?php

namespace App\Livewire\Admin\Roles;

use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $roleId = null;
    public $name = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name',
    ];
    public function mount() {}
    public function render()
    {
        $roles = Role::query()
            ->withCount('users')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $role = Role::findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->roleId) {
            $this->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $this->roleId,
            ]);
            
            $role = Role::findOrFail($this->roleId);
            $role->update(['name' => $this->name]);
            
            session()->flash('message', 'Rôle mis à jour avec succès.');
        } else {
            $this->validate();
            
            Role::create(['name' => $this->name]);
            
            session()->flash('message', 'Rôle créé avec succès.');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);
        
        if ($role->users()->count() > 0) {
            session()->flash('error', 'Impossible de supprimer un rôle assigné à des utilisateurs.');
            return;
        }
        
        $role->delete();
        session()->flash('message', 'Rôle supprimé avec succès.');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetForm()
    {
        $this->roleId = null;
        $this->name = '';
        $this->resetValidation();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
