<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\MinistryType;
use App\Enums\RoleType;
use App\Models\Church;
use App\Models\PreacherProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\UserCodeVerification;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\NewChurchCreated;
use App\Services\NotificationService;
use Illuminate\Auth\Events\Verified;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class RegisterController extends Controller
{
    /**
     * Show the registration form (Step 1).
     */
    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle registration (Step 1) — create the account.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'account_type' => ['required', 'in:church_admin,independent_preacher'],
        ]);

        $roleId = $validated['account_type'] === 'church_admin'
            ? RoleType::CHURCH_ADMIN
            : RoleType::INDEPENDENT_PREACHER;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role_id' => $roleId,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // Send email verification code
        $codeVerification = UserCodeVerification::createForUser($user, 'email_verification', 15);
        $user->notify(new CustomVerifyEmail($codeVerification->code));

        return redirect()->route('admin.verification.notice');
    }

    /**
     * Show the email verification page.
     */
    public function showVerifyEmail()
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('admin.onboarding.setup');
        }

        return Inertia::render('Auth/VerifyEmail', [
            'email' => $user->email,
        ]);
    }

    /**
     * Verify the email with the 6-digit code.
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('admin.onboarding.setup');
        }

        $verification = UserCodeVerification::verifyCode(
            $user->email,
            $request->code,
            'email_verification'
        );

        if (!$verification) {
            return back()->withErrors(['code' => __('Code invalide ou expiré.')]);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
        $verification->markAsUsedAndDelete();

        return redirect()->route('admin.onboarding.setup');
    }

    /**
     * Resend the verification code.
     */
    public function resendCode(Request $request)
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('admin.onboarding.setup');
        }

        $codeVerification = UserCodeVerification::createForUser($user, 'email_verification', 15);
        $user->notify(new CustomVerifyEmail($codeVerification->code));

        return back()->with('success', __('Un nouveau code a été envoyé.'));
    }

    /**
     * Show the onboarding setup form (Step 2).
     * Renders the appropriate form based on the user's role.
     */
    public function showSetup()
    {
        $user = Auth::user();

        // Super admin doesn't need onboarding — redirect to dashboard
        if ($user->role_id === RoleType::ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role_id === RoleType::CHURCH_ADMIN) {
            return Inertia::render('Auth/Onboarding/ChurchSetup');
        }

        return Inertia::render('Auth/Onboarding/PreacherSetup', [
            'ministryTypes' => MinistryType::asSelectArray(),
        ]);
    }

    /**
     * Handle church setup (Step 2 for CHURCH_ADMIN).
     */
    public function setupChurch(Request $request)
    {
        $user = Auth::user();

        if ($user->role_id === RoleType::ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abbreviation' => ['nullable', 'string', 'max:50'],
            'visionary_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'country_name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'max:5'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $imageService = app(ImageUploadService::class);
            $logoUrl = $imageService->handleImageUpload($request->file('logo'), 'church_logos');
        }

        $church = Church::create([
            ...collect($validated)->except('logo')->all(),
            'logo_url' => $logoUrl,
            'created_by' => $user->id,
            'is_active' => true,
            'storage_limit' => Church::DEFAULT_STORAGE_LIMIT,
        ]);

        $user->update(['onboarding_completed_at' => now()]);

        // Notify super admins about new church
        $admins = User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewChurchCreated($church));
        }

        // Send FCM push notification to all users
        try {
            app(NotificationService::class)->sendToAllUsers('new_church', [
                'title' => 'Nouvelle église disponible',
                'body' => "{$church->name} vient de rejoindre Mahubiri",
                'data' => [
                    'church_id' => $church->id,
                    'church_name' => $church->name,
                    'type' => 'new_church',
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send FCM for new church', ['church_id' => $church->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', __('Votre église a été configurée avec succès.'));
    }

    /**
     * Handle preacher profile setup (Step 2 for INDEPENDENT_PREACHER).
     */
    public function setupPreacher(Request $request)
    {
        $user = Auth::user();

        if ($user->role_id === RoleType::ADMIN) {
            return redirect()->route('admin.dashboard');
        }

        $validated = $request->validate([
            'ministry_name' => ['required', 'string', 'max:255'],
            'ministry_type' => ['required', 'string', 'in:' . implode(',', MinistryType::getValues())],
            'country_name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'max:5'],
            'city' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            $imageService = app(ImageUploadService::class);
            $avatarUrl = $imageService->handleImageUpload($request->file('avatar'), 'avatars');
        }

        PreacherProfile::create([
            ...collect($validated)->except('avatar')->all(),
            'avatar_url' => $avatarUrl,
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $user->update(['onboarding_completed_at' => now()]);

        return redirect()->route('admin.dashboard')
            ->with('success', __('Votre profil de prédicateur a été configuré avec succès.'));
    }
}
