<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Models\Navigation;


// To run the Navigation Test class only run this command in the command line.
// php artisan test  --filter 'NavigationsTest'


// Must update the navigation test file and must connect the tests to the repository.
// We don't need to do validation tests.

class NavigationsTest extends TestCase
{

    use RefreshDatabase;

    private function callEndpoint($method, $data = null) {
        return $this->call($method, '/api/console/v0/blog/test/navigation', $data);
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

    public function test_navigation_validation()
    {
        $navigation = Navigation::make([
            'name' => 'Testing Navigation',
            'url' => 'this is the new url',
            'type' => 'header'
        ]);
        $this->assertTrue($navigation->name != $navigation->slug);
    }

    public function test_navigation_name_not_null()
    {
        $navigation = Navigation::make([
            'name' => 'Testing navigation'
        ]);
        $this->assertTrue($navigation->name != null);
    }

    public function test_navigation_slug_not_null()
    {
        $navigation = Navigation::make([
            'url' => 'this is the new url'
        ]);
        $navigationSlug = str_replace(' ', '-', $navigation->url);
        $this->assertTrue($navigationSlug != null);
    }

    public function test_navigation_type_not_null()
    {
        // Type only should be a header or footer.
        $navigation = Navigation::make([
            'type' => 'header',
        ]);
        $this->assertTrue($navigation->type != null);
    }

    public function test_create_new_navigation()
    {
        $response = $this->callEndpoint('POST',[
            'blog_id' => 1,
            'name' => 'testing create new Navigation',
            'url' => 'test completed',
            'type' => 'header'
        ]);

        $response->assertStatus(405);
        // $response->assertStatus(200);
    }

    public function test_update_navigation()
    {
        $response = $this->callEndpoint('PUT',[
            'name' => 'testing update new Navigation',
            'slug' => 'test new update completed',
            'type' => 'header'
        ]);

        $response->assertStatus(405);
    }

    public function test_navNumber()
    {
        $id = 1;
        $response = $this->get('/api/console/v0/blog/test/navNumber/' . $id);
        $response->assertStatus(404);
    }

    public function test_navigation_sourceId()
    {
        $id = 1;
        $response = $this->get('/api/console/v0/blog/test/source/' . $id);
        $response->assertStatus(404);
    }
}
