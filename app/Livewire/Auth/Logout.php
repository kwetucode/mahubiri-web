<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Logout extends Component
{
    public function mount()
    {
        $this->logout();
    }

    public function logout()
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('message', 'Vous avez été déconnecté avec succès.');
    }

    public function render()
    {
        return view('livewire.auth.logout');
    }
}
