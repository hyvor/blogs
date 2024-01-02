<?php declare(strict_types=1);

namespace Tests\Feature\ConsoleAPI\Posts;

// endpoint = GET /posts

use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\MeilisearchInefficient;

beforeEach(function () {

    $this->blog = blogWithAccess();
    addDefaultRoutes($this->blog);

    $this->endpoint = '/posts';
    $this->defaultLanguage = addPrimaryLanguage($this->blog);

});

it('gets posts', function () {

    addPublishedPost($this->blog);

    consoleApi($this->blog,'GET', $this->endpoint)
        ->assertJson(function (AssertableJson $json) {
            $json
                ->each(function (AssertableJson $json) {
                    $json->where('is_page', false)
                        ->etc();
                });
        });
});

it('validates', function () {

    consoleApi($this->blog, 'GET', $this->endpoint, ['status' => 'published'])->assertOk();
    consoleApi($this->blog, 'GET', $this->endpoint, ['status' => 'draft'])->assertOk();
    consoleApi($this->blog, 'GET', $this->endpoint, ['status' => 'scheduled'])->assertOk();
    consoleApi($this->blog, 'GET', $this->endpoint, ['status' => 'featured'])->assertOk();
    consoleApi($this->blog, 'GET', $this->endpoint, ['status' => 'something else'])->assertUnprocessable();

    consoleApi($this->blog, 'GET', $this->endpoint, ['author_id' => 1])->assertOk();
    consoleApi($this->blog, 'GET', $this->endpoint, ['author_id' => 'a string'])->assertUnprocessable();

    consoleApi($this->blog, 'GET', $this->endpoint, ['tag_id' => 1])->assertOk();
    consoleApi($this->blog, 'GET', $this->endpoint, ['tag_id' => 'a string'])->assertUnprocessable();
});

it('filters by post status - published', function () {

    addPosts($this->blog, 2, [], ['status' => 'published']);

    consoleApi($this->blog, 'GET', $this->endpoint, ['status' => 'published'])
        ->assertJsonCount(2)
        ->assertJson(function (AssertableJson $json) {
            $json->each(function (AssertableJson $json) {
                $json->where('variants.0.status', 'published')
                    ->etc();
            });
        });
});

it('filters by featured', function () {


    $posts = addPosts($this->blog, 2, [], ['status' => 'published']);
    $posts[0]->is_featured = true;
    $posts[0]->save();

    consoleApi($this->blog, 'GET', $this->endpoint, ['status' => 'featured'])
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->has(1)
                ->first(
                    fn (AssertableJson $json) => $json
                        ->where('id', $posts[0]->id)
                        ->etc()
                )
        );


});

it('filters by author ID', function () {

    $post = addPost($this->blog);
    $user = addUser($this->blog);

    addAuthorToPost($post, $user);

    consoleApi($this->blog, 'GET', $this->endpoint, ['author_id' => $user->id])
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->has(1)
                ->first(
                    fn (AssertableJson $json) => $json
                        ->where('authors.0.id', $user->id)
                        ->etc()
                )
        );

});

it('filters by tag ID', function () {

    $post = addPost($this->blog);
    $tag = addTag($this->blog);

    addTagToPost($post, $tag);

    consoleApi($this->blog, 'GET', $this->endpoint, ['tag_id' => $tag->id])
        ->assertJson(
            fn (AssertableJson $json) => $json
            ->has(1)
            ->first(
                fn (AssertableJson $json) => $json
                ->where('tags.0.id', $tag->id)
                ->etc()
            )
        );
});

it('filters by timestamps', function () {

    $date = now();

    addPosts($this->blog, 2, ['published_at' => now()->subDays(10)], ['status' => 'published']);
    $post = addPublishedPost($this->blog, ['published_at' => $date]);


    $timestamp = $date->timestamp;

    consoleApi($this->blog, 'GET', $this->endpoint, [
            'start_timestamp' => $timestamp - 1,
            'end_timestamp' => $timestamp + 1,
        ])
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->has(1)
                ->first(
                    fn (AssertableJson $json) => $json
                        ->where('id', $post->id)
                        ->etc()
                )
        );
});

it('searches posts', function() {

    $posts = addPosts($this->blog, 2, [], ['title' => 'Henry VIII']);

    MeilisearchInefficient::waitForAllTasks();

    consoleApi($this->blog, 'GET', $this->endpoint, ['search' => 'henry'])
        ->assertJsonCount(2)
        ->assertJsonPath('0.id', $posts[0]->id)
        ->assertJsonPath('1.id', $posts[1]->id);

});


it('searches posts by language', function() {

    $language2 = addLanguage($this->blog);

    // correct language
    $post1 = postWithVariant(
        ['blog_id' => $this->blog->id],
        ['language_id' => $language2->id, 'title' => 'Henry VIII']
    );

    // primary language
    $post2 = postWithVariant(
        ['blog_id' => $this->blog->id],
        ['language_id' => $this->defaultLanguage->id, 'title' => 'Henry VIII']
    );

    MeilisearchInefficient::waitForAllTasks();

    consoleApi($this->blog, 'GET', $this->endpoint, [
        'search' => 'henry',
        'language_id' => $language2->id
    ])
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $post1->id);

    consoleApi($this->blog, 'GET', $this->endpoint, [
        'search' => 'henry',
        'language_id' => $this->defaultLanguage->id
    ])
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $post2->id);

});

it('searches with published', function() {

    // correct language
    $post1 = postWithVariant(
        ['blog_id' => $this->blog->id],
        ['language_id' => $this->defaultLanguage->id, 'title' => 'Henry VIII', 'status' => 'draft']
    );

    // primary language
    $post2 = postWithVariant(
        ['blog_id' => $this->blog->id],
        ['language_id' => $this->defaultLanguage->id, 'title' => 'Henry VIII', 'status' => 'published']
    );

    MeilisearchInefficient::waitForAllTasks();

    consoleApi($this->blog, 'GET', $this->endpoint, [
        'search' => 'henry',
        'status' => 'published'
    ])
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $post2->id);

});