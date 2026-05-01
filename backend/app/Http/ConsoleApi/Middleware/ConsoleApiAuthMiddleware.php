<?php

namespace App\Http\ConsoleApi\Middleware;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConsoleApiAuthMiddleware
{

    private const USER_KEY = 'console_api_user';
    private const ORGANIZATION_KEY = 'console_api_organization';

    public function __construct(
        private AuthInterface $auth
    ) {}

    public function handle(Request $request, \Closure $next): mixed
    {
        $me = $this->auth->me($request);

        if (!$me) {
            throw new DataCarryingHttpException(
                401,
                [
                    'login_url' => $this->auth->authUrl('login'),
                    'signup_url' => $this->auth->authUrl('signup'),
                ],
                'Unauthorized'
            );
        }

        $request->attributes->set(self::USER_KEY, $me->getUser());
        $request->attributes->set(self::ORGANIZATION_KEY, $me->getOrganization());

        return $next($request);
    }

    public function getUser(Request $request): AuthUser
    {
        /** @var AuthUser $user */
        $user = $request->attributes->get(self::USER_KEY);
        return $user;
    }

    public function getOrganization(Request $request): ?AuthUserOrganization
    {
        /** @var null|AuthUserOrganization $organization */
        $organization = $request->attributes->get(self::ORGANIZATION_KEY);
        return $organization;
    }
}
