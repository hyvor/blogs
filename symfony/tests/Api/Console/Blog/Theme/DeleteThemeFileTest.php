<?php

namespace App\Tests\Api\Console\Blog\Theme;

use App\Api\Console\Controller\ThemeController;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\ThemeFile;
use App\Service\Theme\ThemeFilesService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ThemeController::class)]
#[CoversClass(ThemeFilesService::class)]
class DeleteThemeFileTest extends ApiTestCase
{
    public function test_deletes_a_file(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-delete'],
            [],
        );

        $file = ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => 'none',
        ]);
        $fileId = $file->getId();

        $this->consoleBlogApi('DELETE', $blog, '/theme/file/' . $fileId, user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertNull($this->getEm()->getRepository(ThemeFile::class)->find($fileId));
    }
}
