<?php

namespace App\Models;

use App\Data\Enums\UrlDataFetchTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlData extends Model
{
    use HasFactory;

    protected $casts = [
        'fetch_type' => UrlDataFetchTypeEnum::class,
    ];
}
