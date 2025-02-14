<?php

namespace Database\Factories;

use App\Domains\Blog\Fillers\RouteFiller;
use App\Models\Blog;
use App\Models\Route;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Route>
 * @phpstan-import-type RouteDef from RouteFiller
 */
class RouteFactory extends Factory
{
    public function definition()
    {
        return [
            'blog_id' => Blog::factory(),
            'name' => $this->faker->name,
            'match' => '/' . $this->faker->word,
            'template' => $this->faker->word,
        ];
    }

    /**
     * @return Collection<int, Route>
     */
    public static function defaultsFor(Blog $blog): Collection
    {
        return self::fromArray($blog, RouteFiller::ROUTES);
    }

    /**
     * @return Collection<int, Route>
     */
    public static function fromArray(Blog $blog, array $routes): Collection
    {
        $routesReturn = new Collection();
        foreach ($routes as $route) {
            $routesReturn->push($blog->routes()->create($route));
        }
        return $routesReturn;
    }
}
