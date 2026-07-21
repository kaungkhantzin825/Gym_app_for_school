<?php

namespace App\Models;

use Database\Factories\WeightLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeightLog extends Model
{
    /** @use HasFactory<WeightLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'weight',
        'notes',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'logged_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
