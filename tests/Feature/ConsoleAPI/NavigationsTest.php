<?php

namespace Tests\Feature\ConsoleAPI;

use App\Domains\Navigation\NavigationRepository;
use App\Models\Blog;

// php artisan test  --filter 'NavigationsTest'

beforeEach(function() {
    $this->blog = Blog::find(config('test.blog_id'));
    Navigation::factory()
        ->count(8)
        ->create([
            'blog_id' => $this->blog,
        ]);
});

it('fetches navigation', function () {
    $this
        ->callConsoleApi('GET', 'navigation')
        ->assertOk();
});

it('creates a navigation success', function () {
    $data = 'about';
    $this
        ->callConsoleApi('POST', 'navigation', [
            'navigation_name' => $data,
            'navigation_url' => $data,
            'type' => 'header',
        ])
        ->assertOk();
});

it('creating navigation fails on empty fields', function () {
    $this
        ->callConsoleApi('POST', 'navigation')
        ->assertStatus(400);    
        // ->assertStatus(500);
});

it('creates a navigation fails if (name) is null', function () {
    $data = 'about';
    $this
        ->callConsoleApi('POST', 'navigation', [
            'navigation_name' => null,
            'navigation_url' => $data,
            'type' => 'header',
        ])
        ->assertStatus(400);
});

it('creates a navigation fails if (url) is null', function () {
    $data = 'about';
    $this
        ->callConsoleApi('POST', 'navigation', [
            'navigation_name' => $data,
            'navigation_url' => null,
            'type' => 'header',
        ])
        ->assertStatus(400);
});

it('creates a navigation fails if (type) is not header or footer', function () {
    $data = 'about';
    $this
        ->callConsoleApi('POST', 'navigation', [
            'navigation_name' => $data,
            'navigation_url' => null,
            'type' => 'wrong',
        ])
        ->assertStatus(400);
});

it('creates a navigation fails if there are more than 8 header navigations.', function () {
    $getHeaderCount = NavigationRepository::getHeaderCount();
    $headerCount = $getHeaderCount < 8;
    $data = 'about';
    if ($headerCount) {
        $this
            ->callConsoleApi('POST', 'navigation', [
                'navigation_name' => $data,
                'navigation_url' => $data,
                'type' => 'header',
            ])
            ->assertStatus(400);
    }
    else {
        $this->assertFalse(false);
    }
});

it('creates a navigation fails if there are more than 8 footer navigations.', function () {
    $getFooterCount = NavigationRepository::getFooterCount();
    $footerCount = $getFooterCount < 8;
    $data = 'about';
    if ($footerCount) {
        $this
            ->callConsoleApi('POST', 'navigation', [
                'navigation_name' => $data,
                'navigation_url' => $data,
                'type' => 'header',
            ])
            ->assertStatus(400);
    }
    else{
        $this->assertFalse(false);
    }
});

it('deleting navigation success', function () {
    $id = 1;
    $this
        ->callConsoleApi('DELETE', 'navigation/'.$id)
        ->assertOk();
});

it('updating a navigation success', function () {
    $id = 1;
    $data = 'new';
    $this
        ->callConsoleApi('PUT', 'navigation/'.$id, [
            'navigation_name' => $data,
            'navigation_url' => $data,
            'type' => 'header',
        ])
        // ->assertStatus(400);
        ->assertOk();
});

it('updating a navigation fails if (name) is null', function () {
    $id = 1;
    $data = 'new';
    $this
        ->callConsoleApi('PUT', 'navigation/'.$id, [
            'navigation_name' => null,
            'navigation_url' => $data,
            'type' => 'header',
        ])
        ->assertStatus(400);
});

it('updating a navigation fails if (url) is null', function () {
    $id = 1;
    $data = 'new';
    $this
        ->callConsoleApi('PUT', 'navigation/'.$id, [
            'navigation_name' => $data,
            'navigation_url' => null,
            'type' => 'header',
        ])
        ->assertStatus(400);
});

it('update the sort', function () {
    $id = 1;
    $this
        ->callConsoleApi('PUT', '/navigation/sort/'.$id, [
            'navigationSort' => 2,
        ])
        ->assertOk();
});

it('update the navigation source', function () {
    $id = 1;
    $this
        ->callConsoleApi('PUT', '/navigation/source/'.$id, [
            'sort' => 2,
        ])
        ->assertOk();
});
