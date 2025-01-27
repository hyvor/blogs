<?php

namespace Tests\Feature\ConsoleAPI\Languages;

use App\Data\Enums\LanguageDirectionEnum;
use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Post\Jobs\PostVariantUpdateTsLanguageJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates the language', function () {

    Event::fake();
    Queue::fake();

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
    Queue::assertPushed(PostVariantUpdateTsLanguageJob::class);
});

it('updates RTL', function() {
    Event::fake();

    $blog = blogWithAccess();
    $language = addPrimaryLanguage($blog);

    $code = 'ar';
    $name = 'Arabic';

    consoleApi($blog, 'PATCH', "/language/$language->id", [
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

    $language->refresh();

    expect($language->code)->toBe($code);
    expect($language->name)->toBe($name);
    expect($language->direction)->toBe(LanguageDirectionEnum::RTL);

    Event::assertDispatched(LanguageChangedEvent::class);


});

it('cannot take other language codes', function () {

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
