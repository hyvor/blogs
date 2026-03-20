<?php

namespace App\Service\Blog\Seeder;

use App\Entity\Blog;
use App\Entity\Route;
use Doctrine\ORM\EntityManagerInterface;

class RouteSeeder
{
    private const ROUTES = [
        ['name' => 'post',   'match' => '/{slug}',        'template' => 'post'],
        ['name' => 'page',   'match' => '/{slug}',        'template' => 'page,post'],
        ['name' => 'index',  'match' => '/',              'template' => 'index',        'posts_filter' => ''],
        ['name' => 'tag',    'match' => '/tag/{slug}',    'template' => 'tag,index',    'posts_filter' => 'tag.slug={slug}'],
        ['name' => 'author', 'match' => '/author/{slug}', 'template' => 'author,index', 'posts_filter' => 'author.slug={slug}'],
    ];

    public function __construct(private EntityManagerInterface $em) {}

    public function seed(Blog $blog): void
    {
        foreach (self::ROUTES as $def) {
            $route = new Route();
            $route->setBlog($blog);
            $route->setBlogId($blog->getId());
            $route->setName($def['name']);
            $route->setMatch($def['match']);
            $route->setTemplate($def['template']);
            $route->setPostsFilter($def['posts_filter'] ?? null);
            $this->em->persist($route);
        }

        $this->em->flush();
    }
}
