<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Models\Tag;
use App\Models\Blog;

// To run the TagsTest class only run this command in the command line.
// php artisan test  --filter 'TagsTest'

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function callEndpoint($method, $data = null) {
        // return $this->call($method, '/api/console/v0/blog/test/blogs'. ($data ? '?' . http_build_query($data) : ''));
        return $this->call($method, '/api/console/v0/blog/test/blogs', $data);
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

    public function test_blog_validation()
    {
        $blog = Tag::make([
            'name' => 'Testing redirects',
            'slug' => 'this is the new url'
        ]);
        $this->assertTrue($blog->name != $blog->slug);
    }

    public function test_blog_name_not_null()
    {
        $blog = Tag::make([
            'name' => 'Testing redirects',
            'slug' => 'this is the new url'
        ]);
        $this->assertTrue($blog->name != null);
    }

    public function test_blog_slug_not_null()
    {
        $blog = Tag::make([
            'name' => 'Testing redirects',
            'slug' => 'this is the new url'
        ]);

        $blogSlug = str_replace(' ', '-', $blog->slug);
        $this->assertTrue($blogSlug != null);
    }

    public function test_create_new_blog()
    {
        $response = $this->callEndpoint('POST',[
            'blog_id' => 1,
            'name' => 'testing create new blog',
            'slug' => 'test completed',
            'description' => 'another test description'
        ]);

        $response->assertStatus(405);
        // $response->assertStatus(200);
    }

    public function test_update_blog()
    {
        $response = $this->callEndpoint('PUT',[
            'name' => 'testing update new blog',
            'slug' => 'test new update completed',
        ]);

        $response->assertStatus(405);
    }
}
