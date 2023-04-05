<?php declare(strict_types=1);

namespace App\Http\Middleware\App\ConsoleApi;

use App\Models\User;

class ConsoleApiAccessingUser
{
    public function __construct(public User $user)
    {
    }
}
