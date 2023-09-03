<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->blog = blogWithAccess();
    addPrimaryLanguage($this->blog);
    $this->post = addPublishedPost($this->blog);
});

it('validates', function () {
    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/tags", [
        'ids' => ['some', 'text'],
    ])
        ->assertUnprocessable()
        ->assertSee(['ids.0', 'must', 'integer']);
});

it('changes tags', function () {
    Event::fake();

    PostTag::where('post_id', $this->post->id)->delete();
    $tags = addTags($this->blog, 3);

    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/tags", [
            'ids' => $tags->map(fn ($user) => $user->id)->toArray(),
        ])
        ->assertOk();

    expect(PostTag::where('post_id', $this->post->id)->count())->toBe(count($tags));
    expect($tags[0]->refresh()->posts_count)->toBe(1);

    Event::assertDispatched(PostUpdatedEvent::class, fn (PostUpdatedEvent $event) => $event->post->id === $this->post->id);
});

it('removes all tags', function () {
    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/tags", [
            'ids' => [],
        ])
        ->assertOk();

    expect(PostTag::where('post_id', $this->post->id)->count())->toBe(0);
});

it('does not add tags from other blogs', function () {
    $user = addTag(blog());

    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/tags", [
            'ids' => [$user->id],
        ])
        ->assertUnprocessable()
        ->assertSee(['users', 'missing']);
});



it('updates counts correctly', function() {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    $language2 = addLanguage($blog);

    $post1 = addPublishedPost($blog);
    $post2 = addPublishedPost($blog);

    $tag1 = addTag($blog);
    $tag2 = addTag($blog);

    // adding
    consoleApi($blog, 'PATCH', "/post/{$post1->id}/tags", [
        'ids' => [$tag1->id, $tag2->id],
    ])->assertOk();
    expect($tag1->refresh()->posts_count)->toBe(1);
    expect($tag2->refresh()->posts_count)->toBe(1);

    consoleApi($blog, 'PATCH', "/post/{$post2->id}/tags", [
        'ids' => [$tag1->id],
    ])->assertOk();

    expect($tag1->refresh()->posts_count)->toBe(2);

    // drafts are ignored
    $draft = addPost($blog, [], ['status' => 'draft']);
    consoleApi($blog, 'PATCH', "/post/{$draft->id}/tags", [
        'ids' => [$tag1->id],
    ])->assertOk();
    expect($tag1->refresh()->posts_count)->toBe(2);

    // pages are ignored
    $page = addPost($blog, ['is_page' => true], []);
    consoleApi($blog, 'PATCH', "/post/{$page->id}/tags", [
        'ids' => [$tag1->id],
    ])->assertOk();
    expect($tag1->refresh()->posts_count)->toBe(2);

});

it('updates counts when removing', function() {

    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);

    $post1 = addPublishedPost($blog);
    $tag1 = addTag($blog, ['posts_count' => 1]);

    PostTag::create([
        'post_id' => $post1->id,
        'tag_id' => $tag1->id,
    ]);

    // removing
    consoleApi($blog, 'PATCH', "/post/{$post1->id}/tags", [
        'ids' => [],
    ])->assertOk();
    expect($tag1->refresh()->posts_count)->toBe(0);

    Event::assertDispatched(TagUpdatedEvent::class, 1);

});