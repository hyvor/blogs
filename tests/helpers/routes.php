<?php

use App\Domains\Blog\Fillers\RouteFiller;
use App\Models\Blog;
use Illuminate\Support\Str;

function addDefaultRoutes(Blog $blog) {
    foreach (RouteFiller::ROUTES as $route) {
        $blog->routes()->create($route);
    }
}

function addRoute(Blog $blog, string $match, string $template = 'index.twig', string $name = null) {

    $name ??= Str::random(10);

    $blog->routes()->create([
        'match' => $match,
        'template' => $template,
        'name' => $name,
    ]);
}