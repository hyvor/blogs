<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory; 

      /**
     * Eager load with these relations
     * because these are always wanted
     */
    protected $with = [
        'variants',
    ];

    public function variants()
    {
        return $this->hasMany(TagVariant::class);
    }

    public function counts() 
    {
        return $this->morphMany(Count::class, 'countable');
    }
    
}
