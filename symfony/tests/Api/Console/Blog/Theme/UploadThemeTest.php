<?php

namespace App\Tests\Api\Console\Blog\Theme;

use App\Api\Console\Controller\ThemeController;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Theme\ThemeFilesService;
use App\Service\Theme\ThemeImporter;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(ThemeController::class)]
#[CoversClass(ThemeFilesService::class)]
#[CoversClass(ThemeImporter::class)]
class UploadThemeTest extends ApiTestCase
{
    /** @param array<string, string> $files */
    private function makeZip(array $files): string
    {
        $tmp = (string) tempnam(sys_get_temp_dir(), 'theme-upload-');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::OVERWRITE);
        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
        return $tmp;
    }

    private function makeUploadedZip(string $path): UploadedFile
    {
        return new UploadedFile($path, 'theme.zip', 'application/zip', test: true);
    }

    public function test_requires_zip_field(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $this->consoleBlogApi('POST', $blog, '/theme', user: $user);

        $this->assertResponseFailed(422, 'zip field is required');
    }

    public function test_rejects_invalid_zip_content(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $tmp = (string) tempnam(sys_get_temp_dir(), 'theme-bad-');
        file_put_contents($tmp, 'this is not a zip file');
        $uploadedFile = new UploadedFile($tmp, 'bad.zip', 'application/zip', test: true);

        $this->consoleBlogApi('POST', $blog, '/theme', files: ['zip' => $uploadedFile], user: $user);

        $this->assertResponseFailed(422, 'Unable to open the zip file');

        unlink($tmp);
    }

    public function test_uploads_a_theme(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-upload'],
            [],
        );

        $tmp = $this->makeZip([
            'templates/@base.twig' => 'base',
            'templates/index.twig' => 'index',
            'templates/no.php'     => 'not allowed',
            'styles/index.scss'    => 'body {}',
            'styles/other.txt'     => 'not allowed',
            'config.yaml'          => 'THEME_NAME=sample',
        ]);

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/theme',
            files: ['zip' => $this->makeUploadedZip($tmp)],
            user: $user,
        );

        $this->assertResponseIsSuccessful();
        $response = $this->getJson();

        $this->assertArrayHasKey('files', $response);
        $this->assertArrayHasKey('logs', $response);

        $files = $response['files'];
        $this->assertIsArray($files);
        $names = [];
        foreach ($files as $f) {
            $this->assertIsArray($f);
            $names[] = $f['name'];
        }

        $this->assertContains('@base.twig', $names);
        $this->assertContains('index.twig', $names);
        $this->assertNotContains('no.php', $names);
        $this->assertContains('index.scss', $names);
        $this->assertNotContains('other.txt', $names);

        $configFile = null;
        foreach ($files as $f) {
            $this->assertIsArray($f);
            if ($f['name'] === 'config.yaml') {
                $configFile = $f;
            }
        }
        $this->assertNotNull($configFile);
        $this->assertSame('THEME_NAME=sample', $configFile['content']);

        unlink($tmp);
    }

    public function test_logs_contain_skipped_entries(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        $tmp = $this->makeZip([
            'templates/valid.twig' => 'ok',
            'templates/bad.php'    => 'not allowed',
            'unknown/file.txt'     => 'bad folder',
        ]);

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/theme',
            files: ['zip' => $this->makeUploadedZip($tmp)],
            user: $user,
        );

        $this->assertResponseIsSuccessful();
        $response = $this->getJson();

        $logs = $response['logs'];
        $this->assertIsArray($logs);
        $this->assertNotEmpty($logs);

        $logStrings = [];
        foreach ($logs as $log) {
            $this->assertIsString($log);
            $logStrings[] = $log;
        }
        $logsStr = implode(' ', $logStrings);
        $this->assertStringContainsString('bad.php', $logsStr);
        $this->assertStringContainsString('skipped', $logsStr);

        unlink($tmp);
    }

    public function test_replaces_existing_files_on_upload(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();

        // Seed an existing file directly via the service
        /** @var ThemeFilesService $themeFilesService */
        $themeFilesService = $this->getContainer()->get(ThemeFilesService::class);
        $themeFilesService->createOrUpdateFile($blog, ThemeFileFolder::TEMPLATES, 'index.twig', 'old content');

        // Upload a new zip — should replace all existing files
        $tmp = $this->makeZip(['templates/other.twig' => 'new file']);
        $this->consoleBlogApi('POST', $blog, '/theme', files: ['zip' => $this->makeUploadedZip($tmp)], user: $user);
        $this->assertResponseIsSuccessful();
        unlink($tmp);

        $files = $this->getJson()['files'];
        $this->assertIsArray($files);
        $names = [];
        foreach ($files as $f) {
            $this->assertIsArray($f);
            $names[] = $f['name'];
        }

        $this->assertContains('other.twig', $names);
        $this->assertNotContains('index.twig', $names);
    }
}
