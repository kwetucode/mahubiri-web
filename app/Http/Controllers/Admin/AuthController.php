<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\RoleType;
use App\Models\Church;
use App\Models\Sermon;
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
        return Inertia::render('Auth/Login', [
            'stats' => [
                'sermons' => Sermon::count(),
                'churches' => Church::count(),
                'users' => User::count(),
            ],
        ]);
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

        // Verify user has an admin-level role
        $user = Auth::user();
        if (!in_array($user->role_id, [RoleType::ADMIN, RoleType::CHURCH_ADMIN, RoleType::INDEPENDENT_PREACHER])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('Accès refusé. Seuls les administrateurs peuvent se connecter.'),
            ]);
        }

        $request->session()->regenerate();

        // Redirect to email verification if not verified
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('admin.verification.notice');
        }

        // Redirect to onboarding if not completed (not for super admin)
        // But first check if user already has a church/preacher profile (e.g. onboarding_completed_at was not set properly)
        if ($user->role_id !== \App\Enums\RoleType::ADMIN && is_null($user->onboarding_completed_at)) {
            $hasEntity = match ($user->role_id) {
                \App\Enums\RoleType::CHURCH_ADMIN => $user->church()->exists(),
                \App\Enums\RoleType::INDEPENDENT_PREACHER => $user->preacherProfile()->exists(),
                default => false,
            };

            if ($hasEntity) {
                $user->update(['onboarding_completed_at' => now()]);
            } else {
                return redirect()->route('admin.onboarding.setup');
            }
        }

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
