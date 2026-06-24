<?php

namespace App\Tests\Api\Console\Blog\Theme;

use App\Api\Console\Controller\ThemeController;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\ThemeFilesService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ThemeController::class)]
#[CoversClass(ThemeFilesService::class)]
class GetThemeFilesTest extends ApiTestCase
{
    public function test_gets_files(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-get-files'],
            [],
        );

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => 'none',
        ]);

        $this->consoleBlogApi('GET', $blog, '/theme/files', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertSame('index.twig', $json[0]['name']);
    }
}
