<?php

namespace App\Models;

use Database\Factories\CameraSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraSession extends Model
{
    /** @use HasFactory<CameraSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'app_id',
        'client_id',
        'exercise_type',
        'exercise_name',
        'started_at',
        'ended_at',
        'duration_seconds',
        'sets_count',
        'total_reps',
        'avg_form_score',
        'sets',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'duration_seconds' => 'integer',
            'sets_count' => 'integer',
            'total_reps' => 'integer',
            'avg_form_score' => 'integer',
            'sets' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
