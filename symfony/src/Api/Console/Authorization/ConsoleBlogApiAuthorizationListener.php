<?php

namespace App\Api\Console\Authorization;

use App\Entity\Blog;
use App\Entity\User;
use App\Service\Blog\BlogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER, priority: 100)]
class ConsoleBlogApiAuthorizationListener
{
    const string RESOLVED_BLOG_KEY = 'console_api_resolved_blog';
    const string RESOLVED_BLOG_USER_KEY = 'console_api_resolved_blog_user';

    public function __construct(
        private BlogService $blogService,
        private ConsoleApiAuthorizationListener $authListener,
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
    ) {}

    public function __invoke(ControllerEvent $event): void
    {
        // @codeCoverageIgnoreStart
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/console/v0/blog/')) {
            return;
        }
        if (!$event->isMainRequest()) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $request = $event->getRequest();
        $subdomain = $request->attributes->get('subdomain');
        if (!$subdomain) {
            throw new NotFoundHttpException('Blog not found');
        }

        $blog = $this->blogService->getBlogBySubdomain($subdomain);
        if ($blog === null) {
            throw new NotFoundHttpException('Blog not found');
        }

        $authUser = $this->authListener->getUser();

        $blogUser = $this->em->getRepository(User::class)->findOneBy([
            'blog_id' => $blog->getId(),
            'hyvor_user_id' => $authUser->id,
            'status' => 'active',
        ]);

        if ($blogUser === null) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $request->attributes->set(self::RESOLVED_BLOG_KEY, $blog);
        $request->attributes->set(self::RESOLVED_BLOG_USER_KEY, $blogUser);
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
