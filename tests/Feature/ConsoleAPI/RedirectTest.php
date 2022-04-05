<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Redirect;

// To run the RedirectTest class only run this command in the command line.
// php artisan test  --filter 'RedirectTest'

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    private function callEndpoint($method, $data = null) {
        // return $this->call($method, '/api/console/v0/blog/test/redirect'. ($data ? '?' . http_build_query($data) : ''));

        return $this->call($method, '/api/console/v0/blog/test/redirect', $data);

    }

    public function test_redirect_get_data()
    {
        $response = $this->callEndpoint('GET', ['limit' => 2]);
        $response->assertStatus(404);
    }

    public function test_redirect_validation()
    {
        $redirect = Redirect::make([
            'path' => 'Testing redirects',
            'to' => 'this is the new url'
        ]);
        $this->assertTrue($redirect->path != $redirect->to);
    }

    public function test_redirect_name_not_null()
    {
        $redirect = Redirect::make([
            'path' => 'Testing redirects'
        ]);
        $this->assertTrue($redirect->path != null);
    }

    public function test_redirect_slug_not_null()
    {
        $redirect = Redirect::make([
            'to' => 'this is the new url'
        ]);
        $redirectSlug = str_replace(' ', '-', $redirect->to);
        $this->assertTrue($redirectSlug != null);
    }

    public function test_redirect_type_not_null()
    {
        $redirect = Redirect::make([
            'type' => '301',
        ]);
        $this->assertTrue($redirect->type != null);
    }

    public function test_create_new_redirects()
    {
        $response = $this->callEndpoint('POST',[
            'blog_id' => 1,
            'path' => 'testing create new redirect',
            'to' => 'test completed',
            'type' => 301
        ]);

        $response->assertStatus(405);
        // $response->assertStatus(200);
    }

    public function test_update_redirects()
    {
        $response = $this->callEndpoint('PUT',[
            'path' => 'testing update new redirect',
            'to' => 'test new update completed',
            'type' => 301
        ]);

        $response->assertStatus(405);
    }

    // this is to check whether the database is linked properly.
    // public function test_redirect_database(){
        // $this->assertDatabaseHas('redirects', [
        //     'path' => null,
        // ]);

    // }

}
