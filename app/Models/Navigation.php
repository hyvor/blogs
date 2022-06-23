<?php

namespace App\Models;

use App\Data\Enums\NavigationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Navigation extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => NavigationTypeEnum::class,
    ];

    protected $with = [
        'variants',
    ];

    public function variants()
    {
        return $this->hasMany(NavigationVariant::class);
    }

    public function counts()
    {
        return $this->morphMany(Count::class, 'countable');
    }
}
