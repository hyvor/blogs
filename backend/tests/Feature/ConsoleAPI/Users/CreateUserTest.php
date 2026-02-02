<?php

namespace Tests\Feature\ConsoleAPI\Users;

use App\Domains\User\Events\UserCreatedEvent;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->blog = blogWithAccess();
    addPrimaryLanguage($this->blog);
    addDefaultRoutes($this->blog, 'author');

    // picture_url is not tested here

    $this->username = 'Hyvor';
    $this->email = 'hyvor@hyvor.com';
    $this->name = 'Hyvor Company';
    $this->bio = 'Building SaaS products';
    $this->location = 'France';
    $this->websiteUrl = 'https://hyvor.com';

    AuthFake::databaseSet([
        [
            'id' => 1239,
            'username' => $this->username,
            'email' => $this->email,
            'name' => $this->name,
            'bio' => $this->bio,
            'location' => $this->location,
            'website_url' => $this->websiteUrl,
        ],
    ]);
});

it('creates a user from username and email', function () {
    Event::fake();
    Mail::fake();

    consoleApi($this->blog, 'POST', '/user', [
        'username_or_email' => $this->username,
        'role' => 'admin',
    ])
        ->assertOk()
        ->assertJson(
            fn(AssertableJson $json)
                => $json
                ->where('email', 'hyv***@hyv***')
                ->where('role', 'admin')
                ->where('website_url', $this->websiteUrl)
                ->where('variants.0.name', $this->name)
                ->where('variants.0.bio', $this->bio)
                ->where('variants.0.location', $this->location)
                ->etc(),
        );

    Event::assertDispatched(UserCreatedEvent::class);
});

it('creates a user from email', function () {
    consoleApi($this->blog, 'POST', '/user', [
        'username_or_email' => $this->email,
        'role' => 'contributor',
    ])
        ->assertOk()
        ->assertJson(
            fn(AssertableJson $json)
                => $json
                ->where('email', 'hyv***@hyv***')
                ->where('role', 'contributor')
                ->where('website_url', $this->websiteUrl)
                ->where('variants.0.name', $this->name)
                ->where('variants.0.bio', $this->bio)
                ->where('variants.0.location', $this->location)
                ->etc(),
        );
});

it('does not create owners', function () {
    consoleApi($this->blog, 'POST', '/user', [
        'username_or_email' => $this->email,
        'role' => 'owner',
    ])
        ->assertUnprocessable()
        ->assertSee('Owners cannot be created');
});

it('does not create if the user is not found', function () {
    consoleApi($this->blog, 'POST', '/user', [
        'username_or_email' => 'not.a.user',
        'role' => 'admin',
    ])
        ->assertUnprocessable()
        ->assertSee('Unable to find the user');
});

it('does not create if the user already exists', function () {
    $blog = blogWithAccessLanguageAndRoutes();
    BillingFake::enable(license: new BlogsLicense(users: 3));

    consoleApi($blog, 'POST', '/user', [
        'username_or_email' => $this->username,
        'role' => 'admin',
    ])->assertOk();

    consoleApi($blog, 'POST', '/user', [
        'username_or_email' => $this->username,
        'role' => 'admin',
    ])
        ->assertUnprocessable()
        ->assertSee('User already exists');
});

it('fails when limits are exceeded', function () {
    $blog = blogWithAccess();
    $blog->setCount('users', 2);

    consoleApi($blog, 'POST', '/user', [
        'username_or_email' => 'test',
        'role' => 'admin',
    ])
        ->assertUnprocessable()
        ->assertSee('Max users limit exceeded. Please upgrade your plan');
});
