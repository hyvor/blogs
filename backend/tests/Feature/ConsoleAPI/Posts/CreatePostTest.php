<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostCreatedEvent;
use App\Domains\User\UserRepository;
use App\Models\PostAuthor;
use App\Models\PostVariant;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates a post and the primary variant', function () {
    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    $post = consoleApi($blog, 'POST', '/post')
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('id')
                ->where('is_page', false)
                ->etc()
        )
        ->json();

    $primaryLanguage = $blog->languages[0];

    $variant = PostVariant::where('post_id', $post['id'])
        ->where('language_id', $primaryLanguage->id)
        ->first();

    $this->assertNotNull($variant);

    Event::assertDispatched(PostCreatedEvent::class);
});

it('creates a page', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    consoleApi($blog, 'POST', '/post', ['is_page' => true])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('id')
                ->where('is_page', true)
                ->etc()
        );
});

it('adds the author', function () {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    addDefaultRoutes($blog);

    $post = consoleApi($blog, 'POST', '/post')
        ->assertOk()
        ->json();

    $user = UserRepository::getUserByBlogIdAndHyvorUserId($blog->id, 1);

    expect(
        PostAuthor::where('post_id', $post['id'])
            ->where('user_id', $user->id)
            ->first()
    )->toBeInstanceOf(PostAuthor::class);
});
