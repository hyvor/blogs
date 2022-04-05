<?php

namespace Tests\Feature\DataAPI;

use App\Exceptions\TrustedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PostsTest extends TestCase
{

    use RefreshDatabase;

    public function test_posts_without_params()
    {
        $response = $this->callDataApi('/posts');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('data')
                    ->has('pagination');
            });
    }

    public function test_posts_invalid() {

        $response = $this->callDataApi('/posts', [
            'sort' => 'something_invalid'
        ]);

        $response->assertStatus(400);

    }

    public function test_posts_sort_published_at_desc() 
    {
        $response = $this->callDataApi('/posts', [
            'sort' => 'published_at',
            'limit' => 3
        ])->json();

        $this->assertTrue(
            $response['data'][0]['published_at'] >= $response['data'][1]['published_at'] &&
            $response['data'][1]['published_at'] >= $response['data'][2]['published_at']
        );
    }

    public function test_posts_sort_published_at_asc() 
    {
        $response = $this->callDataApi('/posts', [
            'sort' => 'published_at ASC',
            'limit' => 3
        ])->json();

        $this->assertTrue(
            $response['data'][0]['published_at'] <= $response['data'][1]['published_at'] &&
            $response['data'][1]['published_at'] <= $response['data'][2]['published_at']
        );
    }

    public function test_posts_filter_by() {
        $response = $this->callDataApi('/posts', [
            'filter' => 'id=1'
        ]);
        $response
            ->assertJsonPath('data.0.id', 1)
            ->assertJsonCount(1, 'data');
    }

}
