<?php

namespace Tests\Feature\DataAPI;

use App\Data\Objects\DataAPI\TagObject;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Tag;
use App\Models\TagVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Testing\Fluent\AssertableJson;

function getTagObjectArray(Tag $tag, Blog $blog, Language $language) {
    return json_decode(json_encode(new TagObject($tag, $blog, $language)), true);
}

beforeEach(function() {

    $this->blog = Blog::find(config('test.blog_id'));
   
    $tag = Tag::factory()
        ->has(
            TagVariant::factory()
                ->count(2)
                ->state(new Sequence(
                    ['language_id' => $this->blog->languages[0]],
                    ['language_id' => $this->blog->languages[1]]
                )),
            'variants'
        )->create([
            'blog_id' => $this->blog
        ]);
    
    $this->tag = Tag::find($tag->id);
    
    $this->authorEn = getTagObjectArray($tag, $this->blog, $this->blog->languages[0]);
    $this->authorFr = getTagObjectArray($tag, $this->blog, $this->blog->languages[1]);
    
});

it('fetches a tag by id', function() {
   
    $this
        ->callDataApi('/tag', [
            'id' => $this->tag->id
        ])
        ->assertOk()
        ->assertExactJson($this->authorEn);
    
});

it('fetches a tag by slug', function() {

    $this
        ->callDataApi('/tag', [
            'slug' => $this->tag->slug
        ])
        ->assertOk()
        ->assertExactJson($this->authorEn);
    
});

it('requires validates ID', function() {

    $this
        ->callDataApi('/tag', [
            'id' => 'oh, hi!'
        ])
        ->assertUnprocessable();
    
});

it('requires validates slug', function() {

    $this
        ->callDataApi('/tag', [
            'slug' => true
        ])
        ->assertUnprocessable();

});

it('fetches tag by id and language', function() {

    $this
        ->callDataApi('/tag', [
            'id' => $this->tag->id,
            'language' => $this->blog->languages[1]->code
        ])
        ->assertOk()
        ->assertExactJson($this->authorFr);

});

it('requires a valid language (type)', function() {

    $this
        ->callDataApi('/tag', [
            'id' => $this->tag->id,
            'language' => true
        ])
        ->assertUnprocessable();
    
});

it('returns 404 if tag is not found', function() {

    $this
        ->callDataApi('/tag', [
            'id' => $this->tag->id + 1
        ])
        ->assertNotFound();
    
});

it('filters keys', function() {

    $this
        ->callDataApi('/tag', [
            'id' => $this->tag->id,
            'keys' => 'id'
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('id')
                ->missing('slug');
        });
    
});
