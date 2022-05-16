<?php

namespace Tests\Feature\DataApi;

use App\Data\Objects\DataAPI\AuthorObject;
use App\Models\Blog;
use App\Models\Language;
use App\Models\User;
use App\Models\UserVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Testing\Fluent\AssertableJson;

function getAuthorObjectArray(User $user, Blog $blog, Language $language)
{
    return json_decode(json_encode(new AuthorObject($user, $blog, $language)), true);
}

beforeEach(function () {
    $user = User::factory()
        ->has(
            UserVariant::factory()
                ->count(2)
                ->state(new Sequence(
                    ['language_id' => $this->blog->languages[0]],
                    ['language_id' => $this->blog->languages[1]]
                )),
            'variants'
        )->create([
            'blog_id' => $this->blog,
            'hyvor_user_id' => rand(100, 200),
            'posts_count' => 2,
        ]);

    $this->author = User::find($user->id);

    $this->authorEn = getAuthorObjectArray($user, $this->blog, $this->blog->languages[0]);
    $this->authorFr = getAuthorObjectArray($user, $this->blog, $this->blog->languages[1]);
});

it('fetches an author by id', function () {
    $this
        ->callDataApi('/author', [
            'id' => $this->author->id,
        ])
        ->assertOk()
        ->assertExactJson($this->authorEn);
});

it('fetches a author by slug', function () {
    $this
        ->callDataApi('/author', [
            'slug' => $this->author->slug,
        ])
        ->assertOk()
        ->assertExactJson($this->authorEn);
});
it('does not fetch user when posts count is zero', function () {
    $this->author->update(['posts_count' => 0]);

    $this
        ->callDataApi('/author', [
            'id' => $this->author->id,
        ])
        ->assertUnprocessable();
});

it('requires validates ID', function () {
    $this
        ->callDataApi('/author', [
            'id' => 'oh, hi!',
        ])
        ->assertUnprocessable();
});

it('requires validates slug', function () {
    $this
        ->callDataApi('/author', [
            'slug' => true,
        ])
        ->assertUnprocessable();
});

it('fetches tag by id and language', function () {
    $this
        ->callDataApi('/author', [
            'id' => $this->author->id,
            'language' => $this->blog->languages[1]->code,
        ])
        ->assertOk()
        ->assertExactJson($this->authorFr);
});

it('requires a valid language (type)', function () {
    $this
        ->callDataApi('/author', [
            'id' => $this->author->id,
            'language' => true,
        ])
        ->assertUnprocessable();
});

it('returns 404 if tag is not found', function () {
    $this
        ->callDataApi('/author', [
            'id' => $this->author->id + 1,
        ])
        ->assertNotFound();
});

it('filters keys', function () {
    $this
        ->callDataApi('/author', [
            'id' => $this->author->id,
            'keys' => 'id',
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('id')
                ->missing('slug');
        });
});
