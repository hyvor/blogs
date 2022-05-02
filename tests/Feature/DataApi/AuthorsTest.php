<?php
namespace Tests\Feature\DataAPI;

use App\Models\User;
use App\Models\UserVariant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function() {


    $english = $this->blog->languages[0];
    $french = $this->blog->languages[1];

    $authors = User::factory()
        ->count(50)
        ->has(
            UserVariant::factory()
                ->count(2)
                ->state(new Sequence(
                    ['language_id' => $english],
                    ['language_id' => $french]
                ))
            ,
            'variants'
        )
        ->state(fn () => ['hyvor_user_id' => rand(100, 10000000)])
        ->create([
            'blog_id' => $this->blog,
            'posts_count' => rand(1, 100),
        ]);

    $this->authors = $authors;
    $this->author = $authors[0];

});


it('fetches tags without params', function() {
    $this
        ->callDataApi('/authors')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->has('data', 25, fn (AssertableJson $json) =>
                $json->where('language.code', 'en')
                    ->etc()
                )
                ->has('pagination');
        });
});

it('works with language', function() {

    $this
        ->callDataApi('/authors', [
            'language' => 'fr'
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->has('data', 25, fn (AssertableJson $json) =>
                $json->where('language.code', 'fr')
                    ->etc()
                )
                ->has('pagination');
        });

});


it('does not work with wrong language', function() {

    $this->callDataApi('/authors', [
        'language' => 'jp'
    ])->assertUnprocessable();

});


it('gives correct limit', function() {

    $this
        ->callDataApi('/authors', [
            'limit' => 3
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('data', 3)
                ->etc();
        });

});

it('gives correct page for pagination', function() {

    // default order is posts_count DESC

    $authors = User::where('blog_id', $this->blog->id)->limit(3)->get();

    $authors[0]->update(['posts_count' => 101]);
    $authors[1]->update(['posts_count' => 100]);

    $this
        ->callDataApi('/authors', [
            'limit' => 1,
            'page' => 2
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($authors) {
            $json->has('data', 1)
                ->where('data.0.id', $authors[1]->id)
                ->etc();
        });

});


it('does not work for invalid limit', function() {

    $this->callDataApi('/authors', [
        'limit' => 0,
    ])->assertUnprocessable();

});

it('does not work for invalid page', function() {

    $this->callDataApi('/authors', [
        'page' => -1,
    ])->assertUnprocessable();

});

it('does not work for invalid sort', function() {

    $this->callDataApi('/authors', [
        'sort' => 'something_invalid'
    ])->assertUnprocessable();

});

it('does not work for invalid sort method', function() {

    $this->callDataApi('/authors', [
        'sort' => 'published_at SOME'
    ])->assertUnprocessable();

});


it('sorts by posts_count DESC correctly', function() {

    $response = $this->callDataApi('/authors', [
        'sort' => 'posts_count',
        'limit' => 3
    ])->json();

    $this->assertTrue(
        $response['data'][0]['posts_count'] >= $response['data'][1]['posts_count'] &&
        $response['data'][1]['posts_count'] >= $response['data'][2]['posts_count']
    );

});


it('sorts by posts_count ASC correctly', function() {

    $response = $this->callDataApi('/authors', [
        'sort' => 'posts_count ASC',
        'limit' => 3
    ])->json();

    $this->assertTrue(
        $response['data'][0]['posts_count'] <= $response['data'][1]['posts_count'] &&
        $response['data'][1]['posts_count'] <= $response['data'][2]['posts_count']
    );

});



it('sorts by created_at DESC correctly', function() {

    $response = $this->callDataApi('/authors', [
        'sort' => 'created_at',
        'limit' => 3
    ])->json();

    $this->assertTrue(
        $response['data'][0]['created_at'] >= $response['data'][1]['created_at'] &&
        $response['data'][1]['created_at'] >= $response['data'][2]['created_at']
    );

});


it('sorts by created_at ASC correctly', function() {

    $response = $this->callDataApi('/authors', [
        'sort' => 'created_at ASC',
        'limit' => 3
    ])->json();

    $this->assertTrue(
        $response['data'][0]['created_at'] <= $response['data'][1]['created_at'] &&
        $response['data'][1]['created_at'] <= $response['data'][2]['created_at']
    );

});


it('filters keys', function() {

    $response = $this->callDataApi('/authors', [
        'keys' => 'id',
        'limit' => 3
    ]);

    $response->assertJson(function (AssertableJson $json) {
        $json->has('data', 3, function (AssertableJson $json) {
            $json->has('id')
                ->missing('slug');
        })->etc();
    });

});

it('filters by id', function() {

    $this
        ->callDataApi('/authors', [
            'filter' => "id={$this->author->id}"
        ])
        ->assertJsonPath('data.0.id', $this->author->id)
        ->assertJsonCount(1, 'data');

});

it('filters by slug', function() {

    $this
        ->callDataApi('/authors', [
            'filter' => "slug={$this->author->slug}"
        ])
        ->assertJsonPath('data.0.slug', $this->author->slug)
        ->assertJsonCount(1, 'data');

});

it('filters by posts_count', function() {

    $this->authors->random(3)->each(function ($tag) {
        $tag->update(['posts_count' => rand(100,200)]);
    });

    $this
        ->callDataApi('/authors', [
            'filter' => "posts_count>100"
        ])
        ->assertJsonCount(3, 'data');

});

it('filters by created_at', function() {

    $time = new Carbon('tomorrow');
    $this->author->update(['created_at' => $time]);

    $this->callDataApi('/authors', [
        'filter' => "created_at=tomorrow"
    ])->assertJsonPath('data.0.created_at', $time->timestamp);

});

it('sends total correctly', function() {

    $count = User::where('blog_id', $this->blog->id)
        ->where('posts_count', '>', 0)
        ->count();

    $this->callDataApi('/authors')
        ->assertJsonPath('pagination.total', $count);

});
