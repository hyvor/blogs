<?php

namespace Tests\Feature\ConsoleAPI\Routes;

use Illuminate\Testing\Fluent\AssertableJson;

it('creates a route', function () {
    $name = 'Hyvor';
    $match = '/hyvor';
    $template = 'index.twig';
    $postsFilter = 'tag.slug={slug}';
    $contentType = 'text/html';

    $this->callConsoleApi('POST', '/route', [
        'name' => $name,
        'match' => $match,
        'template' => $template,
        'posts_filter' => $postsFilter,
        'content_type' => $contentType,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->where('name', $name)
                ->where('match', $match)
                ->where('template', $template)
                ->where('posts_filter', $postsFilter)
                ->where('content_type', $contentType)
                ->etc()
        );
});
