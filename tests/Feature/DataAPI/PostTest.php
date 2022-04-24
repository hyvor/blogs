<?php
namespace Tests\Feature\DataAPI;

use App\Data\Objects\DataAPI\PostObject;
use App\Models\Blog;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Testing\Fluent\AssertableJson;

function getPostObject(Post $post, Blog $blog, Language $language) {
    return json_decode(json_encode(new PostObject($post, $blog, $language)), true);
}

beforeEach(function() {

    $this->blog = Blog::find(config('test.blog_id'));

    $post = Post::factory()
        ->has(
            PostVariant::factory()
                ->count(2)
                ->state(new Sequence(
                    ['language_id' => $this->blog->languages[0]],
                    ['language_id' => $this->blog->languages[1]],
                ))
                ->state(function() {
                    return ['status' => 'published'];
                })
            ,
            'variants'
        )
        ->create([
            'blog_id' => $this->blog,
        ]);

    // re-fetch with relationships
    $this->post = Post::find($post->id);

    $this->postObject = getPostObject($this->post, $this->blog, $this->blog->languages[0]);
    $this->postObject2 = getPostObject($this->post, $this->blog, $this->blog->languages[1]);
    
});

it('works with post id', function() {

    $this
        ->callDataApi('/post', [
            'id' => $this->post->id
        ])
        ->assertOk()
        ->assertExactJson($this->postObject);
    
});

it('requires an integer id', function() {
   
    $this
        ->callDataApi('/post', [
            'id' => 'something'
        ])
        ->assertUnprocessable();
    
});

it('works with post slug', function() {
    
    $this
        ->callDataApi('/post', [
            'slug' => $this->post->slug
        ])
        ->assertOk()
        ->assertExactJson($this->postObject);
    
});

it('works with id and lang', function() {

    $response = $this
        ->callDataApi('/post', [
            'id' => $this->post->id,
            'language' => $this->blog->languages[1]->code
        ])
        ->assertOk()
        ->assertExactJson($this->postObject2);
    
});

it('does not work with invalid language', function() {

    $this
        ->callDataApi('/post', [
            'id' => $this->post->id,
            'language' => 'jp'
        ])->assertUnprocessable();
    
});

it('returns 404 for missing posts', function() {

    $this
        ->callDataApi('/post', [
            'id' => $this->post->id + 1,
        ])
        ->assertNotFound();
    
});

it('returns 404 for missing variant', function() {

    // delete variant
    PostVariant::where('post_id', $this->post->id)
        ->where('language_id', $this->blog->languages[1]->id)
        ->delete();

    $this
        ->callDataApi('/post', [
            'id' => $this->post->id,
            'language' => $this->blog->languages[1]->code
        ])
        ->assertNotFound();
    
});

it('do not return unpublished posts', function() {

    PostVariant::where('post_id', $this->post->id)
        ->where('language_id', $this->blog->languages[0]->id)
        ->update(['status' => 'draft']);

    $this
        ->callDataApi('/post', [
            'id' => $this->post->id,
        ])
        ->assertUnprocessable();
    
});

it('do not return posts when blog id is wrong', function() {

    $this
        ->callDataApi('/post', [
            'id' => $this->post->id,
        ], Blog::find(2)->subdomain)
        ->assertNotFound();
    
});

it('filters keys', function() {

    $this
        ->callDataApi('/post', [
            'id' => $this->post->id,
            'keys' => 'id,slug'
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('id')
                ->has('slug')
                ->missing('url');
        });
    
});
