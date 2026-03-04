<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if the email exists
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => __('Aucun compte trouvé avec cette adresse email.'),
            ]);
        }

        // Check if the password is correct
        if (!Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('Le mot de passe est incorrect.'),
            ]);
        }

        // Attempt login
        Auth::login($user, $request->boolean('remember'));

        // Verify user is admin or church admin
        $user = Auth::user();
        if (!in_array($user->role_id, [RoleType::ADMIN, RoleType::CHURCH_ADMIN])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('Accès refusé. Seuls les administrateurs peuvent se connecter.'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
