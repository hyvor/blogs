<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Domains\Blog\Fillers\NavigationFiller;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->blog = blogWithAccess();
    addPrimaryLanguage($this->blog);
    (new NavigationFiller($this->blog))->fill();

    $this->navs = $this->blog->navigations;
});

it('gets navigations', function () {
    consoleApi($this->blog, 'GET', '/navigations')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has(count($this->navs))
                ->each(
                    fn (AssertableJson $json) => $json->has('id')
                        ->has('url')
                        ->has('sort')
                        ->etc()
                );
        });
});
