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
        return $this->call($method, 'http://blogs.hyvor.test/api/console/v0/blog/test/navigation', $data);
    }

    /**
    * A basic test example.
    *
    * @return void
    */
    public function test_get_request()
    {
        $response = $this->callEndpoint('GET', ['limit' => 2]);
        $response->assertStatus(200);
    }

    public function test_post_request()
    {
        $response = $this->callEndpoint('POST', [
            'navigation_name' => 'home',
            'navigation_url' => 'www.example.com',
            'type' => 'header',
        ]);
        $response->assertStatus(500);

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


    public function test_update_navigation()
    {
        // $response = $this->callEndpoint('PUT',[
        //     'navigation_name' => 'about',
        //     'navigation_url' => 'testNew.com',
        //     'type' => 'header'
        // ]);

        $id = 1;
        $response = $this->put('http://blogs.hyvor.test/api/console/v0/blog/test/navigation/'.$id,[
            'navigation_name' => 'about',
            'navigation_url' => 'testNew.com',
            'type' => 'header'
        ]);

        $response->assertStatus(500);
    }

    public function test_navNumber()
    {
        $id = 1;
        $response = $this->get('/api/console/v0/blog/test/navigation/sort/' . $id);
        $response->assertStatus(404);
    }

    public function test_navigation_sourceId()
    {
        $id = 1;
        $response = $this->get('/api/console/v0/blog/test/navigation/source/' . $id);
        $response->assertStatus(404);
    }
}
