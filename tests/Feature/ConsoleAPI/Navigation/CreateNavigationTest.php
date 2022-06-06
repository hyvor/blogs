<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Domains\Language\LanguageRepository;
use App\Models\Navigation;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a navigation', function() {

    $url = '/about';
    $name = 'About';

    $languageId = LanguageRepository::getPrimaryLanguage(blog())->id;

    $this->callConsoleApi('POST', '/navigation', [
        'url' => $url,
        'name' => $name,
        'type' => 'footer'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('url', $url)
                ->where('type', 'footer')
                ->where("variants.0.name", $name)
                ->etc()
        );

});

it('creates header navigations', function() {

    $this->callConsoleApi('POST', '/navigation', [
        'url' => 'https://something.com/some',
        'name' => 'some',
        'type' => 'header'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('type', 'header')->etc());

});

it('does not allow to create more than the limit', function() {

    Navigation::factory()->count(config('limits.max_navigations_per_type_per_blog'))->create([
        'blog_id' => blog(),
        'type' => 'footer'
    ]);

    $this->callConsoleApi('POST', '/navigation', [
        'url' => 'https://something.com/some',
        'name' => 'some',
        'type' => 'footer'
    ])
        ->assertUnprocessable()
        ->assertSee('exceeded');


});