<?php

namespace App\Models;

use Database\Factories\FoodLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodLog extends Model
{
    /** @use HasFactory<FoodLogFactory> */
    use HasFactory;

    protected $fillable = [
        'meal_id',
        'food_name',
        'fatsecret_food_id',
        'brand_name',
        'serving_size',
        'serving_unit',
        'calories',
        'protein',
        'carbs',
        'fat',
        'fiber',
        'sugar',
        'sodium',
    ];

    protected function casts(): array
    {
        return [
            'serving_size' => 'decimal:2',
            'calories' => 'decimal:2',
            'protein' => 'decimal:2',
            'carbs' => 'decimal:2',
            'fat' => 'decimal:2',
            'fiber' => 'decimal:2',
            'sugar' => 'decimal:2',
            'sodium' => 'decimal:2',
        ];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
}
