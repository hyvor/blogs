<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostUpdatedEvent;
use App\Models\Post;
use App\Models\PostTag;
use App\Models\Tag;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->blog = blogWithAccess();
    addPrimaryLanguage($this->blog);
    $this->post = addPost($this->blog);
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

    Event::assertDispatched(PostUpdatedEvent::class, fn (PostUpdatedEvent $event) => $event->post->id === $this->post->id);
});

it('removes all tags', function () {
    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/tags", [
            'ids' => [],
        ])
        ->assertOk();

    expect(PostTag::where('post_id', $this->post->id)->count())->toBe(0);
});

it('does not add users from other blogs', function () {
    $user = addTag(blog());

    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/tags", [
            'ids' => [$user->id],
        ])
        ->assertUnprocessable()
        ->assertSee(['users', 'missing']);
});
