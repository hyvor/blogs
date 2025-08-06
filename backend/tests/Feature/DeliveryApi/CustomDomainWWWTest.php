<?php

namespace Tests\Feature\DeliveryApi;

use Database\Factories\BlogFactory;
use Tests\Case\DatabaseTestCase;

class CustomDomainWWWTest extends DatabaseTestCase
{
    public function test_custom_domain_without_www(): void
    {
        BlogFactory::withLanguageAndRoutes(
            [
                'hosting_domain' => 'example.com',
            ]
        );

        $response = $this->get('https://www.example.com/');
        $response->assertStatus(302);
        $response->assertRedirect('https://example.com/');
    }

    public function test_custom_domain_with_www(): void
    {
        BlogFactory::withLanguageAndRoutes(
            [
                'hosting_domain' => 'www.example.com',
            ]
        );

        $response = $this->get('https://example.com/');
        $response->assertStatus(302);
        $response->assertSee('www.example.com');
    }
}
