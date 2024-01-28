<?php

namespace Tests\Feature\DataApi;

use App\Data\Objects\DataAPI\AuthorObject;
use App\Models\Blog;
use App\Models\Language;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

function getAuthorObjectArray(User $user, Blog $blog, Language $language)
{
    return json_decode(json_encode(new AuthorObject($user, $blog, $language)), true);
}

beforeEach(function () {

    $this->blog = blog();
    $this->lang1 = addPrimaryLanguage($this->blog);
    $this->lang2 = addLanguage($this->blog);

    addDefaultRoutes($this->blog);

    $this->author = addUsers(
        $this->blog,
        1,
        fn() => ['posts_count' => 2]
    )[0];

    $this->authorEn = getAuthorObjectArray($this->author, $this->blog, $this->lang1);
    $this->authorFr = getAuthorObjectArray($this->author, $this->blog, $this->lang2);

});

it('fetches an author by id', function () {
    dataApi($this->blog, '/author', [
            'id' => $this->author->id,
        ])
        ->assertOk()
        ->assertExactJson($this->authorEn);
});

it('fetches a author by slug', function () {
    dataApi($this->blog, '/author', [
            'slug' => $this->author->slug,
        ])
        ->assertOk()
        ->assertExactJson($this->authorEn);
});
it('does not fetch user when posts count is zero', function () {
    $this->author->update(['posts_count' => 0]);

    dataApi($this->blog, '/author', [
            'id' => $this->author->id,
        ])
        ->assertUnprocessable();
});

it('requires validates ID', function () {
    dataApi($this->blog, '/author', [
            'id' => 'oh, hi!',
        ])
        ->assertUnprocessable();
});

it('requires validates slug', function () {
    dataApi($this->blog,'/author', [
            'slug' => true,
        ])
        ->assertUnprocessable();
});

it('fetches tag by id and language', function () {
    dataApi($this->blog, '/author', [
            'id' => $this->author->id,
            'language' => $this->blog->languages[1]->code,
        ])
        ->assertOk()
        ->assertExactJson($this->authorFr);
});

it('requires a valid language (type)', function () {
    dataApi($this->blog, '/author', [
            'id' => $this->author->id,
            'language' => true,
        ])
        ->assertUnprocessable();
});

it('returns 404 if tag is not found', function () {
    dataApi($this->blog, '/author', [
            'id' => $this->author->id + 1,
        ])
        ->assertNotFound();
});

it('filters keys', function () {
    dataApi($this->blog, '/author', [
            'id' => $this->author->id,
            'keys' => 'id',
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('id')
                ->missing('slug');
        });
});
