<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\BlogController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BlogController::class)]
class BlogTest extends ApiTestCase
{
    public function test_fetches_blog(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        BlogVariantFactory::createOneForBlog($blog);

        $this->dataApi($blog, '/blog');

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('subdomain', $json);
    }

    public function test_does_not_fetch_invalid_blogs(): void
    {
        $this->dataApi('nonexistent-subdomain-12345', '/blog');
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_fetches_blog_with_correct_language(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();
        BlogVariantFactory::createOneForBlog($blog);

        $this->dataApi($blog, '/blog', ['language' => 'en']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('en', $json['languages'][0]['code']);
    }

    public function test_filters_key(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage();

        $this->dataApi($blog, '/blog', ['keys' => 'subdomain']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertArrayHasKey('subdomain', $json);
        $this->assertArrayNotHasKey('name', $json);
    }
}
