<?php

namespace Tests\Feature\ConsoleAPI\Posts;

// endpoint = GET /posts

use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->endpoint = '/posts';
    $this->defaultLanguage = $this->blog->languages[0];
    $this->post = Post::where('blog_id', $this->blog->id)
        ->where('is_page', false)
        ->first();
});

it('gets posts', function () {
    $this
        ->callConsoleApi('GET', $this->endpoint)
        ->assertJson(function (AssertableJson $json) {
            $json
                ->each(function (AssertableJson $json) {
                    $json->where('is_page', false)
                        ->etc();
                });
        });
});

it('validates', function () {
    $this->callConsoleApi('GET', $this->endpoint, ['status' => 'published'])->assertOk();
    $this->callConsoleApi('GET', $this->endpoint, ['status' => 'draft'])->assertOk();
    $this->callConsoleApi('GET', $this->endpoint, ['status' => 'scheduled'])->assertOk();
    $this->callConsoleApi('GET', $this->endpoint, ['status' => 'featured'])->assertOk();
    $this->callConsoleApi('GET', $this->endpoint, ['status' => 'something else'])->assertUnprocessable();

    $this->callConsoleApi('GET', $this->endpoint, ['author_id' => 1])->assertOk();
    $this->callConsoleApi('GET', $this->endpoint, ['author_id' => 'a string'])->assertUnprocessable();

    $this->callConsoleApi('GET', $this->endpoint, ['tag_id' => 1])->assertOk();
    $this->callConsoleApi('GET', $this->endpoint, ['tag_id' => 'a string'])->assertUnprocessable();
});

it('filters by post status - published', function () {
    $this
        ->callConsoleApi('GET', $this->endpoint, ['status' => 'published'])
        ->assertJson(function (AssertableJson $json) {
            $json->each(function (AssertableJson $json) {
                $json->where("variants.{$this->defaultLanguage->id}.status", 'published')
                    ->etc();
            });
        });
});

it('filters by featured', function () {

    // feature a post
    $this->post->update(['is_featured' => true]);

    $this
        ->callConsoleApi('GET', $this->endpoint, ['status' => 'featured'])
        ->assertJson(
            fn (AssertableJson $json) =>
            $json
                ->has(1)
                ->first(
                    fn (AssertableJson $json) =>
                    $json
                        ->where('id', $this->post->id)
                        ->etc()
                )
        );
});

it('filters by author ID', function () {

    // delete all post author assigns
    PostAuthor::query()->delete();

    PostAuthor::create([
        'post_id' => $this->post->id,
        'user_id' => $this->user->id,
    ]);

    $this
        ->callConsoleApi('GET', $this->endpoint, ['author_id' => $this->user->id])
        ->assertJson(
            fn (AssertableJson $json) =>
            $json
                ->has(1)
                ->first(
                    fn (AssertableJson $json) =>
                    $json
                        ->where('authors.0.id', $this->user->id)
                        ->etc()
                )
        );
});

it('filters by tag ID', function () {
    $this->tag = $this->blog->tags()->first();

    // delete all post author assigns
    PostTag::query()->delete();

    PostTag::create([
        'post_id' => $this->post->id,
        'tag_id' => $this->tag->id,
    ]);

    $this
        ->callConsoleApi('GET', $this->endpoint, ['tag_id' => $this->tag->id])
        ->assertJson(
            fn (AssertableJson $json) =>
        $json
            ->has(1)
            ->first(
                fn (AssertableJson $json) =>
            $json
                ->where('tags.0.id', $this->tag->id)
                ->etc()
            )
        );
});

it('filters by timestamps', function () {

    // change all posts' published at days
    Post::query()->update(['published_at' => now()->subDays(10)]);

    $date = now();

    $this->post->update(['published_at' => $date]);

    $timestamp = $date->timestamp;

    $this
        ->callConsoleApi('GET', $this->endpoint, [
            'start_timestamp' => $timestamp - 1,
            'end_timestamp' => $timestamp + 1,
        ])
        ->assertJson(
            fn (AssertableJson $json) =>
            $json
                ->has(1)
                ->first(
                    fn (AssertableJson $json) =>
                    $json
                        ->where('id', $this->post->id)
                        ->etc()
                )
        );
});
