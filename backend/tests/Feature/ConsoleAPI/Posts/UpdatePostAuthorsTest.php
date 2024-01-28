<?php

namespace Tests\Feature\ConsoleAPI\Posts;

use App\Domains\Post\Events\PostUpdatedEvent;
use App\Domains\Tag\Events\TagUpdatedEvent;
use App\Domains\User\Events\UserUpdatedEvent;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->blog = blogWithAccess();
    addPrimaryLanguage($this->blog);
    $this->post = addPublishedPost($this->blog);
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
    expect($authors[0]->refresh()->posts_count)->toBe(1);

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



it('updates counts correctly', function() {

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);
    $language2 = addLanguage($blog);

    $post1 = addPublishedPost($blog);
    $post2 = addPublishedPost($blog);

    $user1 = addUser($blog);
    $user2 = addUser($blog);

    // adding
    consoleApi($blog, 'PATCH', "/post/{$post1->id}/authors", [
        'ids' => [$user1->id, $user2->id],
    ])->assertOk();
    expect($user1->refresh()->posts_count)->toBe(1);
    expect($user2->refresh()->posts_count)->toBe(1);

    consoleApi($blog, 'PATCH', "/post/{$post2->id}/authors", [
        'ids' => [$user1->id],
    ])->assertOk();

    expect($user1->refresh()->posts_count)->toBe(2);

    // drafts are ignored
    $draft = addPost($blog, [], ['status' => 'draft']);
    consoleApi($blog, 'PATCH', "/post/{$draft->id}/authors", [
        'ids' => [$user1->id],
    ])->assertOk();
    expect($user1->refresh()->posts_count)->toBe(2);

    // pages are ignored
    $page = addPost($blog, ['is_page' => true], []);
    consoleApi($blog, 'PATCH', "/post/{$page->id}/authors", [
        'ids' => [$user1->id],
    ])->assertOk();
    expect($user1->refresh()->posts_count)->toBe(2);

});

it('updates counts when removing', function() {

    Event::fake();

    $blog = blogWithAccess();
    addPrimaryLanguage($blog);

    $post1 = addPublishedPost($blog);
    $user1 = addUser($blog, ['posts_count' => 1]);

    PostAuthor::create([
        'post_id' => $post1->id,
        'user_id' => $user1->id,
    ]);

    // removing
    consoleApi($blog, 'PATCH', "/post/{$post1->id}/authors", [
        'ids' => [],
    ])->assertOk();
    expect($user1->refresh()->posts_count)->toBe(0);

    Event::assertDispatched(UserUpdatedEvent::class, 1);

});