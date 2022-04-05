<?php
namespace Tests\Feature\CliAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CreateUUIDTest extends TestCase
{

    use RefreshDatabase;

    public function test_create_uuid()
    {
        $response = $this->callCliAPI('post', '/new');
        
        $response->assertStatus(200)
            ->assertJson(function ($json) {
                $json->whereType('uuid', 'string');
            });
    }

}