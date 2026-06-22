<?php

namespace App\Service\Route;

use App\Entity\Blog;
use App\Entity\Route;
use App\Service\Route\Event\RouteChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-type RouteDef array{name: string, match: string, template: string, posts_filter?: string}
 */
class RouteService
{
    use ClockAwareTrait;

    /**
     * @var RouteDef[]
     */
    public const ROUTES = [
        // post
        [
            'name' => 'post',
            'match' => '/{slug}',
            'template' => 'post',
        ],
        // page
        [
            'name' => 'page',
            'match' => '/{slug}',
            'template' => 'page,post',
        ],
        // home page (index)
        [
            'name' => 'index',
            'match' => '/',
            'template' => 'index',
            'posts_filter' => '',
        ],
        // tag
        [
            'name' => 'tag',
            'match' => '/tag/{slug}',
            'template' => 'tag,index',
            'posts_filter' => 'tag.slug={slug}',
        ],
        // author
        [
            'name' => 'author',
            'match' => '/author/{slug}',
            'template' => 'author,index',
            'posts_filter' => 'author.slug={slug}',
        ],
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function getRouteByName(Blog $blog, string $name): ?Route
    {
        return $this->em->getRepository(Route::class)->findOneBy(['blog' => $blog, 'name' => $name]);
    }

    /** @return Route[] */
    public function getRoutes(Blog $blog): array
    {
        return $this->em->getRepository(Route::class)->findBy(
            ['blog' => $blog],
            ['created_at' => 'ASC'],
        );
    }

    public function getRoutesCount(Blog $blog): int
    {
        return $this->em->getRepository(Route::class)->count(['blog' => $blog]);
    }

    public function createRoute(
        Blog $blog,
        string $name,
        string $match,
        string $template,
        ?string $postsFilter,
        ?string $contentType,
    ): Route {
        $now = $this->now();
        $route = new Route();
        $route->setBlog($blog);
        $route->setName($name);
        $route->setMatch($match);
        $route->setTemplate($template);
        $route->setPostsFilter($postsFilter);
        $route->setContentType($contentType);
        $route->setIsEnabled(true);
        $route->setCreatedAt($now);
        $route->setUpdatedAt($now);
        $this->em->persist($route);
        $this->em->flush();
        $this->dispatcher->dispatch(new RouteChangedEvent($route));
        return $route;
    }

    /**
     * @param array{name?: string, match?: string, template?: string, posts_filter?: string|null, content_type?: string|null} $updates
     */
    public function updateRoute(Route $route, array $updates): Route
    {
        if (array_key_exists('name', $updates)) {
            $route->setName($updates['name']);
        }
        if (array_key_exists('match', $updates)) {
            $route->setMatch($updates['match']);
        }
        if (array_key_exists('template', $updates)) {
            $route->setTemplate($updates['template']);
        }
        if (array_key_exists('posts_filter', $updates)) {
            $route->setPostsFilter($updates['posts_filter']);
        }
        if (array_key_exists('content_type', $updates)) {
            $route->setContentType($updates['content_type']);
        }

        $route->setUpdatedAt($this->now());
        $this->em->flush();
        $this->dispatcher->dispatch(new RouteChangedEvent($route));
        return $route;
    }

    public function deleteRoute(Route $route): void
    {
        $this->em->remove($route);
        $this->em->flush();
        $this->dispatcher->dispatch(new RouteChangedEvent($route));
    }
}
