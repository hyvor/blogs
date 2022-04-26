<?php

namespace Tests\Feature\ConsoleAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use App\Models\Blog;

// To run the BlogTest class only run this command in the command line.
// php artisan test  --filter 'BlogTest'

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function callEndpoint($method, $blog, $data = null) {
        return $this->call($method, 'http://blogs.hyvor.test/api/console/v0/blog/test/'.$blog , $data);
    }

    /**
    * A basic test example.
    *
    * @return void
    */
    public function test_get_request()
    {
        $response = $this->callEndpoint('GET', 'blogData', []);
        $response->assertOk();
    }

    public function test_createVariant_request()
    {
        $response = $this->callEndpoint('POST', 'blogVariant', [
            'languageId' => 2,
        ]);
        $response->assertOk();
    }

    public function test_put_request()
    {
        $response = $this->callEndpoint('PUT', 'blog', [
            'subdomain' => 'test',
            'icon' => null,
            'featureImageId' => null,
            'social_facebook' => null,
            'social_twitter' => null,
            'social_linkedin' => null,
            'social_youtube' => null,
            'social_instagram' =>  null,
            'name' => 'test user',
            'description' =>  null,
        ]);
        $response->assertOk();
    }

}
