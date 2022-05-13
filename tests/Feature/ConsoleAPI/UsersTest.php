<?php

namespace Tests\Feature\ConsoleAPI;

use Tests\TestCase;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Language;

// To run the user tests - php artisan test  --filter 'UsersTest'

it('fetches users', function() {
   
    $this
        ->callConsoleApi('GET', 'users')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('0', function (AssertableJson $json) {
                    $json->has('id')
                        ->etc();
                });
        });
});

it('creating user fails if data is empty', function() {

    $this
        ->callConsoleApi('POST', 'user')
        ->assertStatus(500);    
});

it('creates a user success', function() {

    $data = 'test';
    $language = Language::where('blog_id', config('test.blog_id'))
        ->where('is_primary', true)
        ->first();
    
    $this
        ->callConsoleApi('POST', 'user', [
            'role' => 'admin',
            'status' => 'invited',
            'slug' => $data,
            'email' => 'test@test.com',
        ])
        ->assertStatus(500);
        // ->assertJson(function (AssertableJson $json) use ($data, $language) {
        //     $json->has('id')
        //         ->where('slug', $data)
        //         ->has("variants.{$language->id}", function (AssertableJson $json) use ($data) {
        //             $json->where('name', $data)
        //                 ->where('description', $data)
        //                 ->etc();
        //         })
        //         ->etc();
        // });
    
});

it('deleting user with the default language', function() {

    $id = 1;
    $this
        ->callConsoleApi('DELETE', 'user/'.$id, [
            'languageId' => 1,
        ])
        ->assertOk();    
});

it('deleting user variant except the default language', function() {

    $id = 1;
    $this
        ->callConsoleApi('DELETE', 'user/'.$id, [
            'languageId' => 2,
        ])
        ->assertOk();   
});

it('when deleting if the language ID is null', function() {

    $id = 1;
    $this
        ->callConsoleApi('DELETE', 'user/'.$id, [
            'languageId' => null,
        ])
        ->assertStatus(500);   
});

it('create variant ( It should not be the default language )', function() {

    $id = 1;
    $this
        ->callConsoleApi('POST', '/user/variant', [
            'userId' => 1,
            'languageId' => 2,
        ])
        ->assertOk();
});

it('create variant ( If language id is null ) ', function() {

    $id = 1;
    $this
        ->callConsoleApi('POST', '/user/variant', [
            'userId' => 1,
            'languageId' => null,
        ])
        ->assertStatus(500);
});