<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Testing\Fluent\AssertableJson;

//  To run the user tests - php artisan test  --filter 'RoutesTest'

it('fetches routes', function() {
    $this
        ->callConsoleApi('GET', 'route')
        ->assertStatus(200);
});


it('creates a route success', function() {

    $data = 'about';
    $this
        ->callConsoleApi('POST', 'route', [
            'name' => $data,
            'match' => $data,
            'template' => $data
        ])
        ->assertStatus(200)
        ->assertJson(function (AssertableJson $json) use ($data) {
            $json->has('id')
                ->where('match', $data)
                ->etc();
        });
    
});

it('creating route fails on empty fields', function() {
    $this
        ->callConsoleApi('POST', 'route')
        ->assertStatus(500);    
});


it('creates a route fails if name is null', function() {

    $data = 'about';
    $this
        ->callConsoleApi('POST', 'route', [
            'name' => null,
            'match' => $data,
            'template' => $data
        ])
        ->assertStatus(500);
});

it('creates a route fails if match is null', function() {

    $data = 'about';
    $this
        ->callConsoleApi('POST', 'route', [
            'name' =>  $data,
            'match' =>null,
            'template' => $data
        ])
        ->assertStatus(500);
});

it('creates a route fails if template is null', function() {

    $data = 'about';
    $this
        ->callConsoleApi('POST', 'route', [
            'name' =>  $data,
            'match' =>$data,
            'template' => null
        ])
        ->assertStatus(500);
});

it('deleting route success', function() {

    $id = 1;
    $this
        ->callConsoleApi('DELETE', 'route/'.$id)
        ->assertStatus(200);    
});

it('updating a route success', function() {

    $id = 1;
    $data = 'New data';
    $this
        ->callConsoleApi('PUT', 'route/'.$id, [
            'name' => $data,
            'match' => $data,
            'template' => $data
        ])
        ->assertStatus(200);
});
