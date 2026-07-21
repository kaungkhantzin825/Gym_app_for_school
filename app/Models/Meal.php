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
}
