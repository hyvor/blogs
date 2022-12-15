<?php

namespace Tests\Feature\ConsoleAPI\Navigation;

use App\Domains\Navigation\Events\NavigationVariantChangedEvent;
use App\Models\Navigation;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('requires a valid language ID', function () {

    $blog = blogWithAccess();
    $nav = Navigation::factory()->create(['blog_id' => $blog]);

    consoleApi($blog, 'POST', "/navigation/$nav->id/variant", [
        'name' => 'Hi',
        'language_id' => 100,
    ])
        ->assertUnprocessable()
        ->assertSee('Language not found');
});

it('creates a navigation variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    $nav = Navigation::factory()->create(['blog_id' => $blog]);

    $name = 'Hyvor';
    consoleApi($blog, 'POST', "/navigation/$nav->id/variant", [
        'name' => $name,
        'language_id' => $blog->languages[0]->id,
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json->where('name', $name)->etc());

    Event::assertDispatched(NavigationVariantChangedEvent::class);
});
