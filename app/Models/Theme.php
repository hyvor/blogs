<?php

namespace App\Models;

use App\Data\Enums\ThemeCreationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => ThemeCreationTypeEnum::class
    ];

    public function versions()
    {
        return $this->hasMany(ThemeVersion::class);
    }

}

