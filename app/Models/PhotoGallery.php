<?php

namespace App\Models;

use Database\Factories\PhotoGalleryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoGallery extends Model
{
    /** @use HasFactory<PhotoGalleryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'photo_url',
        'caption',
        'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
