<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use Illuminate\Testing\Fluent\AssertableJson;

it('gets blog', function() {

    $this->callConsoleApi('GET', '/blog')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('blog')
                ->has('counts')
                ->has('users')
                ->has('tags')
                ->has('languages');
        });

});