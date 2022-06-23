<?php

namespace App\Http\Middleware\App\ConsoleAPI;

use App\Models\User;

class ConsoleApiAccessingUser
{
    public function __construct(public User $user) {}
}