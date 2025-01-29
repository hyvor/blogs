<?php

namespace Tests\Feature\ConsoleAPI\Redirects;

use App\Models\Redirect;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->blog = blogWithAccess();
    Redirect::factory()->count(3)->create([
        'blog_id' =>$this->blog,
    ]);
});

it('get redirects', function () {
    consoleApi($this->blog, 'GET', '/redirects', [
        'limit' => 2,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
            ->has(2)
            ->each(function (AssertableJson $json) {
                $json->has('id')
                    ->has('path')
                    ->has('to')
                    ->etc();
            })
        );
});

it('works with offset', function () {
    consoleApi($this->blog, 'GET', '/redirects', [
        'limit' => 2,
        'offset' => 2,
    ])
        ->assertOk()
        ->assertJsonCount(1);
});

it('searches', function () {
    $blog = blogWithAccess();
    Redirect::factory()->create([
        'blog_id' => $blog,
        'path' => 'this/is/search/parameter',
    ]);

    consoleApi($blog, 'GET', '/redirects', [
        'search' => 'search',
    ])
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.path', 'this/is/search/parameter');
});
