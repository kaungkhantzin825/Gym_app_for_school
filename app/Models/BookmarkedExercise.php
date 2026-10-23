<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookmarkedExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'app_id',
        'tutorial_video_id',
        'exercise_title',
        'muscle_group',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tutorialVideo(): BelongsTo
    {
        return $this->belongsTo(TutorialVideo::class);
    }
}
