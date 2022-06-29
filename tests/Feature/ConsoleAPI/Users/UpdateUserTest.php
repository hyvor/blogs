<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserUpdatedEvent;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates a user', function () {
    Event::fake();

    $user = User::factory()->create([
        'blog_id' => blog(),
        'role' => 'admin',
    ]);

    $updates = [
        'hyvor_user_id' => 10,
        'role' => 'editor',
        'status' => 'active',
        'slug' => 'i-am-hyvor',
        'email' => 'email@hyvor.com',
        'website_url' => faker()->url(),
        'picture_url' => faker()->url(),

        'social_facebook' => faker()->url(),
        'social_twitter' => faker()->url(),
        'social_linkedin' => faker()->url(),
        'social_youtube' => faker()->url(),
        'social_tiktok' => faker()->url(),
        'social_instagram' => faker()->url(),
        'social_github' => faker()->url(),
    ];

    $this->callConsoleApi('PATCH', "/user/$user->id", $updates)
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($updates) {
            foreach ($updates as $key => $value) {
                $json->where($key, $value);
            }
            $json->etc();
        });

    Event::assertDispatched(UserUpdatedEvent::class);
});

it('does not update the role of the owner', function () {
    $user = User::factory()->create(['role' => 'owner', 'blog_id' => blog()]);

    $this->callConsoleApi('PATCH', "/user/$user->id", [
        'role' => 'admin',
    ])
        ->assertUnprocessable()
        ->assertSee('cannot update the role of the owner');
});

it('does not update the role to owner', function () {
    $user = User::factory()->create(['role' => 'admin', 'blog_id' => blog()]);

    $this->callConsoleApi('PATCH', "/user/$user->id", [
        'role' => 'owner',
    ])
        ->assertUnprocessable()
        ->assertSee('cannot update the role to owner');
});

it('does not update the status of the owner', function () {
    $user = User::factory()->create(['role' => 'owner', 'blog_id' => blog()]);

    $this->callConsoleApi('PATCH', "/user/$user->id", [
        'status' => 'blocked',
    ])
        ->assertUnprocessable()
        ->assertSee('cannot update the status of the owner');
});


it('returns an error when updating to an already existing slug', function() {

    User::factory()->create(['slug' => 'test', 'blog_id' => blog()]);
    $user2 = User::factory()->create(['slug' => 'test2', 'blog_id' => blog()]);

    $this->callConsoleApi('PATCH', "/user/$user2->id", [
        'slug' => 'test',
    ])
        ->assertUnprocessable()
        ->assertSee('Slug already taken');

});