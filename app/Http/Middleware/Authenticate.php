<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Si la requête attend du JSON (API), ne pas rediriger
        if ($request->expectsJson()) {
            return null;
        }

        // Pour toutes les requêtes web, rediriger vers admin.login
        return route('admin.login');
    }
}
