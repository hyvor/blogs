<?php

declare(strict_types=1);

namespace App\Domains\Blog\Fillers;

use App\Domains\Route\RouteRepository;
use App\Models\Blog;

/**
 * @phpstan-type RouteDef array{name: string, match: string, template: string, posts_filter?: string}
 */
class RouteFiller implements FillerInterface
{

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

    public function __construct(private Blog $blog)
    {
    }

    public function fill(): void
    {
        foreach (self::ROUTES as $route) {
            RouteRepository::createRoute(
                $this->blog,
                $route['name'],
                $route['match'],
                $route['template'],
                $route['posts_filter'] ?? null
            );
        }
    }
}
