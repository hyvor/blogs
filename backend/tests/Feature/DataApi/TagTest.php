<?php

namespace Tests\Feature\DataAPI;

use App\Data\Objects\DataAPI\TagObject;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Tag;
use App\Models\TagVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Testing\Fluent\AssertableJson;

function getTagObjectArray(Tag $tag, Blog $blog, Language $language)
{
    return json_decode(json_encode(new TagObject($tag, $blog, $language)), true);
}

beforeEach(function () {
    $this->blog = blog();

    addPrimaryLanguage($this->blog);
    addLanguage($this->blog);
    addDefaultRoutes($this->blog);

    $tag = addTag($this->blog);

    $this->tag = Tag::find($tag->id);

    $this->authorEn = getTagObjectArray($tag, $this->blog, $this->blog->languages[0]);
    $this->authorFr = getTagObjectArray($tag, $this->blog, $this->blog->languages[1]);
});

it('fetches a tag by id', function () {
    dataApi($this->blog, '/tag', [
            'id' => $this->tag->id,
        ])
        ->assertOk()
        ->assertExactJson($this->authorEn);
});

it('fetches a tag by slug', function () {
    dataApi($this->blog, '/tag', [
            'slug' => $this->tag->slug,
        ])
        ->assertOk()
        ->assertExactJson($this->authorEn);
});

it('requires validates ID', function () {
    dataApi($this->blog, '/tag', [
            'id' => 'oh, hi!',
        ])
        ->assertUnprocessable();
});

it('requires validates slug', function () {
    dataApi($this->blog, '/tag', [
            'slug' => true,
        ])
        ->assertUnprocessable();
});

it('fetches tag by id and language', function () {
    dataApi($this->blog, '/tag', [
            'id' => $this->tag->id,
            'language' => $this->blog->languages[1]->code,
        ])
        ->assertOk()
        ->assertExactJson($this->authorFr);
});

it('requires a valid language (type)', function () {
    dataApi($this->blog, '/tag', [
            'id' => $this->tag->id,
            'language' => true,
        ])
        ->assertUnprocessable();
});

it('returns 404 if tag is not found', function () {
    dataApi($this->blog, '/tag', [
            'id' => $this->tag->id + 1,
        ])
        ->assertNotFound();
});

it('filters keys', function () {
    dataApi($this->blog, '/tag', [
            'id' => $this->tag->id,
            'keys' => 'id',
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('id')
                ->missing('slug');
        });
});
