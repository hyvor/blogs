<?php

namespace App\Http\Middleware\App\ConsoleApi;

use App\Data\Enums\UserRoleEnum;
use App\Models\User;

class ConsoleApiAccessingUser
{
    public function __construct(public User $user, public UserRoleEnum $role) {}
}