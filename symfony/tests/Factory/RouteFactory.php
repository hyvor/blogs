<?php

namespace App\Tests\Factory;

use App\Entity\Blog;
use App\Entity\Route;
use App\Service\Route\RouteService;
use App\Tests\Factory\BlogFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Route>
 */
final class RouteFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Route::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'blog' => BlogFactory::new(),
            'is_enabled' => true,
            'match' => self::faker()->text(255),
            'name' => self::faker()->text(255),
            'template' => self::faker()->text(255),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Route $route): void {})
        ;
    }

    /**
     * @return Route[]
     */
    public static function createDefaultsFor(Blog $blog): array
    {
        return self::createManyFromArray($blog, RouteService::ROUTES);
    }

    /**
     * @param array<array<string, mixed>> $routes
     * @return Route[]
     */
    public static function createManyFromArray(Blog $blog, array $routes): array
    {
        $createdRoutes = [];
        foreach ($routes as $route) {
            $route['blog'] = $blog;
            $createdRoutes[] = self::createOne($route);
        }
        return $createdRoutes;
    }
}
