<?php

use App\Api\Delivery\DeliveryController;
use App\Entity\Enum\ThemeFileFolder;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeliveryController::class)]
class BlogOnSubdirectoryTest extends ApiTestCase
{   

    public function test_blog_on_subdirectory(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes();
        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => '<body>Testing</body>',
        ]);

        $this->client->request('GET', '/blog/' . $blog->getSubdomain() . '/');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('<body>Testing</body>', (string) $this->client->getResponse()->getContent());
    }

}