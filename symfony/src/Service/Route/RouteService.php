<?php

namespace App\Service\Route;

use App\Entity\Blog;
use App\Entity\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class RouteService
{
    use ClockAwareTrait;

    public function __construct(private EntityManagerInterface $em) {}

    /** @return Route[] */
    public function getRoutes(Blog $blog): array
    {
        return $this->em->getRepository(Route::class)->findBy(
            ['blog_id' => $blog->getId()],
            ['created_at' => 'ASC'],
        );
    }

    public function getRoutesCount(Blog $blog): int
    {
        return $this->em->getRepository(Route::class)->count(['blog_id' => $blog->getId()]);
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
        $route->setBlogId($blog->getId());
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
        return $route;
    }

    public function updateRoute(
        Route $route,
        ?string $name,
        ?string $match,
        ?string $template,
        ?string $postsFilter,
        ?string $contentType,
    ): Route {
        if ($name !== null) {
            $route->setName($name);
        }
        if ($match !== null) {
            $route->setMatch($match);
        }
        if ($template !== null) {
            $route->setTemplate($template);
        }
        if ($postsFilter !== null) {
            $route->setPostsFilter($postsFilter);
        }
        if ($contentType !== null) {
            $route->setContentType($contentType);
        }
        $route->setUpdatedAt($this->now());
        $this->em->flush();
        return $route;
    }

    public function deleteRoute(Route $route): void
    {
        $this->em->remove($route);
        $this->em->flush();
    }

}
