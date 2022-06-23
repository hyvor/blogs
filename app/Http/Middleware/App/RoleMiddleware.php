<?php

namespace App\Http\Middleware\App;

use App\Data\Enums\UserRoleEnum;
use App\Exceptions\TrustedException;
use App\Http\Middleware\App\ConsoleAPI\ConsoleApiAccessingUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function __construct(ConsoleApiAccessingUser $consoleApiAccessingUser)
    {
        $this->userRole = $consoleApiAccessingUser->user->role;
    }

    public function handle(Request $request, Closure $next, $checkRoles)
    {
        $checkRoles =
            collect(explode('|', $checkRoles))
                ->map(fn ($role) => UserRoleEnum::from($role))
                ->toArray();

        if (! in_array($this->userRole, $checkRoles)) {
            throw new TrustedException("Your user role ({$this->userRole->value}) does not have access to this route");
        }

        return $next($request);
    }
}
