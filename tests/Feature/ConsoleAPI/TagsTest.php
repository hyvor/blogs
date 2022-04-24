<?php

namespace Tests\Feature\ConsoleAPI;

use App\Models\Language;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Tag;

it('fetches tags', function() {
   
    $this
        ->callConsoleApi('GET', 'tags', [
           'limit' => 5
        ])
        ->assertStatus(200)
        ->assertJson(function (AssertableJson $json) {
            $json->count(5)
                ->has('0', function (AssertableJson $json) {
                    $json->has('id')
                        ->has('slug')
                        ->etc();
                });
        });
});

it('fetches tags with offset', function() {
    
    $tagsCount = Tag::where('blog_id', config('test.blog_id'))->count();
    $this
        ->callConsoleApi('GET', 'tags', [
            'limit' => $tagsCount,
            'offset' => $tagsCount - 1
        ])
        ->assertStatus(200)
        ->assertJson(function (AssertableJson $json) {
            $json->count(1);
        });
    
});
    

it('creates a tag success', function() {

    $data = 'test';
    $language = Language::where('blog_id', config('test.blog_id'))
        ->where('is_primary', true)
        ->first();
    
    $this
        ->callConsoleApi('POST', 'tag', [
            'name' => $data,
            'slug' => $data,
            'description' => $data
        ])
        ->assertStatus(200)
        ->assertJson(function (AssertableJson $json) use ($data, $language) {
            $json->has('id')
                ->where('slug', $data)
                ->has("variants.{$language->id}", function (AssertableJson $json) use ($data) {
                    $json->where('name', $data)
                        ->where('description', $data)
                        ->etc();
                })
                ->etc();
        });
    
});

it('creating tag fails on empty name', function() {

    $this
        ->callConsoleApi('POST', 'tag')
        ->assertStatus(400);    
});

it('creating tag with null slug works', function() {

    $this
        ->callConsoleApi('POST', 'tag', [
            'name' => 'May Day'
        ])
        ->assertStatus(200)
        ->assertJson(function (AssertableJson $json) {
            $json->where('slug', 'may-day')
                ->etc();
        });
    
});

it('creating tag with existing slug fails', function() {

    $tag = Tag::where('blog_id', config('test.blog_id'))->first();
    
    $this
        ->callConsoleApi('POST', 'tag', [
            'name' => 'Name',
            'slug' => $tag->slug
        ])
        ->assertStatus(500);
});

it('update a tag success', function() {

    $data = 'test';
    $language = Language::where('blog_id', config('test.blog_id'))
        ->where('is_primary', true)
        ->first();

    $this
        ->callConsoleApi('PUT', 'tag/1', [
            'name' => $data,
            'slug' => $data,
            'description' => $data,
            'languageId'=>$language->id,
            'codeHead' => null,
            'codeFoot' => null,
        ])
        ->assertStatus(200);
});
