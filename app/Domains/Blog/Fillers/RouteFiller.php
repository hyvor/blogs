<?php

namespace App\Domains\Blog\Fillers;

use App\Domains\Route\RouteRepository;
use App\Models\Blog;

class RouteFiller implements FillerInterface
{
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
            'posts_filter' => 'tag.slug = {slug}',
        ],
        // author
        [
            'name' => 'author',
            'match' => '/author/{slug}',
            'template' => 'author,index',
            'posts_filter' => 'author.slug = {slug}',
        ],
        // search
        [
            'name' => 'search',
            'match' => '/search/{search}',
            'template' => 'search,index',
        ],
    ];

    public function __construct(private Blog $blog)
    {
    }

    public function fill()
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
