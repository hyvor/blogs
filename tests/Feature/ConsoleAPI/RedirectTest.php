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
        // $response = $this->GET('http://blogs.hyvor.test/api/console/v0/blog/test/redirect');
        // $response->assertStatus(200);

        $response = $this->GET('http://blogs.hyvor.test/console/test/settings/redirects');
        $response->assertStatus(200);

    }

    public function test_redirect_validation()
    {
        $redirect = Redirect::make([
            'old_url' => 'Testing redirects',
            'new_url' => 'this is the new url'
        ]);
        $this->assertTrue($redirect->old_url != $redirect->new_url);
    }

    // never touch
    public function test_delete_redirect()
    {
        $response = $this->DELETE('http://blogs.hyvor.test/console/test/settings/redirects/51');
        $response->assertStatus(405);
    }

    public function test_create_new_redirects()
    {
        $response = $this->post('http://blogs.hyvor.test/console/test/settings/redirects/',[
            'old_url' => 'testing create new redirect',
            'new_url' => 'test completed',
            'type' => 301
        ]);

        $response->assertStatus(405);
    }

    public function test_redirects_update()
    {
        $response = $this->put('http://blogs.hyvor.test/console/test/settings/redirects/51',[
            'old_url' => 'testing update new redirect',
            'new_url' => 'test new update completed',
            'type' => 301
        ]);

        $response->assertStatus(405);
    }

    // this is to check whether the database is linked properly.
    // public function test_redirect_database(){
    //     $this->assertDatabaseHas('redirects', [
    //         'new_url' => 'done',
    //     ]);
    // }

}
