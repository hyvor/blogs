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
class DownloadThemeTest extends ApiTestCase
{
    public function test_downloads_the_theme_as_a_zip(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-download'],
            [],
        );

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => 'Hi',
        ]);

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => null,
            'name' => 'config.yaml',
            'content' => '',
        ]);

        $response = $this->consoleBlogApi('GET', $blog, '/theme/download', user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame('application/zip', $response->headers->get('Content-Type'));

        $tmp = tempnam(sys_get_temp_dir(), 'theme-download-');
        file_put_contents((string) $tmp, (string) $response->getContent());

        $zip = new \ZipArchive();
        $zip->open((string) $tmp);

        $this->assertSame('Hi', $zip->getFromName('templates/index.twig'));
        $this->assertSame('', $zip->getFromName('config.yaml'));

        $zip->close();
        unlink((string) $tmp);
    }

    public function test_throws_when_no_theme_files(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-download'],
        );

        $response = $this->consoleBlogApi('GET', $blog, '/theme/download', user: $user);

        $this->assertResponseFailed(422, 'No theme files found to export');
    }
}
