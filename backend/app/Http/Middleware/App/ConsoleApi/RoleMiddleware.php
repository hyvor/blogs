<?php declare(strict_types=1);

namespace App\Http\Middleware\App\ConsoleApi;

use App\Data\Enums\UserRoleEnum;
use App\Exceptions\TrustedException;
use Closure;
use Illuminate\Http\Request;

use function collect;

class RoleMiddleware
{

    public function handle(Request $request, Closure $next, string $checkRoles) : mixed
    {
        $checkRoles =
            collect(explode('|', $checkRoles))
                ->map(fn ($role) => UserRoleEnum::from($role))
                ->toArray();

        if (!app()->bound(ConsoleApiAccessingUser::class)) {
            throw new TrustedException("ConsoleApiAccessingUser not bound to container");
        }

        $role = app(ConsoleApiAccessingUser::class)->user->role;

        if (!in_array($role, $checkRoles, true)) {
            throw new TrustedException("Your user role ({$role->value}) does not have access to this route");
        }

        return $next($request);
    }
}
