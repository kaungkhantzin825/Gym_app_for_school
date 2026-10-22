<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'gender',
        'age',
        'height',
        'current_weight',
        'goal',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'height' => 'decimal:2',
            'current_weight' => 'decimal:2',
        ];
    }
}
