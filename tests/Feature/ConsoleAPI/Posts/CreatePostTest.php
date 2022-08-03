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

    $post = $this
        ->callConsoleApi('POST', '/post')
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('id')
                ->where('is_page', false)
                ->etc()
        )
        ->json();

    $primaryLanguage = $this->blog->languages[0];

    $variant = PostVariant::where('post_id', $post['id'])
        ->where('language_id', $primaryLanguage->id)
        ->first();

    $this->assertNotNull($variant);

    Event::assertDispatched(PostCreatedEvent::class);
});

it('creates a page', function () {
    $this
        ->callConsoleApi('POST', '/post', ['is_page' => true])
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json->has('id')
                ->where('is_page', true)
                ->etc()
        );
});

it('adds the author', function () {
    $post = $this
        ->callConsoleApi('POST', '/post')
        ->assertOk()
        ->json();

    $user = UserRepository::getUserByBlogIdAndHyvorUserId(config('test.blog_id'), config('test.hyvor_user_id'));

    expect(
        PostAuthor::where('post_id', $post['id'])
            ->where('user_id', $user->id)
            ->first()
    )->toBeInstanceOf(PostAuthor::class);
});
