<?php

namespace Tests\Feature\DataAPI;

use App\Models\Blog;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\PostVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    // published all posts
    DB::statement('UPDATE post_variants SET status = "published"');
});

it('fetches posts without params', function () {
    $this
        ->callDataApi('/posts')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->has(
                    'data',
                    25,
                    fn (AssertableJson $json) =>
                    $json->where('language.code', 'en')
                        ->etc()
                )
                ->has('pagination');
        });
});


it('fetches pages', function () {
    $this
        ->callDataApi('/posts', [
            'limit' => 2,
            'pages' => true,
        ])
        ->assertJson(function (AssertableJson $json) {
            $json->has('data', 2, function (AssertableJson $json) {
                $json->where('is_page', true)
                    ->etc();
            })->etc();
        });
});

it('works with language', function () {
    $this
        ->callDataApi('/posts', [
            'language' => 'fr',
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has(
                'data',
                25,
                fn (AssertableJson $json) =>
            $json->where('language.code', 'fr')
                ->etc()
            )
                ->has('pagination');
        });
});

it('does not work with wrong language', function () {
    $this->callDataApi('/posts', [
        'language' => 'jp',
    ])->assertUnprocessable();
});

it('gives correct limit', function () {
    $this
        ->callDataApi('/posts', [
            'limit' => 3,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('data', 3)
                ->etc();
        });
});

it('gives correct page for pagination', function () {
    $blog = Blog::find(config('test.blog_id'));
    $blog->posts->map(fn ($post) => $post->variants()->delete());
    $blog->posts()->delete();
    Post::where('blog_id', config('test.blog_id'))->delete();

    // now add 3
    $posts = Post::factory()->count(3)
        ->has(
            PostVariant::factory()
            ->count(1)
            ->state([
                'language_id' => $blog->languages[0],
                'status' => 'published',
            ]),
            'variants'
        )
        ->create(['blog_id' => $blog]);

    $response = $this->callDataApi('/posts', [
        'limit' => 2,
        'page' => 2,
        'sort' => 'id ASC',
    ], $blog->subdomain);

    $response->assertOk()
        ->assertJson(function (AssertableJson $json) use ($posts) {
            $json->has('data.0', function (AssertableJson $json) use ($posts) {
                $json->where('id', $posts[2]->id)
                    ->etc();
            })
                ->etc();
        });
});

it('does not work for invalid limit', function () {
    $this->callDataApi('/posts', [
        'limit' => 0,
    ])->assertUnprocessable();
});

it('does not work for invalid page', function () {
    $this->callDataApi('/posts', [
        'page' => -1,
    ])->assertUnprocessable();
});

it('does not work for invalid sort', function () {
    $this->callDataApi('/posts', [
        'sort' => 'something_invalid',
    ])->assertUnprocessable();
});

it('does not work for invalid sort method', function () {
    $this->callDataApi('/posts', [
        'sort' => 'published_at SOME',
    ])->assertUnprocessable();
});

it('sorts by published_at DESC correctly', function () {
    $response = $this->callDataApi('/posts', [
        'sort' => 'published_at',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['published_at'] >= $response['data'][1]['published_at'] &&
        $response['data'][1]['published_at'] >= $response['data'][2]['published_at']
    );
});

it('sorts by published_at ASC correctly', function () {
    $response = $this->callDataApi('/posts', [
        'sort' => 'published_at ASC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['published_at'] <= $response['data'][1]['published_at'] &&
        $response['data'][1]['published_at'] <= $response['data'][2]['published_at']
    );
});

it('sorts by created_at DESC correctly', function () {
    $response = $this->callDataApi('/posts', [
        'sort' => 'created_at',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['created_at'] >= $response['data'][1]['created_at'] &&
        $response['data'][1]['created_at'] >= $response['data'][2]['created_at']
    );
});

it('sorts by updated_at ASC correctly', function () {
    $response = $this->callDataApi('/posts', [
        'sort' => 'updated_at ASC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['updated_at'] <= $response['data'][1]['updated_at'] &&
        $response['data'][1]['updated_at'] <= $response['data'][2]['updated_at']
    );
});

it('sorts by id DESC correctly', function () {
    $response = $this->callDataApi('/posts', [
        'sort' => 'id',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['id'] >= $response['data'][1]['id'] &&
        $response['data'][1]['id'] >= $response['data'][2]['id']
    );
});

it('sorts by is_featured DESC correctly', function () {

    // Posts added in the Seeder are not featured
    $post = getAPost();
    $post->is_featured = true;
    $post->save();

    $response = $this->callDataApi('/posts', [
        'sort' => 'is_featured DESC',
        'limit' => 3,
    ])->json();

    $this->assertTrue($response['data'][0]['is_featured'] && $response['data'][0]['id'] === $post->id);
});

it('sorts by title DESC correctly', function () {
    $response = $this->callDataApi('/posts', [
        'sort' => 'title DESC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['title'] >= $response['data'][1]['title'] &&
        $response['data'][1]['title'] >= $response['data'][2]['title']
    );
});

it('sorts by words DESC correctly', function () {
    $response = $this->callDataApi('/posts', [
        'sort' => 'words DESC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['words'] >= $response['data'][1]['words'] &&
        $response['data'][1]['words'] >= $response['data'][2]['words']
    );
});

it('correctly filters by defined keys', function () {
    $response = $this->callDataApi('/posts', [
        'keys' => 'id',
        'limit' => 3,
    ]);

    $response->assertJson(function (AssertableJson $json) {
        $json->has('data', 3, function (AssertableJson $json) {
            $json->has('id')
                ->missing('slug');
        })->etc();
    });
});

it('filters posts by id', function () {
    $post = getAPost();

    $this
        ->callDataApi('/posts', [
            'filter' => "id=$post->id",
        ])
        ->assertJsonPath('data.0.id', $post->id)
        ->assertJsonCount(1, 'data');
});

it('filters by published_at', function () {
    $time = new Carbon('yesterday');

    $post = getAPost();
    $post->update(['published_at' => $time]);

    $this->callDataApi('/posts', [
        'filter' => "published_at=yesterday",
    ])->assertJsonPath('data.0.published_at', $time->timestamp);
});

it('filters by created_at', function () {
    $time = new Carbon('-7 days');
    $timeString = $time->toDateTimeString();

    $post = getAPost();
    $post->update(['created_at' => $time]);

    $this->callDataApi('/posts', [
        'filter' => "created_at='$timeString'",
    ])->assertJsonPath('data.0.created_at', $time->timestamp);
});

it('filters by updated_at', function () {
    $time = new Carbon('-14 days');
    $timeString = $time->toDateTimeString();

    $post = getAPost();
    $post->variants->map(fn ($v) => $v->update(['updated_at' => $timeString]));

    $this->callDataApi('/posts', [
        'filter' => "updated_at='$timeString'",
    ])->assertJsonPath('data.0.updated_at', $time->timestamp);
});

it('filters_by_is_featured', function () {
    $post = getAPost();
    $post->update(['is_featured' => true]);

    $response = $this->callDataApi('/posts', [
        'filter' => "is_featured=true",
    ]);
    $response
        ->assertJsonPath('data.0.slug', $post->slug)
        ->assertJsonCount(1, 'data');
});

it('filters by slug', function () {
    $post = getAPost();
    $post->update(['slug' => 'some-new-slug']);

    $response = $this->callDataApi('/posts', [
        'filter' => "slug=$post->slug",
    ]);
    $response
        ->assertJsonPath('data.0.slug', $post->slug)
        ->assertJsonCount(1, 'data');
});

it('filters by featured image', function () {
    $post = getAPost();
    $post->update(['featured_image_url' => 'some-new-url']);

    $response = $this->callDataApi('/posts', [
        'filter' => "featured_image_url!=null",
    ]);
    $response->assertJsonPath('data.0.id', $post->id);
});

it('filters by canonical url', function () {
    $response = $this->callDataApi('/posts', [
        'filter' => "canonical_url=null",
    ]);
    $response->assertJsonPath('data.0.canonical_url', null);
});

it('filters by words', function () {

    // make sure there's at least one post with 20+ words
    $post = getAPost();
    $post->variants()->update(['words' => 21]);

    $response = $this->callDataApi('/posts', [
        'filter' => "words>20",
    ]);
    $response->assertJson(function ($json) {
        $json->has('data.0', function ($json) {
            $json->where('words', fn ($val) => $val > 20)
                ->etc();
        })->etc();
    });
});

it('filters by tag ID', function () {

    // ensure tag
    $blog = Blog::find(config('test.blog_id'));
    $tag = $blog->tags[0];
    $post = getAPost();
    PostTag::create(['post_id' => $post->id, 'tag_id' => $tag->id]);

    $response = $this->callDataApi('/posts', [
        'filter' => "tag.id=$tag->id",
    ]);
    $response->assertJsonPath('data.0.tags.0.id', $tag->id);
});

it('filters by tag slug', function () {

    // ensure tag
    $blog = Blog::find(config('test.blog_id'));
    $tag = $blog->tags[0];
    $post = getAPost();
    PostTag::where('post_id', $post->id)->delete();
    PostTag::create(['post_id' => $post->id, 'tag_id' => $tag->id]);

    $response = $this->callDataApi('/posts', [
        'filter' => "tag.slug='$tag->slug'",
    ]);
    $response->assertJsonPath('data.0.tags.0.slug', $tag->slug);
});

it('filters by author ID', function () {

    // ensure author
    $blog = Blog::find(config('test.blog_id'));
    $user = $blog->users[0];
    $post = getAPost();

    PostAuthor::where('post_id', $post->id)->delete();
    PostAuthor::create(['post_id' => $post->id, 'user_id' => $user->id]);

    $response = $this->callDataApi('/posts', [
        'filter' => "author.id=$user->id",
    ]);
    $response->assertJsonPath('data.0.authors.0.id', $user->id);
});

it('filters by author slug', function () {

    // ensure author
    $blog = Blog::find(config('test.blog_id'));
    $user = $blog->users[0];
    $post = getAPost();


    PostAuthor::where('post_id', $post->id)->delete();
    PostAuthor::create(['post_id' => $post->id, 'user_id' => $user->id]);

    $response = $this->callDataApi('/posts', [
        'filter' => "author.slug=$user->slug",
    ]);
    $response->assertJsonPath('data.0.authors.0.slug', $user->slug);
});

// helper
function getAPost()
{
    $post = Post::where('blog_id', config('test.blog_id'))->where('is_page', false)->first();
    // make sure to publish the variants
    $post->variants->map(fn ($v) => $v->update(['status' => 'published']));

    return $post;
}
