<?php

namespace App\Api\Console\Authorization;

use App\Entity\Blog;
use App\Service\ApiKey\ApiKeyService;
use App\Service\Blog\BlogService;
use App\Service\User\UserService;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;


class ConsoleApiAuthorizationListener
{
    private const string RESOLVED_USER_KEY = 'console_api_resolved_user';
    private const string RESOLVED_ORGANIZATION_KEY = 'console_api_resolved_organization';
    private const string RESOLVED_BLOG_KEY = 'console_api_resolved_blog';

    public function __construct(
        private AuthInterface $auth,
        private BlogService $blogService,
        private ApiKeyService $apiKeyService,
        private RequestStack $requestStack,
        private UserService $userService,
    ) {}

    #[AsEventListener(event: KernelEvents::CONTROLLER, priority: 200)]
    public function onController(ControllerEvent $event): void
    {
        // @codeCoverageIgnoreStart
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/console/v0')) {
            return;
        }
        if (!$event->isMainRequest()) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $request = $event->getRequest();
        $subdomain = $request->attributes->get('subdomain');

        if (is_string($subdomain) && $subdomain !== '') {
            $this->handleBlogLevel($event, $subdomain);
        } else {
            $this->handleOrgLevel($event);
        }
    }

    #[AsEventListener(event: KernelEvents::RESPONSE, priority: 200)]
    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('Symfony', 'Performing');
    }

    private function handleBlogLevel(ControllerEvent $event, string $subdomain): void
    {
        $request = $event->getRequest();

        $blog = $this->blogService->getBlogBySubdomain($subdomain);
        if ($blog === null) {
            throw new NotFoundHttpException('Blog not found');
        }

        if ($request->headers->has('x-api-key')) {
            $this->handleApiKeyAuth($request, $blog);
        } else {
            $this->handleBlogSessionAuth($request, $blog);
        }
    }

    private function handleApiKeyAuth(Request $request, Blog $blog): void
    {
        $apiKeyHeader = $request->headers->get('x-api-key');
        assert(is_string($apiKeyHeader));

        $apiKey = $this->apiKeyService->getByRawKey($blog, $apiKeyHeader);
        if ($apiKey === null) {
            throw new AccessDeniedHttpException('Invalid API key.');
        }

        // TODO: verify scopes

        $request->attributes->set(self::RESOLVED_BLOG_KEY, $blog);
    }

    private function handleBlogSessionAuth(Request $request, Blog $blog): void
    {
        $me = $this->auth->me($request);
        if ($me === null) {
            throw new DataCarryingHttpException(401, [
                'login_url' => $this->auth->authUrl('login'),
                'signup_url' => $this->auth->authUrl('signup'),
            ], 'Unauthorized');
        }

        $authUser = $me->getUser();
        $authOrganization = $me->getOrganization();

        if ($authOrganization === null) {
            throw new AccessDeniedHttpException('Organization is required');
        }

        if ($blog->getOrganizationId() !== $authOrganization->id) {
            throw new AccessDeniedHttpException('This project does not belong to your current organization.');
        }

        if ((int)$request->headers->get('X-Organization-Id') !== $authOrganization->id) {
            throw new AccessDeniedHttpException('org_mismatch');
        }

        $blogUser = $this->userService->getUserByBlogAndAuthUser($blog, $authUser);

        if ($blogUser === null) {
            throw new AccessDeniedHttpException('You do not have access to this blog');
        }

        // TODO: verify scopes

        $request->attributes->set(self::RESOLVED_BLOG_KEY, $blog);
    }

    private function handleOrgLevel(ControllerEvent $event): void
    {
        $request = $event->getRequest();
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

    // helpers

    /**
     * only use in org-level endpoints
     */
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

    public function getBlog(): Blog
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request !== null);
        $blog = $request->attributes->get(self::RESOLVED_BLOG_KEY);
        assert($blog instanceof Blog);
        return $blog;
    }
}
