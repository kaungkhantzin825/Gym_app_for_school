<?php

namespace App\Models;

use Database\Factories\TutorialVideoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorialVideo extends Model
{
    /** @use HasFactory<TutorialVideoFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'video_url',
        'thumbnail_url',
        'gender_target',
        'muscle_group',
        'focus_areas',
        'equipment',
        'how_to_do',
    ];

    protected function casts(): array
    {
        return [
            'focus_areas' => 'array',
        ];
    }
}
