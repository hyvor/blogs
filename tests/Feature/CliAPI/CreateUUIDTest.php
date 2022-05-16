<?php

namespace Tests\Feature\CliAPI;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUUIDTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_uuid()
    {
        $response = $this->callCliAPI('post', '/new');

        $response->assertOk()
            ->assertJson(function ($json) {
                $json->whereType('uuid', 'string');
            });
    }
}
