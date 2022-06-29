<?php

namespace Tests\Feature\DataAPI;

use App\Data\Objects\DataAPI\PostObject;
use App\Http\Controllers\DataApi\KeysFilter;
use App\Models\Post;

beforeEach(function () {
    $post = Post::first();
    $this->obj = new PostObject($post, $post->blog, $post->blog->languages[0]);
});

function j(array|object $obj): array
{
    return json_decode(json_encode($obj), true);
}

it('does not filter keys when keys param is null', function () {
    $obj = KeysFilter::filter($this->obj, null);
    $this->assertEquals(j($this->obj), j($obj));
});

it('filters basic key', function () {
    $arr = j(KeysFilter::filter($this->obj, 'id'));

    $this->assertArrayHasKey('id', $arr);
    $this->assertArrayNotHasKey('slug', $arr);
});

it('filters multiple keys', function () {
    $arr = j(KeysFilter::filter($this->obj, 'id, slug'));

    $this->assertArrayHasKey('id', $arr);
    $this->assertArrayHasKey('slug', $arr);
    $this->assertArrayNotHasKey('url', $arr);
});

it('filters nested object keys', function () {
    $arr = j(KeysFilter::filter($this->obj, 'tags.id'));
    $tag = $arr['tags'][0];

    $this->assertArrayHasKey('id', $tag);
    $this->assertArrayNotHasKey('slug', $tag);
});

it('filters nested object multi keys', function () {
    $arr = j(KeysFilter::filter($this->obj, 'tags.id, tags.slug'));
    $tag = $arr['tags'][0];

    $this->assertArrayHasKey('id', $tag);
    $this->assertArrayHasKey('slug', $tag);
    $this->assertArrayNotHasKey('url', $tag);
});

it('filters nested objects given the object name', function () {
    $arr = j(KeysFilter::filter($this->obj, 'tags'));

    $this->assertArrayHasKey('tags', $arr);

    $tag = $arr['tags'][0];

    $this->assertArrayHasKey('id', $tag);
    $this->assertArrayHasKey('slug', $tag);
});

it('filters keys with exclude', function () {
    $arr = j(KeysFilter::filter($this->obj, '!id'));
    $this->assertArrayNotHasKey('id', $arr);
    $this->assertArrayHasKey('slug', $arr);
});

it('filters keys with nested exclude', function () {
    $arr = j(KeysFilter::filter($this->obj, '!tags.id'));

    $tag = $arr['tags'][0];

    $this->assertArrayNotHasKey('id', $tag);
    $this->assertArrayHasKey('slug', $tag);
});
