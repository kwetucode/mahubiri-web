<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role?->name,
                    'avatar_url' => $request->user()->avatar_url,
                ] : null,
                'entity' => fn () => $this->resolveEntity($request),
            ],
            'notifications' => fn () => $request->user() ? [
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
                'donation_uuid' => fn () => $request->session()->get('donation_uuid'),
                'upgrade_uuid' => fn () => $request->session()->get('upgrade_uuid'),
                'twoFactorQrCode' => fn () => $request->session()->get('twoFactorQrCode'),
                'twoFactorRecoveryCodes' => fn () => $request->session()->get('twoFactorRecoveryCodes'),
            ],
        ]);
    }

    /**
     * Resolve the church or preacher entity for the authenticated user.
     */
    private function resolveEntity(Request $request): ?array
    {
        $user = $request->user();
        if (!$user) return null;

        $roleName = $user->role?->name;

        if ($roleName === 'church_admin') {
            $church = $user->church;
            if ($church) {
                return [
                    'name' => $church->abbreviation ?: $church->name,
                    'full_name' => $church->name,
                    'logo_url' => $this->toAbsoluteUrl($church->logo_url),
                    'type' => 'church',
                ];
            }
        }

        if ($roleName === 'independent_preacher') {
            $profile = $user->preacherProfile;
            if ($profile) {
                return [
                    'name' => $profile->ministry_name ?: $user->name,
                    'full_name' => $profile->ministry_name,
                    'logo_url' => $this->toAbsoluteUrl($profile->avatar_url),
                    'type' => 'preacher',
                ];
            }
        }

        return null;
    }

    private function toAbsoluteUrl(?string $url): ?string
    {
        if (!$url) return null;
        if (str_starts_with($url, 'http')) return $url;
        return url($url);
    }
}
