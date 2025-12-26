<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterRole = '';
    public $showModal = false;
    public $userId = null;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $role_id = '';

    // Mot de passe par défaut pour les nouveaux utilisateurs
    const DEFAULT_PASSWORD = 'password123';

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
        ];

        if ($this->userId) {
            $rules['email'] = 'required|email|unique:users,email,' . $this->userId;
        } else {
            $rules['email'] = 'required|email|unique:users,email';
        }

        return $rules;
    }

    public function mount(){}   

    public function render()
    {
        $users = User::query()
            ->with('role')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterRole, function ($query) {
                $query->where('role_id', $this->filterRole);
            })
            ->orderBy('name')
            ->paginate(10);

        $roles = Role::orderBy('name')->get();

        // Statistiques
        $totalUsers = User::count();
        $usersByRole = Role::withCount('users')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'totalUsers' => $totalUsers,
            'usersByRole' => $usersByRole,
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role_id = $user->role_id;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role_id' => $this->role_id,
        ];

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update($data);
            session()->flash('message', 'Utilisateur mis à jour avec succès.');
        } else {
            // Utilisation du mot de passe par défaut pour les nouveaux utilisateurs
            $data['password'] = Hash::make(self::DEFAULT_PASSWORD);
            User::create($data);
            session()->flash('message', 'Utilisateur créé avec succès. Mot de passe par défaut : ' . self::DEFAULT_PASSWORD);
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);

        // Empêcher la suppression de l'utilisateur connecté
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return;
        }

        $user->delete();
        session()->flash('message', 'Utilisateur supprimé avec succès.');
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    private function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->role_id = '';
        $this->resetValidation();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterRole()
    {
        $this->resetPage();
    }
}
