<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * This table is used for saving counted (or summed) data of any model
 *
 * It uses polymorphic one-to-many relationships
 * (https://laravel.com/docs/8.x/eloquent-relationships#one-to-many-polymorphic-relations)
 */

class Count extends Model
{
    use HasFactory;

    protected $casts = [
        'value' => 'integer',
    ];

    public function countable()
    {
        $this->morphTo();
    }
}
