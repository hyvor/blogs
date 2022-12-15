<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostUpdatedEvent;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->blog = blogWithAccess();
    addPrimaryLanguage($this->blog);
    $this->post = addPost($this->blog);
});

it('validates', function () {
    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/authors", [
        'ids' => ['some', 'text'],
    ])
        ->assertUnprocessable()
        ->assertSee(['ids.0', 'must', 'integer']);
});

it('changes authors', function () {
    Event::fake();

    PostAuthor::where('post_id', $this->post->id)->delete();
    $authors = addUsers($this->blog, 3);

    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/authors", [
            'ids' => $authors->map(fn ($user) => $user->id)->toArray(),
        ])
        ->assertOk();

    expect(PostAuthor::where('post_id', $this->post->id)->count())->toBe(count($authors));

    Event::assertDispatched(PostUpdatedEvent::class, fn (PostUpdatedEvent $event) => $event->post->id === $this->post->id);
});

it('removes all authors', function () {
    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/authors", [
            'ids' => [],
        ])
        ->assertOk();

    expect(PostAuthor::where('post_id', $this->post->id)->count())->toBe(0);
});

it('does not add users from other blogs', function () {
    $user = addUser(blog());

    consoleApi($this->blog, 'PATCH', "/post/{$this->post->id}/authors", [
            'ids' => [$user->id],
        ])
        ->assertUnprocessable()
        ->assertSee(['users', 'missing']);
});
