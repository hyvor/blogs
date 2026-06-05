<?php

namespace App\Tests\Api\Data;

use App\Api\Data\Controller\BlogController;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\LanguageFactory;
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
        $this->assertArrayHasKey('name', $json);
    }

    public function test_does_not_fetch_invalid_blogs(): void
    {
        $this->dataApi('nonexistent-subdomain-12345', '/blog');
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_fetches_blog_with_correct_primary_language(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage(languageAttrs: ['code' => 'es']);
        $variant = BlogVariantFactory::createOneForBlog($blog);

        $this->dataApi($blog, '/blog', ['language' => 'es']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('es', $json['languages'][0]['code']);
        $this->assertSame($variant->getName(), $json['name']);
    }

    public function test_fetches_blog_with_correct_non_primary_language(): void
    {
        $blog = BlogFactory::createOneWithPrimaryLanguage(languageAttrs: ['code' => 'en']);
        LanguageFactory::createOneFor($blog, ['code' => 'fr', 'name' => 'French']);
        $variants = BlogVariantFactory::createManyForBlogWithAllLanguages($blog);

        $this->dataApi($blog, '/blog', ['language' => 'fr']);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($variants[1]->getName(), $json['name']);
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
