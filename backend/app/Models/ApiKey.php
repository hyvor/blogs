<?php

namespace App\Models;

use App\Data\Enums\ApiKeysTypeEnum;
use App\Data\Enums\UserRoleEnum;
use Database\Factories\ApiKeyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{

    /**
     * @use HasFactory<ApiKeyFactory>
     */
    use HasFactory;

    protected $casts = [
        'type' => ApiKeysTypeEnum::class,
        'role' => UserRoleEnum::class,
    ];
}
