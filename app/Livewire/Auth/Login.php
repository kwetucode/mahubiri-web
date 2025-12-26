<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Enums\RoleType;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|string',
    ];

    public function mount()
    {
        // Redirect if already authenticated
        if (Auth::check() && Auth::user()->role_id === RoleType::ADMIN) {
            return redirect()->route('dashboard');
        }
    }

    public function login()
    {
        $this->validate();

        // Rate limiting
        $key = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        // Attempt authentication
        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials, $this->remember)) {
            RateLimiter::clear($key);
            
            $user = Auth::user();

            // Vérifier le rôle admin
            if ($user->role_id !== RoleType::ADMIN) {
                Auth::logout();
                
                session()->flash('error', 'Accès refusé. Seuls les administrateurs peuvent se connecter.');
                return;
            }

            session()->regenerate();
            
            return redirect()->intended(route('dashboard'));
        }

        // Increment rate limiter
        RateLimiter::hit($key, 60);

        throw ValidationException::withMessages([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ]);
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}
