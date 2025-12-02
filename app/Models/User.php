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
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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
            'welcome_email_sent_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the church that owns the user.
     */
    public function church(): HasOne
    {
        return $this->hasOne(Church::class, 'created_by');
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
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
