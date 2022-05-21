<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;

beforeEach(function() {
    $this->post = Post::where('blog_id', config('test.blog_id'))->first();
});

it('validates', function() {
    $this->callConsoleApi('PATCH', "/post/{$this->post->id}/tags", [
        'ids' => ['some', 'text']
    ])
        ->assertUnprocessable()
        ->assertSee(['ids.0', 'must', 'integer']);
});

it('changes tags', function() {

    PostTag::where('post_id', $this->post->id)->delete();
    $tags = Tag::where('blog_id', config('test.blog_id'))->get();

    $this
        ->callConsoleApi('PATCH', "/post/{$this->post->id}/tags", [
            'ids' => $tags->map(fn($user) => $user->id)->toArray()
        ])
        ->assertOk();

    expect(PostTag::where('post_id', $this->post->id)->count())->toBe(count($tags));

});

it('removes all tags', function() {

    $this
        ->callConsoleApi('PATCH', "/post/{$this->post->id}/tags", [
            'ids' => []
        ])
        ->assertOk();

    expect(PostTag::where('post_id', $this->post->id)->count())->toBe(0);

});

it('does not add users from other blogs', function() {

    $user = Tag::where('blog_id', config('test.not_blog_id'))->first();

    $this
        ->callConsoleApi('PATCH', "/post/{$this->post->id}/tags", [
            'ids' => [$user->id]
        ])
        ->assertUnprocessable()
        ->assertSee(['users', 'missing']);

});