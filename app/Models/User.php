<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'facebook_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'welcome_email_sent_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the church that owns the user.
     */
    public function church(): HasOne
    {
        return $this->hasOne(Church::class, 'created_by')->where('is_active', true);
    }

    /**
     * Get the preacher profile that owns the user.
     */
    public function preacherProfile(): HasOne
    {
        return $this->hasOne(PreacherProfile::class)->where('is_active', true);
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Override the QR code URL to use the app's base URL as the issuer.
     */
    public function twoFactorQrCodeUrl()
    {
        $issuer = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.name');

        return app(TwoFactorAuthenticationProvider::class)->qrCodeUrl(
            $issuer,
            $this->{Fortify::username()},
            Fortify::currentEncrypter()->decrypt($this->two_factor_secret)
        );
    }

    /**
     * Check if user has a specific role
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Check if welcome email has been sent
     */
    public function hasWelcomeEmailBeenSent(): bool
    {
        return !is_null($this->welcome_email_sent_at);
    }

    /**
     * Mark welcome email as sent
     */
    public function markWelcomeEmailAsSent(): void
    {
        $this->update(['welcome_email_sent_at' => now()]);
    }

    /**
     * Get user's favorite sermons
     */
    public function favoriteSermons()
    {
        return $this->hasMany(SermonFavorite::class);
    }

    /**
     * Get user's sermon views
     */
    public function sermonViews()
    {
        return $this->hasMany(SermonView::class);
    }

    /**
     * Get user's FCM tokens for push notifications
     */
    public function fcmTokens()
    {
        return $this->hasMany(UserFcmToken::class);
    }

    /**
     * Get user's notification settings
     */
    public function notificationSettings()
    {
        return $this->hasOne(UserNotificationSettings::class);
    }
}
