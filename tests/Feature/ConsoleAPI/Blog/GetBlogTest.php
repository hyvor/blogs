<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use Illuminate\Testing\Fluent\AssertableJson;

it('gets blog', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);

    consoleApi($blog, 'GET', '/blog')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('blog')
                ->has('counts')
                ->has('users')
                ->has('tags')
                ->has('languages');
        });
});
