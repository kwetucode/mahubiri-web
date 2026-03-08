<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        return Inertia::render('Admin/Profile/Edit');
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
}
