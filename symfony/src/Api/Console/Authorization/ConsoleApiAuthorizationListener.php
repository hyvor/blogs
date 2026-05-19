<?php

namespace App\Api\Console\Authorization;

use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 200)]
class ConsoleApiAuthorizationListener
{
    const string RESOLVED_USER_KEY = 'console_api_resolved_user';
    const string RESOLVED_ORGANIZATION_KEY = 'console_api_resolved_organization';

    public function __construct(private AuthInterface $auth, private RequestStack $requestStack) {}

    public function __invoke(ControllerEvent $event): void
    {
        // @codeCoverageIgnoreStart
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/console/v0')) {
            return;
        }
        if (!$event->isMainRequest()) {
            return;
        }
        // @codeCoverageIgnoreEnd
        $this->handleSession($event);
    }

    private function handleSession(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $isOrgLevel = count($event->getAttributes(OrganizationLevelEndpoint::class)) > 0;
        $orgOptional = count($event->getAttributes(OrganizationOptional::class)) > 0;

        $me = $this->auth->me($request);
        if ($me === null) {
            throw new DataCarryingHttpException(401, [
                'login_url' => $this->auth->authUrl('login'),
                'signup_url' => $this->auth->authUrl('signup'),
            ], 'Unauthorized');
        }

        $request->attributes->set(self::RESOLVED_USER_KEY, $me->getUser());
        $request->attributes->set(self::RESOLVED_ORGANIZATION_KEY, $me->getOrganization());

        if ($orgOptional) {
            assert($isOrgLevel === true);
            return;
        }

        if ($me->getOrganization() === null) {
            throw new AccessDeniedHttpException('Organization is required');
        }

        $orgFromReq = (int)$request->headers->get('X-Organization-ID');
        if ($orgFromReq !== $me->getOrganization()->id) {
            throw new AccessDeniedHttpException('org_mismatch');
        }
    }

    public function getUser(): AuthUser
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request !== null);
        $user = $request->attributes->get(self::RESOLVED_USER_KEY);
        assert($user instanceof AuthUser);
        return $user;
    }

    public function hasOrganization(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request !== null);
        return $request->attributes->get(self::RESOLVED_ORGANIZATION_KEY) instanceof AuthUserOrganization;
    }

    public function getOrganization(): AuthUserOrganization
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request !== null);
        $org = $request->attributes->get(self::RESOLVED_ORGANIZATION_KEY);
        assert($org instanceof AuthUserOrganization);
        return $org;
    }
}
