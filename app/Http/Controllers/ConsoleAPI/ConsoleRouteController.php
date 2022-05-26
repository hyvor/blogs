<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\RouteObject;
use App\Domains\Route\RouteRepository;

use App\Http\Controllers\Controller;
use App\Models\Blog;
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
            'posts_filter' => 'required|string|nullable',
            'content_type' => 'required|string|nullable',
        ]);

        $name = $request->input('name');
        $match = $request->input('match');
        $template = $request->input('template');
        $postsFilter = $request->input('posts_filter');
        $contentType = $request->input('content_type');

        $route = RouteRepository::createRoute($blog, $name, $match, $template, $postsFilter, $contentType);

        return response()->json(new RouteObject($route));
    }

    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'name' => 'required|string',
            'match' => 'required|string',
            'template' => 'required|string',
            'posts_filter' => 'required|string|nullable',
            'content_type' => 'required|string|nullable',
        ]);

        $id = $request->route('id');
        $name = $request->input('name');
        $match = $request->input('match');
        $template = $request->input('template');
        $postsFilter = $request->input('postsFilter') ?? null;
        $contentType = $request->input('contentType') ?? null;

        $route = RouteRepository::updateRoute($id, $name, $match, $template, $postsFilter, $contentType);

        return response()->json($route);
    }

    public function delete(Request $request)
    {
        $id = $request->route('id');
        $deleteRoute = RouteRepository::deleteRoute($id);

        return response()->json($deleteRoute);
    }
}
