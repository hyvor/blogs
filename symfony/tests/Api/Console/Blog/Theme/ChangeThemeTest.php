<?php

namespace App\Tests\Api\Console\Blog\Theme;

use App\Api\Console\Controller\ThemeController;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\ThemeFilesService;
use App\Service\Theme\ThemeZipService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFactory;
use App\Tests\Factory\ThemeFileFactory;
use App\Tests\Factory\ThemeVersionFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ThemeController::class)]
#[CoversClass(ThemeFilesService::class)]
#[CoversClass(ThemeZipService::class)]
class ChangeThemeTest extends ApiTestCase
{
    public function test_changes_the_theme_of_a_blog_with_the_latest_version(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-change'],
            [],
        );

        $theme = ThemeFactory::createOne(['name' => 'sample', 'type' => 'original']);
        ThemeVersionFactory::createOne([
            'theme' => $theme,
            'version' => '1.0.0',
            'zip' => $this->makeZip(['config.yaml' => "THEME_VERSION=1.0.0\n"]),
        ]);
        ThemeVersionFactory::createOne([
            'theme' => $theme,
            'version' => '2.0.0',
            'zip' => $this->makeZip(['config.yaml' => "THEME_VERSION=2.0.0\n"]),
        ]);

        ThemeFileFactory::createOne(['blog' => $blog, 'name' => 'hello.twig', 'folder' => ThemeFileFolder::TEMPLATES]);

        $this->consoleBlogApi('PATCH', $blog, '/theme', ['name' => 'sample'], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();

        $configFile = null;
        $helloFile = null;
        foreach ($json as $file) {
            if ($file['name'] === 'config.yaml') {
                $configFile = $file;
            }
            if ($file['name'] === 'hello.twig') {
                $helloFile = $file;
            }
        }

        $this->assertNotNull($configFile);
        $this->assertStringContainsString('2.0.0', (string) $configFile['content']);
        $this->assertNull($helloFile);
    }

    public function test_theme_not_found(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-change-404'],
            [],
        );

        $this->consoleBlogApi('PATCH', $blog, '/theme', ['name' => 'does-not-exist'], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertSame([], $this->getJson());
    }

    /**
     * @param array<string, string> $files
     */
    private function makeZip(array $files): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'zip-fixture-');
        $zip = new \ZipArchive();
        $zip->open((string) $tmp, \ZipArchive::OVERWRITE);
        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
        $content = (string) file_get_contents((string) $tmp);
        unlink((string) $tmp);
        return $content;
    }
}
