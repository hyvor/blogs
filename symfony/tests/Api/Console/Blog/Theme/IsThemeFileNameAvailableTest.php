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
class IsThemeFileNameAvailableTest extends ApiTestCase
{
    public function test_name_is_available(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-name-available'],
            [],
        );

        $this->consoleBlogApi(
            'GET',
            $blog,
            '/theme/file/name-available?name=index.twig&folder=templates',
            user: $user,
        );

        $this->assertResponseIsSuccessful();
        $this->assertTrue($this->getJson()['available']);
    }

    public function test_name_is_not_available(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-name-not-available'],
            [],
        );

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => 'none',
        ]);

        $this->consoleBlogApi(
            'GET',
            $blog,
            '/theme/file/name-available?name=index.twig&folder=templates',
            user: $user,
        );

        $this->assertResponseIsSuccessful();
        $this->assertFalse($this->getJson()['available']);
    }
}
