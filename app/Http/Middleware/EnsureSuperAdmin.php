<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\RoleType;

class EnsureSuperAdmin
{
    /**
     * Restrict access to super admin (role_id = ADMIN) only.
     * Church admins are redirected to the dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || $user->role_id !== RoleType::ADMIN) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès réservé aux administrateurs.'], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
