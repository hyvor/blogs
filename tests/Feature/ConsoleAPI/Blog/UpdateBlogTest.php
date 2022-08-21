<?php

namespace Tests\Feature\ConsoleAPI\Blog;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates blog data', function () {
    Event::fake();

    $subdomain = 'new-subdomain';
    $hostingAt = 'domain';
    $hostingDomain = 'hyvor.com';

    $this->callConsoleApi('PATCH', '/blog', [
        'subdomain' => $subdomain,
        'hosting_at' => $hostingAt,
        'hosting_domain' => $hostingDomain,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('subdomain', $subdomain)
                ->where('hosting_at', $hostingAt)
                ->where('hosting_domain', $hostingDomain)
                ->etc()
        );

    Event::assertDispatched(BlogUpdatedEvent::class);
});

it('updates self URL and clears custom domain', function () {
    blog()->update(['hosting_domain' => 'hyvor.com']);

    $url = 'https://hyvor.com/blog';

    $this->callConsoleApi('PATCH', '/blog', [
        'hosting_at' => 'self',
        'hosting_url' => $url,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('hosting_at', 'self')
                ->where('hosting_url', $url)
                ->where('hosting_domain', null) // <- clears custom domain
                ->etc()
        );
});

/**
 * I am bored to test everything
 * Not sure if its worthy
 */
it('update metadata', function () {
    $logo = 'https://example.com/image.png';
    $cover = 'https://example.com/cover.png';

    $this->callConsoleApi('PATCH', '/blog', [
        'logo_url' => $logo,
        'cover_url' => $cover,
    ])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->where('logo_url', $logo)
                ->where('cover_url', $cover)
                ->etc()
        );
});

it('validates URLs', function () {
    $this->callConsoleApi('PATCH', '/blog', [
        'logo_url' => 'hello',
    ])
        ->assertUnprocessable()
        ->assertSee(['valid', 'URL']);
});
