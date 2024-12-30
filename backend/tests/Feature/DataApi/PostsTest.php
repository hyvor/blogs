<?php

namespace Tests\Feature\DataAPI;

use App\Models\Blog;
use App\Models\HyvorTalkGatedContentRule;
use App\Models\HyvorTalkWebsite;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\PostVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {

    $blog = blog();

    $this->primaryLanguage = addPrimaryLanguage($blog);
    $this->secondaryLanguage = addLanguage($blog);

    addDefaultRoutes($blog);

    $this->posts = addPosts($blog, 4, [], ['status' => 'published']);
    $this->pages = addPosts($blog, 3, ['is_page' => true], ['status' => 'published']);

    $this->blog = $blog;

});

it('fetches posts without params', function () {
    dataApi($this->blog, '/posts')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 4)
                ->has('data')
                ->has('data.0', fn (AssertableJson $json) =>
                    $json->where('language.code', $this->primaryLanguage->code)->etc())
                ->has('pagination');
        });
});

it('fetches pages', function () {
    dataApi($this->blog, '/posts', [
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
    dataApi($this->blog, '/posts', [
            'language' => $this->secondaryLanguage->code,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('data')
                ->has('data.0', fn (AssertableJson $json) =>
                    $json->where('language.code', $this->secondaryLanguage->code)->etc())
                ->has('pagination');
        });
});

it('does not work with wrong language', function () {
    dataApi($this->blog, '/posts', [
        'language' => 'jp',
    ])->assertUnprocessable();
});

it('gives correct limit', function () {
    dataApi($this->blog, '/posts', [
            'limit' => 3,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('data', 3)
                ->etc();
        });
});

it('gives correct page for pagination', function () {

    $response = dataApi($this->blog, '/posts', [
        'limit' => 2,
        'page' => 2,
        'sort' => 'id ASC',
    ]);

    $response->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('data.0', function (AssertableJson $json) {
                $json->where('id', $this->posts[2]->id)
                    ->etc();
            })
                ->etc();
        });
});

it('does not work for invalid limit', function () {
    dataApi($this->blog, '/posts', [
        'limit' => 0,
    ])->assertUnprocessable();
});

it('does not work for invalid page', function () {
    dataApi($this->blog, '/posts', [
        'page' => -1,
    ])->assertUnprocessable();
});

it('does not work for invalid sort', function () {
    dataApi($this->blog, '/posts', [
        'sort' => 'something_invalid',
    ])->assertUnprocessable();
});

it('does not work for invalid sort method', function () {
    dataApi($this->blog, '/posts', [
        'sort' => 'published_at SOME',
    ])->assertUnprocessable();
});

it('sorts by published_at DESC correctly', function () {
    $response = dataApi($this->blog, '/posts', [
        'sort' => 'published_at',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['published_at'] >= $response['data'][1]['published_at'] &&
        $response['data'][1]['published_at'] >= $response['data'][2]['published_at']
    );
});

it('sorts by published_at ASC correctly', function () {
    $response = dataApi($this->blog, '/posts', [
        'sort' => 'published_at ASC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['published_at'] <= $response['data'][1]['published_at'] &&
        $response['data'][1]['published_at'] <= $response['data'][2]['published_at']
    );
});

it('sorts by created_at DESC correctly', function () {
    $response = dataApi($this->blog, '/posts', [
        'sort' => 'created_at',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['created_at'] >= $response['data'][1]['created_at'] &&
        $response['data'][1]['created_at'] >= $response['data'][2]['created_at']
    );
});

it('sorts by updated_at ASC correctly', function () {
    $response = dataApi($this->blog, '/posts', [
        'sort' => 'updated_at ASC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['updated_at'] <= $response['data'][1]['updated_at'] &&
        $response['data'][1]['updated_at'] <= $response['data'][2]['updated_at']
    );
});

it('sorts by id DESC correctly', function () {
    $response = dataApi($this->blog, '/posts', [
        'sort' => 'id',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['id'] >= $response['data'][1]['id'] &&
        $response['data'][1]['id'] >= $response['data'][2]['id']
    );
});

it('sorts by is_featured DESC correctly', function () {

    $post = $this->posts->random();
    $post->is_featured = true;
    $post->save();

    $response = dataApi($this->blog, '/posts', [
        'sort' => 'is_featured DESC',
        'limit' => 3,
    ])->json();

    $this->assertTrue($response['data'][0]['is_featured'] && $response['data'][0]['id'] === $post->id);
});

it('sorts by title DESC correctly', function () {
    $response = dataApi($this->blog, '/posts', [
        'sort' => 'title DESC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['title'] >= $response['data'][1]['title'] &&
        $response['data'][1]['title'] >= $response['data'][2]['title']
    );
});

it('sorts by words DESC correctly', function () {
    $response = dataApi($this->blog, '/posts', [
        'sort' => 'words DESC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['words'] >= $response['data'][1]['words'] &&
        $response['data'][1]['words'] >= $response['data'][2]['words']
    );
});

it('correctly filters by defined keys', function () {
    $response = dataApi($this->blog, '/posts', [
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
    $post = $this->posts->random();

    dataApi($this->blog, '/posts', [
            'filter' => "id=$post->id",
        ])
        ->assertJsonPath('data.0.id', $post->id)
        ->assertJsonCount(1, 'data');
});

it('filters by published_at', function () {
    $time = new Carbon('yesterday');

    $post = $this->posts->random();
    $post->update(['published_at' => $time]);

    dataApi($this->blog, '/posts', [
        'filter' => 'published_at=yesterday',
    ])->assertJsonPath('data.0.published_at', $time->timestamp);
});

it('filters by created_at', function () {
    $time = new Carbon('-7 days');
    $timeString = $time->toDateTimeString();

    $post = $this->posts->random();
    $post->update(['created_at' => $time]);

    dataApi($this->blog, '/posts', [
        'filter' => "created_at='$timeString'",
    ])->assertJsonPath('data.0.created_at', $time->timestamp);
});

it('filters by updated_at', function () {
    $time = new Carbon('-14 days');
    $timeString = $time->toDateTimeString();

    $post = $this->posts->random();
    $post->variants->map(function ($v) use ($timeString) {
        $v->updated_at = $timeString;
        $v->timestamps = false;
        $v->save();
    });

    dataApi($this->blog, '/posts', [
        'filter' => "updated_at='$timeString'",
    ])->assertJsonPath('data.0.updated_at', $time->timestamp);
});

it('filters_by_is_featured', function () {
    $post = $this->posts->random();
    $post->update(['is_featured' => true]);

    $response = dataApi($this->blog, '/posts', [
        'filter' => 'is_featured=true',
    ]);
    $response
        ->assertJsonPath('data.0.slug', $post->variants[0]->slug)
        ->assertJsonCount(1, 'data');
});

it('filters by slug', function () {
    $post = $this->posts->random();
    $post->variants[0]->update(['slug' => 'some-new-slug']);

    $response = dataApi($this->blog, '/posts', [
        'filter' => "slug=some-new-slug",
    ]);
    $response
        ->assertJsonPath('data.0.slug', $post->variants[0]->slug)
        ->assertJsonCount(1, 'data');
});

it('filters by featured image', function () {
    $post = $this->posts->random();
    $post->update(['featured_image_url' => 'some-new-url']);

    $response = dataApi($this->blog, '/posts', [
        'filter' => 'featured_image_url!=null',
    ]);
    $response->assertJsonPath('data.0.id', $post->id);
});

it('filters by canonical url', function () {
    $response = dataApi($this->blog, '/posts', [
        'filter' => 'canonical_url=null',
    ]);
    $response->assertJsonPath('data.0.canonical_url', null);
});

it('filters by words', function () {

    // make sure there's at least one post with 20+ words
    $post = $this->posts->random();
    $post->variants()->update(['words' => 21]);

    $response = dataApi($this->blog, '/posts', [
        'filter' => 'words>20',
    ]);
    $response->assertJson(function ($json) {
        $json->has('data.0', function ($json) {
            $json->where('words', fn ($val) => $val > 20)
                ->etc();
        })->etc();
    });
});

it('filters by tag ID', function () {

    $tag = addTag($this->blog);
    $post = $this->posts->random();
    addTagToPost($post, $tag);

    $response = dataApi($this->blog, '/posts', [
        'filter' => "tag.id=$tag->id",
    ]);

    $response->assertJsonPath('data.0.tags.0.id', $tag->id);
});

it('filters by tag slug', function () {

    $tag = addTag($this->blog);
    $post = $this->posts->random();
    addTagToPost($post, $tag);

    $response = dataApi($this->blog, '/posts', [
        'filter' => "tag.slug='$tag->slug'",
    ]);
    $response->assertJsonPath('data.0.tags.0.slug', $tag->slug);
});

it('filters by author ID', function () {

    $post = $this->posts->random();
    $user = addUser($this->blog);
    addAuthorToPost($post, $user);

    $response = dataApi($this->blog, '/posts', [
        'filter' => "author.id=$user->id",
    ]);
    $response->assertJsonPath('data.0.authors.0.id', $user->id);

});

it('filters by author slug', function () {

    $post = $this->posts->random();
    $user = addUser($this->blog);
    addAuthorToPost($post, $user);

    $response = dataApi($this->blog, '/posts', [
        'filter' => "author.slug='$user->slug'",
    ]);
    $response->assertJsonPath('data.0.authors.0.slug', $user->slug);
});

it('fetches with ht gated content', function() {

    HyvorTalkWebsite::create([
        'blog_id' => $this->blog->id,
        'website_id' => 10,
        'encryption_key' => 'my-key'
    ]);

    $tag = addTag($this->blog);
    HyvorTalkGatedContentRule::factory()->create([
        'blog_id' => $this->blog->id,
        'tag_id' => $tag->id,
    ]);
    addTagToPost($this->posts[0], $tag);
    $this->posts[1]->variants[0]->update(['content_html' => 'my-content']);

    DB::enableQueryLog();

    $response = dataApi($this->blog, '/posts', [
        'sort' => 'id ASC',
        ])
        ->assertOk()
        ->json();

    $posts = $response['data'];

    expect($posts[0]['id'])->toBe($this->posts[0]->id);
    expect($posts[0]['content'])->toContain('<hyvor-talk-gated-content');

    expect($posts[1]['id'])->toBe($this->posts[1]->id);
    expect($posts[1]['content'])->toBe('my-content');

    $queries = collect(DB::getQueryLog());

    // make sure fetching gated content does not cause n+1 queries
    expect($queries->where(fn ($q) => str_contains($q['query'], 'hyvor_talk_gated_content_rules'))->count())->toBe(1);
    expect($queries->where(fn ($q) => str_contains($q['query'], 'inter_hyvor_talk_websites'))->count())->toBe(1);

});
