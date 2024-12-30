<?php

namespace Tests\Feature\DataAPI;

use App\Models\Blog;
use App\Models\Tag;
use App\Models\TagVariant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $blog = blog();

    addDefaultRoutes($blog);

    addPrimaryLanguage($blog);
    addLanguage($blog);

    $tags = addTags($blog, 4);

    $this->blog = $blog;
    $this->tags = $tags;
    $this->tag = $tags[0];
});

it('fetches tags without params', function () {
    dataApi($this->blog, '/tags')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->has(
                    'data',
                    4,
                    fn (AssertableJson $json) => $json->where('language.code', $this->blog->languages[0]->code)
                    ->etc()
                )
                ->has('pagination');
        });
});

it('works with language', function () {
    dataApi($this->blog, '/tags', [
            'language' => $this->blog->languages[1]->code,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->has(
                    'data',
                    4,
                    fn (AssertableJson $json) => $json->where('language.code', $this->blog->languages[1]->code)
                        ->etc()
                )
                ->has('pagination');
        });
});

it('does not work with wrong language', function () {
    dataApi($this->blog, '/tags', [
        'language' => 'jp',
    ])->assertUnprocessable();
});

it('gives correct limit', function () {
    dataApi($this->blog, '/tags', [
            'limit' => 3,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('data', 3)
                ->etc();
        });
});

it('gives correct page for pagination', function () {

    // default order is posts_count DESC

    $tags = Tag::where('blog_id', $this->blog->id)->limit(3)->get();

    $tags[0]->update(['posts_count' => 101]);
    $tags[1]->update(['posts_count' => 100]);

    dataApi($this->blog, '/tags', [
            'limit' => 1,
            'page' => 2,
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($tags) {
            $json->has('data', 1)
                ->where('data.0.id', $tags[1]->id)
                ->etc();
        });
});

it('does not work for invalid limit', function () {
    dataApi($this->blog, '/tags', [
        'limit' => 0,
    ])->assertUnprocessable();
});

it('does not work for invalid page', function () {
    dataApi($this->blog, '/tags', [
        'page' => -1,
    ])->assertUnprocessable();
});

it('does not work for invalid sort', function () {
    dataApi($this->blog, '/tags', [
        'sort' => 'something_invalid',
    ])->assertUnprocessable();
});

it('does not work for invalid sort method', function () {
    dataApi($this->blog, '/tags', [
        'sort' => 'published_at SOME',
    ])->assertUnprocessable();
});

it('sorts by posts_count DESC correctly', function () {
    $response = dataApi($this->blog, '/tags', [
        'sort' => 'posts_count',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['posts_count'] >= $response['data'][1]['posts_count'] &&
        $response['data'][1]['posts_count'] >= $response['data'][2]['posts_count']
    );
});

it('sorts by posts_count ASC correctly', function () {
    $response = dataApi($this->blog, '/tags', [
        'sort' => 'posts_count ASC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['posts_count'] <= $response['data'][1]['posts_count'] &&
        $response['data'][1]['posts_count'] <= $response['data'][2]['posts_count']
    );
});

it('sorts by created_at DESC correctly', function () {
    $response = dataApi($this->blog, '/tags', [
        'sort' => 'created_at',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['created_at'] >= $response['data'][1]['created_at'] &&
        $response['data'][1]['created_at'] >= $response['data'][2]['created_at']
    );
});

it('sorts by created_at ASC correctly', function () {
    $response = dataApi($this->blog, '/tags', [
        'sort' => 'created_at ASC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['created_at'] <= $response['data'][1]['created_at'] &&
        $response['data'][1]['created_at'] <= $response['data'][2]['created_at']
    );
});

it('filters keys', function () {
    $response = dataApi($this->blog, '/tags', [
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

it('filters by id', function () {
    dataApi($this->blog, '/tags', [
            'filter' => "id={$this->tag->id}",
        ])
        ->assertJsonPath('data.0.id', $this->tag->id)
        ->assertJsonCount(1, 'data');
});

it('filters by slug', function () {
    dataApi($this->blog, '/tags', [
            'filter' => "slug='{$this->tag->slug}'",
        ])
        ->assertJsonPath('data.0.slug', $this->tag->slug)
        ->assertJsonCount(1, 'data');
});

it('filters by posts_count', function () {
    $this->tags->random(3)->each(function ($tag) {
        $tag->update(['posts_count' => rand(101, 200)]);
    });

    dataApi($this->blog, '/tags', [
            'filter' => 'posts_count>100',
        ])
        ->assertJsonCount(3, 'data');
});

it('filters by created_at', function () {
    $time = new Carbon('tomorrow');
    $this->tag->update(['created_at' => $time]);

    dataApi($this->blog, '/tags', [
        'filter' => 'created_at=tomorrow',
    ])->assertJsonPath('data.0.created_at', $time->timestamp);
});

it('sends total correctly', function () {
    $count = Tag::where('blog_id', $this->blog->id)->count();

    dataApi($this->blog, '/tags')
        ->assertJsonPath('pagination.total', $count);
});

it('visibility test', function() {
    
    $tag = addTag($this->blog, [
        'is_private' => true,
    ]);

    // public only
    dataApi($this->blog, '/tags')
        ->assertOk()
        ->assertJsonCount(4, 'data')
        ->assertJsonPath('data.0.is_private', false);

    dataApi($this->blog, '/tags', [
        'visibility' => 'private',
    ])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.is_private', true)
        ->assertJsonPath('data.0.id', $tag->id);


    dataApi($this->blog, '/tags', ['visibility' => 'any'])
        ->assertOk()
        ->assertJsonCount(5, 'data');
    
});