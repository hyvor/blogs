<?php

namespace App\Models;

use App\Data\Enums\RedirectTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    protected $casts = [
        'type ' => RedirectTypeEnum::class,
    ];
}
