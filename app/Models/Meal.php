<?php

namespace App\Models;

use Database\Factories\MealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meal extends Model
{
    /** @use HasFactory<MealFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'photo_path',
        'notes',
        'total_calories',
        'total_protein',
        'total_carbs',
        'total_fat',
        'meal_time',
    ];

    /**
     * `photo_url` is computed, never stored: the raw `photo_path` means nothing
     * to a client, and building the URL here keeps it on the API origin.
     */
    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return [
            'total_calories' => 'decimal:2',
            'total_protein' => 'decimal:2',
            'total_carbs' => 'decimal:2',
            'total_fat' => 'decimal:2',
            'meal_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function foodLogs(): HasMany
    {
        return $this->hasMany(FoodLog::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return self::photoUrl($this->photo_path);
    }

    /**
     * Turn a stored path into a URL the app can actually load.
     *
     * `/storage/...` is unreachable on the production host (the symlink is not
     * followed), so every upload is served by `GET /api/v1/media/...` instead -
     * the same route `User::profilePhotoUrl()` uses.
     */
    public static function photoUrl(?string $photoPath): ?string
    {
        if ($photoPath === null || $photoPath === '') {
            return null;
        }

        if (str_starts_with($photoPath, 'http://') || str_starts_with($photoPath, 'https://')) {
            return $photoPath;
        }

        // `meal-scans/...` comes from the AI analyser, `meals/...` from an
        // upload. Anything else is treated as a plain file in `meals`.
        $directory = str_contains($photoPath, '/') ? dirname($photoPath) : 'meals';

        if (! in_array($directory, ['meals', 'meal-scans'], true)) {
            $directory = 'meals';
        }

        return url('/api/v1/media/'.$directory.'/'.rawurlencode(basename($photoPath)));
    }
}
