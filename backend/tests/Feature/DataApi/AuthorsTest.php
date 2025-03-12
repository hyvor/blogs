<?php

namespace Tests\Feature\DataAPI;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->blog = blog();
    $this->lang1 = addPrimaryLanguage($this->blog);
    $this->lang2 = addLanguage($this->blog);
    addDefaultRoutes($this->blog);
});

it('fetches authors without params', function () {
    addUsers(
        $this->blog,
        4,
        fn() => ['posts_count' => rand(1, 100)]
    );

    dataApi($this->blog, '/authors')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->has(
                    'data',
                    4,
                    fn(AssertableJson $json) => $json->where('language.code', $this->lang1->code)
                        ->etc()
                )
                ->has('pagination');
        });
});

it('works with language', function () {
    addUsers(
        $this->blog,
        2,
        fn() => ['posts_count' => rand(1, 100)]
    );

    dataApi($this->blog, '/authors', [
        'language' => $this->lang2->code,
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->has(
                    'data',
                    2,
                    fn(AssertableJson $json) => $json->where('language.code', $this->lang2->code)
                        ->etc()
                )
                ->has('pagination');
        });
});

it('does not work with wrong language', function () {
    dataApi($this->blog, '/authors', [
        'language' => 'jp',
    ])->assertUnprocessable();
});

it('gives correct limit', function () {
    addUsers(
        $this->blog,
        2,
        fn() => ['posts_count' => rand(1, 100)]
    );

    dataApi($this->blog, '/authors', [
        'limit' => 1,
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('data', 1)
                ->etc();
        });
});

it('gives correct page for pagination', function () {
    $authors = addUsers(
        $this->blog,
        2,
        new Sequence(
            fn() => ['posts_count' => 101],
            fn() => ['posts_count' => 100],
        )
    );

    dataApi($this->blog, '/authors', [
        'limit' => 1,
        'page' => 2,
    ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($authors) {
            $json->has('data', 1)
                ->where('data.0.id', $authors[1]->id)
                ->etc();
        });
});

it('does not work for invalid limit', function () {
    dataApi($this->blog, '/authors', [
        'limit' => 0,
    ])->assertUnprocessable();
});

it('does not work for invalid page', function () {
    dataApi($this->blog, '/authors', [
        'page' => -1,
    ])->assertUnprocessable();
});

it('does not work for invalid sort', function () {
    dataApi($this->blog, '/authors', [
        'sort' => 'something_invalid',
    ])->assertUnprocessable();
});

it('does not work for invalid sort method', function () {
    dataApi($this->blog, '/authors', [
        'sort' => 'published_at SOME',
    ])->assertUnprocessable();
});

it('sorts by posts_count DESC correctly', function () {
    addUsers(
        $this->blog,
        3,
        fn() => ['posts_count' => rand(1, 100)]
    );

    $response = dataApi($this->blog, '/authors', [
        'sort' => 'posts_count',
        'limit' => 3,
    ])->assertOk()->json();

    $this->assertTrue(
        $response['data'][0]['posts_count'] >= $response['data'][1]['posts_count'] &&
        $response['data'][1]['posts_count'] >= $response['data'][2]['posts_count']
    );
});

it('sorts by posts_count ASC correctly', function () {
    addUsers(
        $this->blog,
        3,
        fn() => ['posts_count' => rand(1, 100)]
    );

    $response = dataApi($this->blog, '/authors', [
        'sort' => 'posts_count ASC',
        'limit' => 3,
    ])->assertOk()->json();

    $this->assertTrue(
        $response['data'][0]['posts_count'] <= $response['data'][1]['posts_count'] &&
        $response['data'][1]['posts_count'] <= $response['data'][2]['posts_count']
    );
});

it('sorts by created_at DESC correctly', function () {
    addUsers(
        $this->blog,
        3,
        fn() => ['posts_count' => rand(1, 100), 'created_at' => now()->subDays(rand(1, 100))],
    );

    $response = dataApi($this->blog, '/authors', [
        'sort' => 'created_at',
        'limit' => 3,
    ])->assertOk()->json();

    $this->assertTrue(
        $response['data'][0]['created_at'] >= $response['data'][1]['created_at'] &&
        $response['data'][1]['created_at'] >= $response['data'][2]['created_at']
    );
});

it('sorts by created_at ASC correctly', function () {
    addUsers(
        $this->blog,
        3,
        fn() => ['posts_count' => rand(1, 100), 'created_at' => now()->subDays(rand(1, 100))],
    );

    $response = dataApi($this->blog, '/authors', [
        'sort' => 'created_at ASC',
        'limit' => 3,
    ])->json();

    $this->assertTrue(
        $response['data'][0]['created_at'] <= $response['data'][1]['created_at'] &&
        $response['data'][1]['created_at'] <= $response['data'][2]['created_at']
    );
});

it('filters keys', function () {
    addUsers(
        $this->blog,
        3,
        fn() => ['posts_count' => rand(1, 100)],
    );

    $response = dataApi($this->blog, '/authors', [
        'keys' => 'id',
        'limit' => 3,
    ])->assertOk();

    $response->assertJson(function (AssertableJson $json) {
        $json->has('data', 3, function (AssertableJson $json) {
            $json->has('id')
                ->missing('slug');
        })->etc();
    });
});

it('filters by id', function () {
    $authors = addUsers(
        $this->blog,
        2,
        fn() => ['posts_count' => rand(1, 100)],
    );

    dataApi($this->blog, '/authors', [
        'filter' => "id={$authors[0]->id}",
    ])
        ->assertOk()
        ->assertJsonPath('data.0.id', $authors[0]->id)
        ->assertJsonCount(1, 'data');
});

it('filters by slug', function () {
    $authors = addUsers(
        $this->blog,
        2,
        fn() => ['posts_count' => rand(1, 100)],
    );

    dataApi($this->blog, '/authors', [
        'filter' => "slug='{$authors[0]->slug}'",
    ])
        ->assertOk()
        ->assertJsonPath('data.0.slug', $authors[0]->slug)
        ->assertJsonCount(1, 'data');
});

it('filters by posts_count', function () {
    addUsers(
        $this->blog,
        3,
        new Sequence(
            fn() => ['posts_count' => 100],
            fn() => ['posts_count' => 101],
            fn() => ['posts_count' => 102],
        ),
    );

    dataApi($this->blog, '/authors', [
        'filter' => 'posts_count>100',
    ])
        ->assertJsonCount(2, 'data');
});

it('filters by created_at', function () {
    $tomorrow = new Carbon('tomorrow');
    addUsers(
        $this->blog,
        2,
        new Sequence(
            ['created_at' => now(), 'posts_count' => 1],
            ['created_at' => $tomorrow, 'posts_count' => 1]
        )
    );

    dataApi($this->blog, '/authors', [
        'filter' => 'created_at=tomorrow',
    ])
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.created_at', $tomorrow->timestamp);
});

it('sends total correctly', function () {
    addUsers(
        $this->blog,
        3,
        fn() => ['posts_count' => rand(1, 100)],
    );

    dataApi($this->blog, '/authors')
        ->assertOk()
        ->assertJsonPath('pagination.total', 3);
});
