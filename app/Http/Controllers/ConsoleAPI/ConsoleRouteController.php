<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\RouteObject;
use App\Domains\Route\RouteRepository;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Route;
use Illuminate\Http\Request;

class ConsoleRouteController extends Controller
{
    public function get(Blog $blog)
    {
        $routes = RouteRepository::getRoutes($blog)->mapInto(RouteObject::class);

        return response()->json($routes);
    }

    public function create(Request $request, Blog $blog)
    {
        $request->validate([
            'name' => 'required|string',
            'match' => 'required|string',
            'template' => 'required|string',
            'posts_filter' => 'string|nullable',
            'content_type' => 'string|nullable',
        ]);

        $name = $request->input('name');
        $match = $request->input('match');
        $template = $request->input('template');
        $postsFilter = $request->input('posts_filter');
        $contentType = $request->input('content_type');

        $route = RouteRepository::createRoute($blog, $name, $match, $template, $postsFilter, $contentType);

        return response()->json(new RouteObject($route));
    }

    public function update(Request $request, Route $route)
    {
        $validations = [
            'name' => 'string',
            'match' => 'string',
            'template' => 'string',
            'posts_filter' => 'string|nullable',
            'content_type' => 'string|nullable',
        ];

        $request->validate($validations);

        $updatables = array_keys($validations);
        $updates = [];

        foreach ($updatables as $updatable) {
            if ($request->has($updatable)) {
                $updates[$updatable] = $request->input($updatable);
            }
        }

        $route = RouteRepository::updateRoute($route, $updates);

        return response()->json(new RouteObject($route));
    }

    public function delete(Route $route)
    {
        RouteRepository::deleteRoute($route);

        return response()->json();
    }
}
