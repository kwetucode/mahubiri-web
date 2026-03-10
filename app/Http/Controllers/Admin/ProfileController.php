<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = request()->user();

        return Inertia::render('Admin/Profile/Edit', [
            'twoFactor' => [
                'enabled' => $user->hasEnabledTwoFactorAuthentication(),
                'confirmed' => ! is_null($user->two_factor_confirmed_at),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
        ]);

        $request->user()->update($request->only('name', 'email'));

        return back()->with('success', __('Profile updated.'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $request->password,
        ]);

        return back()->with('success', __('Password updated.'));
    }

    public function updateAvatar(Request $request, FileUploadService $uploadService)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar_url) {
            $uploadService->deleteFile($user->avatar_url, 'images');
        }

        $avatarUrl = $uploadService->handleImageUpload($request->file('avatar'), 'avatars');

        $user->update(['avatar_url' => $avatarUrl]);

        return back()->with('success', __('Avatar updated.'));
    }

    public function deleteAvatar(Request $request, FileUploadService $uploadService)
    {
        $user = $request->user();

        if ($user->avatar_url) {
            $uploadService->deleteFile($user->avatar_url, 'images');
            $user->update(['avatar_url' => null]);
        }

        return back()->with('success', __('Avatar removed.'));
    }

    public function enableTwoFactor(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $user = $request->user();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return back()->with('error', __('Two-factor authentication is already enabled.'));
        }

        $enable($user);

        return back()->with([
            'twoFactorQrCode' => $user->twoFactorQrCodeSvg(),
            'twoFactorRecoveryCodes' => $user->recoveryCodes(),
        ]);
    }

    public function confirmTwoFactor(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $request->validate(['code' => ['required', 'string']]);

        try {
            $confirm($request->user(), $request->code);
        } catch (\Illuminate\Validation\ValidationException) {
            return back()->withErrors(['code' => __('The provided two factor authentication code was invalid.')]);
        }

        return back()->with('success', __('Two-factor authentication confirmed.'));
    }

    public function disableTwoFactor(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $disable($request->user());

        return back()->with('success', __('Two-factor authentication disabled.'));
    }

    public function showRecoveryCodes(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        return back()->with([
            'twoFactorRecoveryCodes' => $request->user()->recoveryCodes(),
        ]);
    }

    public function regenerateRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $generate($request->user());

        return back()->with([
            'twoFactorRecoveryCodes' => $request->user()->recoveryCodes(),
            'success' => __('Recovery codes regenerated.'),
        ]);
    }
}
