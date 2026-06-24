<?php

namespace App\Tests\Api\Console\Blog\Theme;

use App\Api\Console\Controller\ThemeController;
use App\Service\Theme\ThemeFilesService;
use App\Service\Theme\ThemeZipService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(ThemeController::class)]
#[CoversClass(ThemeFilesService::class)]
#[CoversClass(ThemeZipService::class)]
class UploadThemeTest extends ApiTestCase
{
    public function test_uploads_a_theme(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-upload'],
            [],
        );

        $tmp = tempnam(sys_get_temp_dir(), 'theme-upload-');
        $zip = new \ZipArchive();
        $zip->open((string) $tmp, \ZipArchive::OVERWRITE);
        $zip->addFromString('templates/@base.twig', 'base');
        $zip->addFromString('templates/index.twig', 'index');
        $zip->addFromString('templates/no.php', 'not allowed');
        $zip->addFromString('styles/index.scss', 'body {}');
        $zip->addFromString('styles/other.txt', 'not allowed');
        $zip->addFromString('config.yaml', 'THEME_NAME=sample');
        $zip->close();

        $uploadedFile = new UploadedFile((string) $tmp, 'sample-theme.zip', 'application/zip', test: true);

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/theme',
            files: ['zip' => $uploadedFile],
            user: $user,
        );

        $this->assertResponseIsSuccessful();
        $files = $this->getJson();

        $names = array_map(fn($f) => $f['name'], $files);
        $this->assertContains('@base.twig', $names);
        $this->assertContains('index.twig', $names);
        $this->assertNotContains('no.php', $names);
        $this->assertContains('index.scss', $names);
        $this->assertNotContains('other.txt', $names);

        $configFile = null;
        foreach ($files as $f) {
            if ($f['name'] === 'config.yaml') {
                $configFile = $f;
            }
        }
        $this->assertNotNull($configFile);
        $this->assertSame('THEME_NAME=sample', $configFile['content']);

        unlink((string) $tmp);
    }
}
