<?php

namespace Database\Factories;

use App\Domains\Blog\Fillers\RouteFiller;
use App\Models\Blog;
use App\Models\Route;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Route>
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
    public static function defaultsFor(Blog $blog, ?string $name = null): Collection
    {
        $routes = new Collection();
        foreach (RouteFiller::ROUTES as $route) {
            if ($name !== null && $route['name'] !== $name) {
                continue;
            }

            $routes->push($blog->routes()->create($route));
        }

        return $routes;
    }
}
