<?php

namespace App\Models;

use Database\Factories\WorkoutSetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutSet extends Model
{
    /** @use HasFactory<WorkoutSetFactory> */
    use HasFactory;

    protected $fillable = [
        'workout_id',
        'exercise_id',
        'set_number',
        'reps',
        'weight',
        'rpe',
        'rest_time_seconds',
        'is_superset',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'is_superset' => 'boolean',
        ];
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }
}
