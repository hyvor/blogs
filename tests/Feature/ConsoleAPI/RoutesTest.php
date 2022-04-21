<?php

namespace Tests\Feature\ConsoleAPI;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class RoutesTest extends TestCase
{
    use RefreshDatabase;

    private function callEndpoint($method, $route, $data = null) {
        return $this->call($method, 'http://blogs.hyvor.test/api/console/v0/blog/test/'.$route , $data);
    }

    /**
    * A basic test example.
    *
    * @return void
    */
    public function test_get_request()
    {
        $response = $this->callEndpoint('GET', '', [
            'limit' => 2
        ]);
        $response->assertStatus(404);
    }

    public function test_post_request()
    {
        $response = $this->callEndpoint('POST',[
            'blog_id' => 1,
            'name' => 'testing create new blog',
            'slug' => 'test completed',
            'description' => 'another test description'
        ]);
        $response->assertStatus(405);
    }

    public function test_put_request()
    {
        $response = $this->callEndpoint('PUT',[
            'name' => 'testing update new blog',
            'slug' => 'test new update completed',
        ]);
        $response->assertStatus(405);
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




    // public function test_blog_validation()
    // {
    //     $blog = Blog::make([
    //         'name' => 'Testing redirects',
    //         'slug' => 'this is the new url'
    //     ]);
    //     $this->assertTrue($blog->name != $blog->slug);
    // }

    // public function test_blog_name_not_null()
    // {
    //     $blog = Blog::make([
    //         'name' => 'Testing redirects',
    //         'slug' => 'this is the new url'
    //     ]);
    //     $this->assertTrue($blog->name != null);
    // }

    // public function test_blog_slug_not_null()
    // {
    //     $blog = Blog::make([
    //         'name' => 'Testing redirects',
    //         'slug' => 'this is the new url'
    //     ]);

    //     $blogSlug = str_replace(' ', '-', $blog->slug);
    //     $this->assertTrue($blogSlug != null);
    // }

}
