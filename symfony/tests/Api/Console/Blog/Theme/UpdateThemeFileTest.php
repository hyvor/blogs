<?php

namespace App\Tests\Api\Console\Blog\Theme;

use App\Api\Console\Controller\ThemeController;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\Event\ConfigEditedEvent;
use App\Service\Theme\Event\StylesEditedEvent;
use App\Service\Theme\Event\TemplateEditedEvent;
use App\Service\Theme\ThemeFilesService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ThemeController::class)]
#[CoversClass(ThemeFilesService::class)]
class UpdateThemeFileTest extends ApiTestCase
{
    public function test_updates_name(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-update-name'],
            [],
        );

        $file = ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => 'none',
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/theme/file/' . $file->getId(), [
            'name' => 'new.twig',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('new.twig', $this->getJson()['name']);
    }

    public function test_updates_content(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-update-content'],
            [],
        );

        $file = ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => 'none',
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/theme/file/' . $file->getId(), [
            'content' => 'new',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('new', $this->getJson()['content']);
        $this->getEd()->assertDispatched(TemplateEditedEvent::class);
    }

    public function test_updates_a_style_file(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-update-style'],
            [],
        );

        $file = ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::STYLES,
            'name' => 'index.scss',
            'content' => 'none',
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/theme/file/' . $file->getId(), [
            'content' => 'new',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('new', $this->getJson()['content']);
        $this->getEd()->assertDispatched(StylesEditedEvent::class);
    }

    public function test_updates_content_to_empty(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-update-empty'],
            [],
        );

        $file = ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => 'none',
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/theme/file/' . $file->getId(), [
            'content' => '',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertNull($this->getJson()['content']);
    }

    public function test_updates_config_with_event(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-update-config'],
            [],
        );

        $file = ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => null,
            'name' => 'config.yaml',
            'content' => 'none',
        ]);

        $this->consoleBlogApi('PATCH', $blog, '/theme/file/' . $file->getId(), [
            'content' => '',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->getEd()->assertDispatched(ConfigEditedEvent::class);
    }
}
