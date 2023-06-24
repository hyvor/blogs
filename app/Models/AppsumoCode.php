<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppsumoCode extends Model
{
    use HasFactory;

    protected $casts = [
        'redeemed_at' => 'datetime'
    ];
}
