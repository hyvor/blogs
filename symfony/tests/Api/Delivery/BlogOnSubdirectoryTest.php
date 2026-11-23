<?php

namespace App\Tests\Api\Delivery;

use App\Api\Delivery\AppDeliveryController;
use App\Entity\Enum\ThemeFileFolder;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AppDeliveryController::class)]
class BlogOnSubdirectoryTest extends ApiTestCase
{
    public function test_blog_on_subdirectory(): void
    {
        $this->setEnvVar('DELIVERY_URL', '');

        $blog = BlogFactory::createOneWithLanguageAndRoutes([
        ]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => '<body>Testing</body>',
        ]);

        $this->client->request('GET', '/blog/' . $blog->getSubdomain());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('<body>Testing</body>', (string) $this->client->getResponse()->getContent());
    }

    public function test_when_blog_not_found(): void
    {
        $this->client->request('GET', '/blog/nonexistent-blog');
        $this->assertResponseStatusCodeSame(404);
    }

    public function test_redirects_to_subdomain_if_delivery_url_is_set(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(['subdomain' => 'supun']);
        $this->client->request('GET', '/blog/' . $blog->getSubdomain());
        $this->assertResponseRedirects('https://supun.hyvorblogs.io', 302);
    }

    public function test_redirects_to_hosting_url(): void
    {
        $this->setEnvVar('DELIVERY_URL', '');
        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'subdomain' => 'supun',
            'hosting_url' => 'https://example.com/blog',
        ]);

        $this->client->request('GET', '/blog/' . $blog->getSubdomain() . '/hello');

        $this->assertResponseRedirects('https://example.com/blog/hello', 302);
    }

    public function test_does_not_redirect_to_hosting_url_when_disabled(): void
    {
        $this->setEnvVar('DELIVERY_URL', '');
        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'hosting_url' => 'https://example.com/blog',
            'hosting_redirect_subdomain' => false,
        ]);
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => '<body>Testing</body>',
        ]);

        $this->client->request('GET', '/blog/' . $blog->getSubdomain());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('<body>Testing</body>', (string) $this->client->getResponse()->getContent());
    }

    public function test_redirects_to_subdomain_not_hosting_url_if_delivery_url_is_set(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes([
            'subdomain' => 'supun',
            'hosting_url' => 'https://example.com/blog',
        ]);

        $this->client->request('GET', '/blog/' . $blog->getSubdomain() . '/hello');

        $this->assertResponseRedirects('https://supun.hyvorblogs.io/hello', 302);
    }

}
