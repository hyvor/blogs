<?php

namespace App\Models;

use App\Data\Enums\ApiTypeEnum;
use App\Data\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => ApiTypeEnum::class,
        'role' => UserRoleEnum::class
    ];
}
