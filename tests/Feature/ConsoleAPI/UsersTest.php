<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Models\Tag;


class UsersTest extends TestCase
{

    use RefreshDatabase;

    private function callEndpoint($method, $data = null) {
        return $this->call($method, '/api/console/v0/blog/test/users', $data);
    }

    /**
    * A basic test example.
    *
    * @return void
    */
    public function test_get_request()
    {
        $response = $this->callEndpoint('GET', ['limit' => 2]);
        $response->assertStatus(404);
    }

    public function test_user_validation()
    {
        $user = Tag::make([
            'name' => 'Testing users',
            'slug' => 'this is the new url'
        ]);
        $this->assertTrue($user->name != $user->slug);
    }

    public function test_user_name_not_null()
    {
        $user = Tag::make([
            'name' => 'Testing users',
            'slug' => 'this is the new url'
        ]);
        $this->assertTrue($user->name != null);
    }

    public function test_user_slug_not_null()
    {
        $user = Tag::make([
            'name' => 'Testing users',
            'slug' => 'this is the new url'
        ]);

        $userSlug = str_replace(' ', '-', $user->slug);
        $this->assertTrue($userSlug != null);
    }

    public function test_create_new_user()
    {
        $response = $this->callEndpoint('POST',[
            'blog_id' => 1,
            'name' => 'testing create new user',
            'slug' => 'test completed',
            'description' => 'another test description'
        ]);

        $response->assertStatus(405);
        // $response->assertStatus(200);
    }

    public function test_update_users()
    {
        $response = $this->callEndpoint('PUT',[
            'name' => 'testing update new user',
            'slug' => 'test new update completed',
        ]);

        $response->assertStatus(405);
    }
}
