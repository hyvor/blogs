<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Redirect;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_data()
    {
        $response = $this->get('http://blogs.hyvor.test/console/test/settings/redirect');
        $response->assertStatus(200); 
        // /api/console/v0/blog/test/redirect
        // $this->json('get', 'http://blogs.hyvor.test/api/console/v0/blog/supun/redirect')
        // ->assertStatus(200)
        // ->assertJsonStructure(
        //     [
        //         "old_url" => "hello world",
        //         "new_url" => "yes hello world testing",
        //         "type" => "301"
                
        //     ]
        // );
    }

    // public function test_redirect_validation()
    // {
    //     $redirect = Redirect::make([
    //         'old_url' => 'Testing redirects',
    //         'new_url' => 'this is the new url'
    //     ]);
    //     $this->assertTrue($redirect->old_url != $redirect->new_url);
    // }

    // never touch
    // public function test_delete_redirect()
    // {
    //     $redirect = Redirect::factory()->count(1)->make();
    //     $redirect = Redirect::first();
    //     if($redirect){
    //         $redirect->delete();
    //     }
    //     $this->assertTrue(true);
    // }

    // public function test_create_new_redirects()
    // {
    //     $response = $this->post('/api/console/v0/blog/test/redirects',[
    //         'old_url' => 'testing create new redirect',
    //         'new_url' => 'test completed',
    //         'type' => 301
    //     ]);

    //     $response->assertStatus(404);
    // }

    // public function test_redirects_update()
    // {
    //     $response = $this->put('http://blogs.hyvor.test/console/supun/settings/redirects');

    //     $response->assertStatus(200);
    // }

    // this is to check whether the database is linked properly.
    // public function test_redirect_database(){
    //     $this->assertDatabaseHas('redirects', [
    //         'new_url' => 'done',
    //     ]);
    // }

}
