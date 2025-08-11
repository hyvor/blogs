<?php

namespace Tests\Feature\DeliveryApi;

use Database\Factories\BlogFactory;
use Tests\Case\DatabaseTestCase;

class CustomDomainHttpRedirectTest extends DatabaseTestCase
{
    public function test_custom_domain_with_http(): void
    {
        BlogFactory::withLanguageAndRoutes(
            [
                'hosting_domain' => 'example.com',
            ]
        );

        $response = $this->get('http://example.com/');
        $response->assertStatus(302);
        $response->assertRedirect('https://example.com/');
    }
}
