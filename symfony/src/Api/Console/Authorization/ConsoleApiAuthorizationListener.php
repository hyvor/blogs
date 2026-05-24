<?php

namespace App\Api\Console\Authorization;

use App\Entity\Blog;
use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Service\ApiKey\ApiKeyService;
use App\Service\Blog\BlogService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Bundle\Api\DataCarryingHttpException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 200)]
class ConsoleApiAuthorizationListener
{
    const string RESOLVED_USER_KEY = 'console_api_resolved_user';
    const string RESOLVED_ORGANIZATION_KEY = 'console_api_resolved_organization';
    const string RESOLVED_BLOG_KEY = 'console_api_resolved_blog';
    const string RESOLVED_BLOG_USER_KEY = 'console_api_resolved_blog_user';

    public function __construct(
        private AuthInterface $auth,
        private BlogService $blogService,
        private ApiKeyService $apiKeyService,
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
    ) {}

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

        $request = $event->getRequest();
        $subdomain = $request->attributes->get('subdomain');

        if (is_string($subdomain) && $subdomain !== '') {
            $this->handleBlogLevel($event, $subdomain);
        } else {
            $this->handleOrgLevel($event);
        }
    }

    private function handleBlogLevel(ControllerEvent $event, string $subdomain): void
    {
        $request = $event->getRequest();

        $blog = $this->blogService->getBlogBySubdomain($subdomain);
        if ($blog === null) {
            throw new NotFoundHttpException('Blog not found');
        }

        $request->attributes->set(self::RESOLVED_BLOG_KEY, $blog);

        if ($request->headers->has('authorization')) {
            $this->handleApiKeyAuth($request, $blog);
        } else {
            $this->handleBlogSessionAuth($request, $blog);
        }
    }

    private function handleApiKeyAuth(Request $request, Blog $blog): void
    {
        $authorizationHeader = $request->headers->get('authorization');
        assert(is_string($authorizationHeader));

        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            throw new AccessDeniedHttpException('Authorization header must start with "Bearer ".');
        }

        $rawKey = trim(substr($authorizationHeader, 7));
        if ($rawKey === '') {
            throw new AccessDeniedHttpException('API key is missing or empty.');
        }

        $apiKey = $this->apiKeyService->getByRawKey($blog, $rawKey);
        if ($apiKey === null) {
            throw new AccessDeniedHttpException('Invalid API key.');
        }

        $owner = $this->em->getRepository(User::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'role' => UserRole::OWNER,
            'status' => 'active',
        ]);

        if ($owner === null) {
            throw new AccessDeniedHttpException('Blog owner not found.');
        }

        $request->attributes->set(self::RESOLVED_BLOG_USER_KEY, $owner);
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
        $request->attributes->set(self::RESOLVED_USER_KEY, $authUser);
        $request->attributes->set(self::RESOLVED_ORGANIZATION_KEY, $me->getOrganization());

        $blogUser = $this->em->getRepository(User::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'hyvor_user_id' => $authUser->id,
            'status' => 'active',
        ]);

        if ($blogUser === null) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $request->attributes->set(self::RESOLVED_BLOG_USER_KEY, $blogUser);
    }

    private function handleOrgLevel(ControllerEvent $event): void
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

    public function getBlog(): Blog
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request !== null);
        $blog = $request->attributes->get(self::RESOLVED_BLOG_KEY);
        assert($blog instanceof Blog);
        return $blog;
    }

    public function getBlogUser(): User
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request !== null);
        $user = $request->attributes->get(self::RESOLVED_BLOG_USER_KEY);
        assert($user instanceof User);
        return $user;
    }
}
