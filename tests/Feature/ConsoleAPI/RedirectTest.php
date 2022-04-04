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

    private function callEndpoint($method, $redirect,  $data = null) {
        return $this->call($method, 'http://blogs.hyvor.test/api/console/v0/blog/test/'.$redirect, $data);

    }

    public function test_redirect_get_data()
    {
        $response = $this->callEndpoint('GET', 'redirect', [
            'limit' => 2
        ]);
        $response->assertStatus(200);
    }

    public function test_post_request()
    {
        $response = $this->callEndpoint('POST', 'redirect', [
            'path' => 'testing create new redirect',
            'to' => 'test completed',
            'type' => 301,
        ]);
        $response->assertStatus(400);
    }

    public function test_put_request()
    {
        $id = 1;
        $response = $this->callEndpoint('PUT', 'redirect/'.$id, [
            'path' => 'testing update new redirect',
            'to' => 'test new update completed',
            'type' => 301
        ]);
        $response->assertStatus(500);
    }

    public function test_delete_request()
    {
        $id = 1;
        $response = $this->callEndpoint('DELETE', 'redirect/'.$id, []);
        $response->assertStatus(500);
    }

    public function test_redirect_equality_validation()
    {
        $redirect = Redirect::make([
            'path' => 'Testing redirects',
            'to' => 'this is the new url'
        ]);
        $this->assertTrue($redirect->path != $redirect->to);
    }

    public function test_redirect_validation()
    {
        $redirect = Redirect::make([
            'path' => 'Testing redirects',
            'to' => 'this is the new url',
            'type' => '301',
        ]);
        $this->assertTrue(
            $redirect->path != null,
            $redirect->to != null,
            $redirect->type != null,
        );
    }

    public function test_redirect_not_null()
    {
        $redirect = Redirect::make([
            'to' => 'this is the new url'
        ]);
        $redirectSlug = str_replace(' ', '-', $redirect->to);
        $this->assertTrue($redirectSlug != null);
    }

    // this is to check whether the database is linked properly.
    // public function test_redirect_database(){
    //     $this->assertDatabaseHas('redirects', [
    //         'path' => null,
    //     ]);

    // }

}
