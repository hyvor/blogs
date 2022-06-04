<?php

namespace Tests\Feature\ConsoleAPI\Users;

use Hyvor\HyvorConnecter\Userbase;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {

    // picture_url is not tested here

    $this->languageId = blog()->languages[0]->id;

    $this->username = 'Hyvor';
    $this->email = 'hyvor@hyvor.com';
    $this->name = 'Hyvor Company';
    $this->bio = 'Building SaaS products';
    $this->location = 'France';
    $this->websiteUrl = 'https://hyvor.com';

    Userbase::fake([
        [
            'id' => 1239,
            'username' => $this->username,
            'email' => $this->email,
            'name' => $this->name,
            'bio' => $this->bio,
            'location' => $this->location,
            'website_url' => $this->websiteUrl,
        ]
    ]);

});

it('creates a user from username and email', function() {

    $this->callConsoleApi('POST', '/user', [
        'username_or_email' => $this->username,
        'role' => 'admin'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('email', $this->email)
                ->where('role', 'admin')
                ->where('website_url', $this->websiteUrl)
                ->where("variants.$this->languageId.name", $this->name)
                ->where("variants.$this->languageId.bio", $this->bio)
                ->where("variants.$this->languageId.location", $this->location)
                ->etc()
        );

});

it('creates a user from email', function() {

    $this->callConsoleApi('POST', '/user', [
        'username_or_email' => $this->email,
        'role' => 'contributor'
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) =>
        $json->where('email', $this->email)
            ->where('role', 'contributor')
            ->where('website_url', $this->websiteUrl)
            ->where("variants.$this->languageId.name", $this->name)
            ->where("variants.$this->languageId.bio", $this->bio)
            ->where("variants.$this->languageId.location", $this->location)
            ->etc()
        );

});

it('does not create owners', function() {

    $this->callConsoleApi('POST', '/user', [
        'username_or_email' => $this->email,
        'role' => 'owner'
    ])
        ->assertUnprocessable()
        ->assertSee('Owners cannot be created');

});


it('does not create if the user is not found', function() {

    $this->callConsoleApi('POST', '/user', [
        'username_or_email' => 'not.a.user',
        'role' => 'admin'
    ])
        ->assertUnprocessable()
        ->assertSee('Unable to find the user');

});

it('does not create if the user already exists', function() {

    $this->callConsoleApi('POST', '/user', [
        'username_or_email' => $this->username,
        'role' => 'admin'
    ])->assertOk();

    $this->callConsoleApi('POST', '/user', [
        'username_or_email' => $this->username,
        'role' => 'admin'
    ])->assertUnprocessable()
        ->assertSee('User already exists');

});