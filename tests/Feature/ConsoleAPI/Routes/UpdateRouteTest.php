<?php

namespace Tests\Feature\ConsoleAPI\Routes;

use App\Models\Route;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates route', function () {
    $route = Route::factory()->create(['blog_id' => blog()]);

    $name = 'new-name';
    $match = '/new-match';
    $template = 'new-template';
    $postsFilter = 'slug = {slug}';
    $contentType = 'text/svg';

    $this->callConsoleApi('PATCH', "/route/$route->id", [
        'name' => $name,
        'match' => $match,
        'template' => $template,
        'posts_filter' => $postsFilter,
        'content_type' => $contentType,
    ])
       ->assertOk()
       ->assertJson(fn (AssertableJson $json) => $json->where('name', $name)
                ->where('match', $match)
                ->where('template', $template)
                ->where('posts_filter', $postsFilter)
                ->where('content_type', $contentType)
                ->etc()
       );
});

it('supports nullable', function () {
    $route = Route::factory()->create([
        'blog_id' => blog(),
        'posts_filter' => 'test',
        'content_type' => 'text/html',
    ]);

    $this->callConsoleApi('PATCH', "/route/$route->id", [
        'posts_filter' => null,
        'content_type' => null,
    ])
        ->assertOk()
        ->assertJson(function ($json) {
            $json->where('posts_filter', null)
                ->where('content_type', null)
                ->etc();
        });
});
