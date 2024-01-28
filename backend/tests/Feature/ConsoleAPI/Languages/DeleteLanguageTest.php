<?php

namespace Tests\Feature\ConsoleAPI\Languages;

use App\Domains\Language\Events\LanguageChangedEvent;
use App\Domains\Language\Jobs\DeleteLanguageVariants;
use App\Models\BlogVariant;
use App\Models\Language;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

it('deletes a language and data related to it', function () {
    Event::fake();

    $blog = blogWithAccess();
    $language = Language::factory()->create(['code' => 'si', 'blog_id' => $blog]);

    $variant = BlogVariant::create([
        'blog_id' => $blog->id,
        'language_id' => $language->id,
        'name' => 'Sinhala',
    ]);

    consoleApi($blog, 'DELETE', "/language/$language->id")
        ->assertOk();

    expect(BlogVariant::find($variant->id))->toBeNull();
    Event::assertDispatched(LanguageChangedEvent::class);
});

it('does not delete the primary language', function () {
    $blog = blogWithAccess();
    $language = addPrimaryLanguage($blog);

    consoleApi($blog, 'DELETE', "/language/$language->id")
        ->assertUnprocessable()
        ->assertSee(['Primary', 'cannot']);
});
