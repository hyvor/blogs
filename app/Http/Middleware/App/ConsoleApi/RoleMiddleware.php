<?php declare(strict_types=1);

namespace App\Http\Middleware\App\ConsoleApi;

use App\Data\Enums\UserRoleEnum;
use App\Exceptions\TrustedException;
use Closure;
use Illuminate\Http\Request;

use function collect;

class RoleMiddleware
{

    private UserRoleEnum $userRole;

    public function __construct(ConsoleApiAccessingUser $consoleApiAccessingUser)
    {
        $this->userRole = $consoleApiAccessingUser->user->role;
    }

    public function handle(Request $request, Closure $next, string $checkRoles) : mixed
    {
        $checkRoles =
            collect(explode('|', $checkRoles))
                ->map(fn ($role) => UserRoleEnum::from($role))
                ->toArray();

        if (!in_array($this->userRole, $checkRoles, true)) {
            throw new TrustedException("Your user role ({$this->userRole->value}) does not have access to this route");
        }

        return $next($request);
    }
}
