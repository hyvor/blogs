<?php

namespace Tests\Feature\ConsoleAPI\Languages;

use App\Domains\Language\Events\LanguageChangedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates the language', function () {
    Event::fake();

    $blog = blogWithAccess();
    $language = addPrimaryLanguage($blog);

    $code = 'si';
    $name = 'සිංහල';

    consoleApi($blog, 'PATCH', "/language/$language->id", [
        'code' => $code,
        'name' => $name,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('id')
                ->where('code', $code)
                ->where('name', $name)
                ->etc()
        );

    $language->refresh();

    expect($language->code)->toBe($code);
    expect($language->name)->toBe($name);

    Event::assertDispatched(LanguageChangedEvent::class);
});

it('cannot take other languages', function () {

    $blog = blogWithAccess();

    $language = addPrimaryLanguage($blog);
    $language2 = addLanguage($blog);

    consoleApi($blog, 'PATCH', "/language/$language->id", [
        'code' => $language2->code,
        'name' => 'some name',
    ])
        ->assertUnprocessable()
        ->assertSee(['code', 'exists']);
});
