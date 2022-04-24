<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Redirect;
use App\Models\Blog;

//  To run the redirect tests - php artisan test  --filter 'RedirectTest'

beforeEach(function() {
    $this->blog = Blog::find(config('test.blog_id'));
    Redirect::factory()
        ->count(10)
        ->create([
            'blog_id' => $this->blog,
        ]);
});

it('fetches redirects', function() {

    $this
    ->callConsoleApi('GET', 'redirect', [
       'limit' => 5
    ])
    ->assertOk()
    ->assertJson(function (AssertableJson $json) {
        $json->count(5)
            ->has('0', function (AssertableJson $json) {
                $json->has('id')
                    ->etc();
            });
    });
});

it('fetches redirect with offset', function() {
    
    $redirectsCount = Redirect::where('blog_id', config('test.blog_id'))->count();
    $this
        ->callConsoleApi('GET', 'redirect', [
            'limit' => $redirectsCount,
            'offset' => $redirectsCount - 1
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count(1);
        });
    
});

it('creates a redirect success', function() {

    $this
        ->callConsoleApi('POST', 'redirect', [
            'path' => 'test-one',
            'to' => 'test-two',
            'type' => '302'
        ])
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('id')
                ->etc();
        }); 
});

it('creates redirect should fail if type is not 302 or 301', function() {

    $data = 'about';
    $this
        ->callConsoleApi('POST', 'redirect', [
            'path' => $data,
            'to' => $data,
            'type' => 'hello'
        ])
        ->assertStatus(400);
});

it('creating redirects fails on empty values', function() {

    $this
        ->callConsoleApi('POST', 'redirect')
        ->assertStatus(400);    
});

it('creates redirect should fail if (path) has spaces', function() {

    $data = 'about';
    $this
        ->callConsoleApi('POST', 'redirect', [
            'path' => 'this is wrong',
            'to' => $data,
            'type' => '301'
        ])
        ->assertStatus(400);
});

it('creates redirect should fail if (to) has spaces', function() {

    $data = 'about';
    $this
        ->callConsoleApi('POST', 'redirect', [
            'path' => $data,
            'to' => 'this is also wrong',
            'type' => 'hello'
        ])
        ->assertStatus(400);
});

it('creates redirect should fail if (to) has a null value', function() {

    $data = 'about';
    $this
        ->callConsoleApi('POST', 'redirect', [
            'path' => $data,
            'to' => null,
            'type' => 'hello'
        ])
        ->assertStatus(400);
});

it('creates redirect should fail if (path) has a null value', function() {

    $data = 'about';
    $this
        ->callConsoleApi('POST', 'redirect', [
            'path' => null,
            'to' => $data,
            'type' => 'hello'
        ])
        ->assertStatus(400);
});

it('deleting redirect success', function() {

    $id = 1;
    $this
        ->callConsoleApi('DELETE', 'redirect/'.$id)
        ->assertOk();
});

it('updating a route success', function() {

    $id = 1;
    $this
        ->callConsoleApi('PUT', 'redirect/'.$id, [
            'name' => 'test',
            'match' => 'one',
            'template' => '301'
        ])
        ->assertStatus(400);
});

it('updating redirect should fail if (path) has spaces', function() {

    $id = 1;
    $data = 'about';
    $this
        ->callConsoleApi('PUT', 'redirect/'.$id, [
            'path' => 'this is wrong',
            'to' => $data,
            'type' => '301'
        ])
        ->assertStatus(400);
});

it('updating redirect should fail if (to) has spaces', function() {

    $id = 1;
    $data = 'about';
    $this
        ->callConsoleApi('PUT', 'redirect/'.$id, [
            'path' => $data,
            'to' => 'this is also wrong',
            'type' => 'hello'
        ])
        ->assertStatus(400);
});

it('updating redirect should fail if (to) has a null value', function() {

    $id = 1;
    $data = 'about';
    $this
        ->callConsoleApi('PUT', 'redirect/'.$id, [
            'path' => $data,
            'to' => null,
            'type' => 'hello'
        ])
        ->assertStatus(400);
});

it('updating redirect should fail if (path) has a null value', function() {

    $id = 1;
    $data = 'about';
    $this
        ->callConsoleApi('PUT', 'redirect/'.$id, [
            'path' => null,
            'to' => $data,
            'type' => 'hello'
        ])
        ->assertStatus(400);
});