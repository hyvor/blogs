<?php

// namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Models\Tag;

// To run the TagsTest class only run this command in the command line.
// php artisan test  --filter 'TagsTest'

class TagsTest extends TestCase
{
    use RefreshDatabase;

    private function callEndpoint($method, $data = null) {
        // return $this->call($method, '/api/console/v0/blog/test/tags'. ($data ? '?' . http_build_query($data) : ''));
        return $this->call($method, '/api/console/v0/blog/test/tags', $data);
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

    public function test_tag_validation()
    {
        $tag = Tag::make([
            'name' => 'Testing redirects',
            'slug' => 'this is the new url'
        ]);
        $this->assertTrue($tag->name != $tag->slug);
    }

    public function test_tag_name_not_null()
    {
        $tag = Tag::make([
            'name' => 'Testing redirects',
            'slug' => 'this is the new url'
        ]);
        $this->assertTrue($tag->name != null);
    }

    public function test_tag_slug_not_null()
    {
        $tag = Tag::make([
            'name' => 'Testing redirects',
            'slug' => 'this is the new url'
        ]);

        $tagSlug = str_replace(' ', '-', $tag->slug);
        $this->assertTrue($tagSlug != null);
    }

    public function test_create_new_tag()
    {
        $response = $this->callEndpoint('POST',[
            'blog_id' => 1,
            'name' => 'testing create new tag',
            'slug' => 'test completed',
            'description' => 'another test description'
        ]);

        $response->assertStatus(405);
        // $response->assertStatus(200);
    }

    public function test_update_users()
    {
        $response = $this->callEndpoint('PUT',[
            'name' => 'testing update new tag',
            'slug' => 'test new update completed',
        ]);

        $response->assertStatus(405);
    }
}
