<?php

namespace Tests\Feature\ConsoleAPI\Languages;

use App\Domains\Language\Events\LanguageChangedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a language', function () {
    Event::fake();

    $code = 'si';
    $name = 'සිංහල';

    $blog = blogWithAccess();

    consoleApi($blog, 'POST', '/language', [
        'code' => $code,
        'name' => $name,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('id')
                ->where('code', $code)
                ->where('name', $name)
                ->where('direction' , 'ltr')
                ->etc()
        );

    Event::assertDispatched(LanguageChangedEvent::class);
});

it('creates a RTL language', function() {

    Event::fake();

    $code = 'ar';
    $name = 'Arabic';

    $blog = blogWithAccess();

    consoleApi($blog, 'POST', '/language', [
        'code' => $code,
        'name' => $name,
        'direction' => 'rtl'
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('id')
                ->where('code', $code)
                ->where('name', $name)
                ->where('direction' , 'rtl')
                ->etc()
        );

    Event::assertDispatched(LanguageChangedEvent::class);

});

it('does not create language if the code already exists', function () {


    $blog = blogWithAccess();
    $language = addPrimaryLanguage($blog);

    consoleApi($blog, 'POST', '/language', [
        'code' => $language->code,
        'name' => 'English',
    ])
        ->assertUnprocessable()
        ->assertSee(['Language', 'exists']);
});
