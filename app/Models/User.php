<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'phone', 'profile_photo', 'password', 'role', 'fcm_token', 'device_type'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $appends = [
        'profile_photo_url',
    ];

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return static::profilePhotoUrl($this->profile_photo);
    }

    /**
     * Absolute URL for a stored photo path, or null when there is no photo.
     *
     * Two things matter here:
     *
     * 1. The URL points at `/api/v1/media/...`, which PHP serves directly.
     *    `Storage::disk('public')->url()` produces `/storage/...`, and that
     *    path only works while the `public/storage` symlink exists and the
     *    web server is allowed to follow it. On the live host it answers 403,
     *    so uploads looked successful but the image never rendered.
     * 2. It is built from the current request rather than `APP_URL`. A stale
     *    `APP_URL` - for example one still carrying `:8000` from local
     *    development - would otherwise be baked into every photo URL and be
     *    unreachable from the app.
     */
    public static function profilePhotoUrl(?string $profilePhoto): ?string
    {
        if ($profilePhoto === null || $profilePhoto === '') {
            return null;
        }

        if (str_starts_with($profilePhoto, 'http://') || str_starts_with($profilePhoto, 'https://')) {
            return $profilePhoto;
        }

        return url('/api/v1/media/profile-photos/'.rawurlencode(basename($profilePhoto)));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Who may open the admin area.
     *
     * Three ways in, in order of precedence:
     *  1. the account is an admin;
     *  2. nobody is an admin yet — bootstrap mode, so a fresh install cannot
     *     lock itself out. Promote an account with `php artisan admin:promote`
     *     and this branch stops applying;
     *  3. the email is listed in ADMIN_EMAILS (recovery hatch).
     */
    public function canAccessAdmin(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! static::query()->where('role', 'admin')->exists()) {
            return true;
        }

        return in_array(
            strtolower((string) $this->email),
            array_map('strtolower', config('gym.admin_emails', [])),
            true
        );
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    public function workouts(): HasMany
    {
        return $this->hasMany(Workout::class);
    }

    public function cameraSessions(): HasMany
    {
        return $this->hasMany(CameraSession::class);
    }

    public function weightLogs(): HasMany
    {
        return $this->hasMany(WeightLog::class);
    }

    public function photoGalleries(): HasMany
    {
        return $this->hasMany(PhotoGallery::class);
    }

    public function supportMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }
}
