<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Models\Tag;

// To run the TagsTest class only run this command in the command line.
// php artisan test  --filter 'TagsTest'

class TagsTest extends TestCase
{
    use RefreshDatabase;

    private function callEndpoint($method, $tag,  $data = null) {
        return $this->call($method, 'http://blogs.hyvor.test/api/console/v0/blog/test/'.$tag, $data);
    }

    /**
    * A basic test example.
    *
    * @return void
    */
    public function test_get_request()
    {
        $response = $this->callEndpoint('GET', 'tags', [
            'limit' => 2
        ]);
        $response->assertStatus(200);
    }

    public function test_post_request()
    {
        $response = $this->callEndpoint('POST', 'tags', [
            'name' => 'testing create new tag',
            'slug' => 'test completed',
            'description' => 'another test description'
        ]);

        $response->assertStatus(200);
    }

    public function test_put_request()
    {
        $tagId = 1;
        $response = $this->callEndpoint('PUT', 'tag/'.$tagId, [
            'name' => 'hyvor',
            'slug' => 'test-new',
            'languageId' => 1,
            'codeHead' => null,
            'codeFoot' => null,
            'description' => 'test new update completed',
        ]);
        $response->assertStatus(200);
    }

    public function test_delete_request()
    {
        $tagId = 1;
        $response = $this->callEndpoint('DELETE', 'tag/'.$tagId , [
            'languageId' => 1,
        ]);
        $response->assertStatus(200);
    }

    public function test_delete_Variant_request()
    {
        $tagId = 1;
        $response = $this->callEndpoint('DELETE', 'tag/'.$tagId , [
            'languageId' => 2,
        ]);
        $response->assertStatus(200);
    }

    public function test_createVariant_request()
    {
        $response = $this->callEndpoint('POST', 'tagVariant', [
            'tagId' => 1,
            'languageId' => 2,
        ]);
        $response->assertStatus(200);
    }


    public function test_tag_validation()
    {
        $tag = Tag::make([
            'name' => 'hyvor',
            'slug' => 'test-new',
        ]);
        $this->assertTrue(
            $tag->name != null,
            $tag->slug != null,
        );
    }

    public function test_slug_validation_null()
    {
        $tag = Tag::make([
            'slug' => 'this is the new url'
        ]);

        $tagSlug = str_replace(' ', '-', $tag->slug);
        $this->assertTrue($tagSlug != null);
    }
}
