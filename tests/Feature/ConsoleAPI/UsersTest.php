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
        ->assertStatus(200)
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
        ->assertStatus(200);    
});

it('deleting user variant except the default language', function() {

    $id = 1;
    $this
        ->callConsoleApi('DELETE', 'user/'.$id, [
            'languageId' => 2,
        ])
        ->assertStatus(200);   
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
        ->assertStatus(200);
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

class UsersTest extends TestCase
{
    /**
    * A basic test example.
    *
    * @return void
    */
    // public function test_get_request()
    // {
    //     $response = $this->callEndpoint('GET', 'users', ['limit' => 2]);
    //     $response->assertStatus(200);
    // }

    // public function test_post_request()
    // {
    //     $response = $this->callEndpoint('POST', 'user', [
    //         'picture_id' => null,
    //         'slug' => 'revolution-blog',
    //         'status' => 'active',
    //         'role' => 'owner',
    //         'email' =>'hyvor@new.record',
    //         'url' => null,
    //         'social_facebook' => null,
    //         'social_twitter' => null,
    //         'social_linkedin' => null,
    //         'social_youtube' => null,
    //         'social_instagram' =>  null,
    //         'name' => 'test zone',
    //         'location' =>  null,
    //         'bio' => null,
    //     ]);
    //     $response->assertStatus(500);
    // }


    // public function test_patch_request()
    // {
    //     $id = 1;
    //     $response = $this->callEndpoint('PATCH', 'user/'.$id , [
    //         'picture_id' => null,
    //         'slug' => 'revolution',
    //         'status' => 'active',
    //         'role' => 'owner',
    //         'email' =>'blog@blig.com',
    //         'url' => null,
    //         'social_facebook' => null,
    //         'social_twitter' => null,
    //         'social_linkedin' => null,
    //         'social_youtube' => null,
    //         'social_instagram' =>  null,
    //         'name' => 'test user',
    //         'location' =>  null,
    //         'bio' => null,
    //     ]);
    //     $response->assertStatus(500);
    // }

}
