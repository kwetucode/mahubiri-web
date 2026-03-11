<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCodeVerification;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Send the reset code to the user's email.
     */
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => __('Aucun compte trouvé avec cette adresse email.')]);
        }

        $codeVerification = UserCodeVerification::createForUser($user, 'password_reset', 15);
        $user->notify(new CustomResetPasswordNotification($codeVerification->code));

        return redirect()->route('admin.password.reset', ['email' => $request->email])
            ->with('status', 'code-sent');
    }

    /**
     * Show the reset password form (code + new password).
     */
    public function showResetPassword(Request $request)
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->query('email', ''),
            'status' => session('status'),
        ]);
    }

    /**
     * Reset the password using the verification code.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $verification = UserCodeVerification::verifyCode(
            $request->email,
            $request->code,
            'password_reset'
        );

        if (!$verification) {
            return back()->withErrors(['code' => __('Code invalide ou expiré.')]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => __('Utilisateur non trouvé.')]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->setRememberToken(Str::random(60));

        $user->save();

        $verification->markAsUsedAndDelete();

        return redirect()->route('admin.login')
            ->with('status', 'password-reset');
    }

    /**
     * Resend the verification code.
     */
    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => __('Aucun compte trouvé avec cette adresse email.')]);
        }

        $codeVerification = UserCodeVerification::createForUser($user, 'password_reset', 15);
        $user->notify(new CustomResetPasswordNotification($codeVerification->code));

        return back()->with('status', 'code-resent');
    }
}
