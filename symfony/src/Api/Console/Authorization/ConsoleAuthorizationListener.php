<?php

namespace App\Api\Console\Authorization;

use App\Entity\Blog;
use App\Service\Blog\BlogService;
use App\Service\User\UserService;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 200)]
class ConsoleAuthorizationListener
{
    public const string RESOLVED_USER_ATTRIBUTE_KEY = 'console_api_resolved_user';
    public const string RESOLVED_ORGANIZATION_ATTRIBUTE_KEY = 'console_api_resolved_organization';
    public const string RESOLVED_BLOG_ATTRIBUTE_KEY = 'console_api_resolved_blog';

    public function __construct(
        private AuthInterface $auth,
        private BlogService $blogService,
        private UserService $userService,
    ) {}

    public function __invoke(ControllerEvent $event): void
    {
        // @codeCoverageIgnoreStart
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/console/v1')) {
            return;
        }

        if ($event->isMainRequest() === false) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $this->handleSession($event);
    }

    public function handleSession(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        $me = $this->auth->me($request);
        if ($me === null) {
            throw new DataCarryingHttpException(
                403,
                [
                    'login_url' => $this->auth->authUrl('login'),
                ],
                'Invalid session.'
            );
        }

        $user = $me->getUser();
        $organization = $me->getOrganization();

        if ($organization === null) {
            throw new AccessDeniedHttpException('Current organization is missing.');
        }

        $organizationFromFrontend = (int)$request->headers->get('x-organization-id');

        if ($organizationFromFrontend !== $organization->id) {
            throw new AccessDeniedHttpException('org_mismatch');
        }

        $request->attributes->set(self::RESOLVED_USER_ATTRIBUTE_KEY, $user);
        $request->attributes->set(self::RESOLVED_ORGANIZATION_ATTRIBUTE_KEY, $organization);

        $subdomainValue = $request->attributes->get('subdomain');
        $subdomain = is_scalar($subdomainValue) ? strval($subdomainValue) : null;

        if (!$subdomain) {
            throw new BadRequestException('Subdomain is required.');
        }

        $blog = $this->blogService->getBlogBySubdomain($subdomain);

        if (!$blog) {
            throw new BadRequestException('Blog not found');
        }

        if ($blog->getOrganizationId() !== $organization->id) {
            throw new AccessDeniedHttpException('does_not_belong_the_resource');
        }

        if (!$this->userService->hasAccessToBlog($blog, $user->id)) {
            throw new AccessDeniedHttpException('You do not have access to this blog.');
        }

        $request->attributes->set(self::RESOLVED_BLOG_ATTRIBUTE_KEY, $blog);
    }


    /**
     * Helper methods
     */
    public static function hasUser(Request $request): bool
    {
        return $request->attributes->has(self::RESOLVED_USER_ATTRIBUTE_KEY);
    }

    public static function getUser(Request $request)
    {
        $user = $request->attributes->get(self::RESOLVED_USER_ATTRIBUTE_KEY);
        assert($user !== null);
        return $user;
    }

    public static function hasOrganization(Request $request): bool
    {
        return $request->attributes->has(self::RESOLVED_ORGANIZATION_ATTRIBUTE_KEY);
    }

    public static function getOrganization(Request $request)
    {
        $organization = $request->attributes->get(self::RESOLVED_ORGANIZATION_ATTRIBUTE_KEY);
        assert($organization !== null);
        return $organization;
    }

    public static function hasBlog(Request $request): bool
    {
        return $request->attributes->has(self::RESOLVED_BLOG_ATTRIBUTE_KEY);
    }

    public static function getBlog(Request $request): Blog
    {
        $blog = $request->attributes->get(self::RESOLVED_BLOG_ATTRIBUTE_KEY);
        assert($blog instanceof Blog);
        return $blog;
    }
}