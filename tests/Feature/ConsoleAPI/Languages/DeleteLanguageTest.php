<?php

namespace Tests\Feature\ConsoleAPI\Languages;

use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Language\Jobs\DeleteLanguageVariants;
use App\Models\Language;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('deletes a language', function () {
    Event::fake();
    Queue::fake();

    $blog = blogWithAccess();

    $language = Language::factory()->create(['code' => 'si', 'blog_id' => $blog]);

    consoleApi($blog, 'DELETE', "/language/$language->id")
        ->assertOk();

    Queue::assertPushed(DeleteLanguageVariants::class);
    Event::assertDispatched(LanguageChangedEvent::class);
});

it('does not delete the primary language', function () {
    $blog = blogWithAccess();
    $language = addPrimaryLanguage($blog);

    consoleApi($blog, 'DELETE', "/language/$language->id")
        ->assertUnprocessable()
        ->assertSee(['Primary', 'cannot']);
});
