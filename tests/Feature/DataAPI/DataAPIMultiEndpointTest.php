<?php

namespace Tests\Feature\DataAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DataAPIMultiEndpointTest extends TestCase {

    use RefreshDatabase;

    private function callEndpoint($data = null) {
        return $this->get('/api/data/v0/blog/test/posts' . ($data ? '?' . http_build_query($data) : ''));
    }
 
    // limit
    public function testLimit() {
        $response = $this->callEndpoint([
            'limit' => 4
        ]);
        $response
            ->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    // page (offset in SQL)
    public function testPage() {
        $response1 = $this->callEndpoint(['limit' => 2])->json();
        $response2 = $this->callEndpoint(['limit' => 1, 'page' => 2])->json();

        $this->assertEquals(
            $response1['data'][1],
            $response2['data'][0]
        );
    }
    
    public function testKeyNormal() {
        $response = $this->callEndpoint(['keys' => 'id']);

        $response->assertJson(function (AssertableJson $json) {
            $json->has('data.0', function ($json) {
                $json->has('id');
            })
            ->has('count');
        });
    }

    public function testKeyMultiple() {
        $response = $this->callEndpoint(['keys' => 'id,slug']);

        $response->assertJson(function (AssertableJson $json) {
            $json->has('data.0', function ($json) {
                $json->has('id')
                    ->has('slug');
            })
            ->has('count');
        });
    }

    public function testKeyNested() {
        $response = $this->callEndpoint(['keys' => 'tags.id']);

        $response->assertJson(function (AssertableJson $json) {
            $json->has('data.0.tags.0', function ($json) {
                $json->has('id');
            })
            ->has('count');
        });
    }

    public function testKeysMulti() {

        $response = $this->callEndpoint(['keys' => 'tags']);

        $response->assertJson(function (AssertableJson $json) {
            $json->has('data.0.tags.0', function ($json) {
                $json->has('id')
                    ->etc();
            })
            ->has('count');
        });

    }

    public function testKeysExclude() {

        $response = $this->callEndpoint(['keys' => '!id']);

        $response->assertJson(function (AssertableJson $json) {
            $json->has('data.0', function ($json) {
                $json->missing('id')
                    ->has('tags')
                    ->etc();
            })
            ->has('count');
        });
    
    }

    public function testKeysExcludeNested() {

        $response = $this->callEndpoint(['keys' => '!tags']);

        $response->assertJson(function (AssertableJson $json) {
            $json->has('data.0', function ($json) {
                $json->missing('tags')
                    ->has('id')
                    ->etc();
            })
            ->has('count');
        });
    
    }


}