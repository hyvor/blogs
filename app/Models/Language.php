<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function fallback()
    {
        $this->hasOne(Language::class, 'id', 'fallback_language_id');
    }
}
