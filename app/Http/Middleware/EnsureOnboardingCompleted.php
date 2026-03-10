<?php

namespace App\Http\Middleware;

use App\Enums\RoleType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Redirect users who haven't completed onboarding to the setup page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admin doesn't need onboarding
        if ($user && $user->role_id === RoleType::ADMIN) {
            return $next($request);
        }

        if ($user && is_null($user->onboarding_completed_at)) {
            return redirect()->route('admin.onboarding.setup');
        }

        return $next($request);
    }
}
