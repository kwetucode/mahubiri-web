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
            // Check if user already has an entity (church or preacher profile)
            // If so, fix the missing onboarding_completed_at and let them through
            $hasEntity = match ($user->role_id) {
                RoleType::CHURCH_ADMIN => $user->church()->exists(),
                RoleType::INDEPENDENT_PREACHER => $user->preacherProfile()->exists(),
                default => false,
            };

            if ($hasEntity) {
                $user->update(['onboarding_completed_at' => now()]);
                return $next($request);
            }

            return redirect()->route('admin.onboarding.setup');
        }

        return $next($request);
    }
}
