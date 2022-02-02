<?php

namespace Tests\Feature\DataAPI;

use App\Exceptions\TrustedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DataAPIPostsTest extends TestCase
{

    use RefreshDatabase;

    private function callEndpoint($data = null) {
        return $this->get('/api/data/v0/blog/test/posts' . ($data ? '?' . http_build_query($data) : ''));
    }

    public function testPostsWithoutParams()
    {
        $response = $this->callEndpoint();
        $response->assertStatus(200);
    }

    public function testPostsInvalid() {

        $response = $this->callEndpoint([
            'sort' => 'something_invalid'
        ]);

        $response->assertStatus(400);

    }

    public function testPostsSortByPublishedAt() 
    {
        $response = $this->callEndpoint([
            'sort' => 'published_at',
            'limit' => 3
        ])->json();

        $this->assertTrue(
            $response['data'][0]['published_at'] >= $response['data'][1]['published_at'] &&
            $response['data'][1]['published_at'] >= $response['data'][2]['published_at']
        );
    }

    public function testPostsSortByPublishedAtAsc() 
    {
        $response = $this->callEndpoint([
            'sort' => 'published_at ASC',
            'limit' => 3
        ])->json();

        $this->assertTrue(
            $response['data'][0]['published_at'] <= $response['data'][1]['published_at'] &&
            $response['data'][1]['published_at'] <= $response['data'][2]['published_at']
        );
    }

    public function testFilterById() {
        $response = $this->callEndpoint([
            'filter' => 'id=1'
        ]);
        $response
            ->assertJsonPath('data.0.id', 1)
            ->assertJsonCount(1, 'data');
    }


}
