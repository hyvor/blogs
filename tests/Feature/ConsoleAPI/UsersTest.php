<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;


class UsersTest extends TestCase
{

    use RefreshDatabase;

    private function callEndpoint($method, $user, $data = null) {
        return $this->call($method, 'http://blogs.hyvor.test/api/console/v0/blog/test/'.$user, $data);
    }

    /**
    * A basic test example.
    *
    * @return void
    */
    public function test_get_request()
    {
        $response = $this->callEndpoint('GET', 'users', ['limit' => 2]);
        $response->assertStatus(200);
    }

    public function test_post_request()
    {
        $response = $this->callEndpoint('POST', 'user', [
            'picture_id' => null,
            'slug' => 'revolution-blog',
            'status' => 'active',
            'role' => 'owner',
            'email' =>'hyvor@new.record',
            'url' => null,
            'social_facebook' => null,
            'social_twitter' => null,
            'social_linkedin' => null,
            'social_youtube' => null,
            'social_instagram' =>  null,
            'name' => 'test zone',
            'location' =>  null,
            'bio' => null,
        ]);
        $response->assertStatus(500);
    }


    public function test_patch_request()
    {
        $id = 1;
        $response = $this->callEndpoint('PATCH', 'user/'.$id , [
            'picture_id' => null,
            'slug' => 'revolution',
            'status' => 'active',
            'role' => 'owner',
            'email' =>'blog@blig.com',
            'url' => null,
            'social_facebook' => null,
            'social_twitter' => null,
            'social_linkedin' => null,
            'social_youtube' => null,
            'social_instagram' =>  null,
            'name' => 'test user',
            'location' =>  null,
            'bio' => null,
        ]);
        $response->assertStatus(500);
    }


    public function test_delete_request()
    {
        $id = 1;
        $response = $this->callEndpoint('DELETE', 'user/'.$id , [
            'languageId' => 1,
        ]);
        $response->assertStatus(200);
    }

    public function test_delete_Variant_request()
    {
        $id = 1;
        $response = $this->callEndpoint('DELETE', 'user/'.$id , [
            'languageId' => 2,
        ]);
        $response->assertStatus(200);
    }


    public function test_createVariant_request()
    {
        $response = $this->callEndpoint('POST', 'userVariant', [
            'userId' => 1,
            'languageId' => 2,
        ]);
        $response->assertStatus(200);
    }


    public function test_user_validation()
    {
        $user = User::make([
            'slug' => 'hyvor-test',
            'status' => 'active',
            'role' => 'owner',
            'email' =>'hyvor@hyvor.com',
            'name' => 'hyvorBlogs',
        ]);
        $this->assertTrue(
            $user->slug != null,
            $user->status != null,
            $user->role != null,
            $user->email != null,
            $user->name != null,
        );
    }
}
