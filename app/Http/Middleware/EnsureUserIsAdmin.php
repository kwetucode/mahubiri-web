<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\RoleType;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = auth()->user();

        // Vérifier si l'utilisateur a le rôle admin ou church_admin
        if (!in_array($user->role_id, [RoleType::ADMIN, RoleType::CHURCH_ADMIN])) {
            auth()->logout();
            return redirect()->route('admin.login')
                ->with('error', 'Accès refusé. Vous devez être administrateur.');
        }

        return $next($request);
    }
}
