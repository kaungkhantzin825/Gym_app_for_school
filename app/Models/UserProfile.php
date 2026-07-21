<?php

namespace App\Models;

use Database\Factories\UserProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    /** @use HasFactory<UserProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'age',
        'gender',
        'height',
        'current_weight',
        'target_weight',
        'goal',
        'activity_level',
        'daily_calories_target',
        'daily_protein_target',
        'daily_carbs_target',
        'daily_fat_target',
    ];

    protected function casts(): array
    {
        return [
            'height' => 'decimal:2',
            'current_weight' => 'decimal:2',
            'target_weight' => 'decimal:2',
            'daily_calories_target' => 'decimal:2',
            'daily_protein_target' => 'decimal:2',
            'daily_carbs_target' => 'decimal:2',
            'daily_fat_target' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
