<?php

namespace App\Tests\Api\Console\Blog\Theme;

use App\Api\Console\Controller\ThemeController;
use App\Entity\Enum\ThemeFileFolder;
use App\Service\Limit;
use App\Service\Theme\Event\AssetEditedEvent;
use App\Service\Theme\ThemeFilesService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\ThemeFileFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(ThemeController::class)]
#[CoversClass(ThemeFilesService::class)]
#[CoversClass(AssetEditedEvent::class)]
class CreateThemeFileTest extends ApiTestCase
{
    public function test_creates_a_file(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-create'],
            [],
        );

        $this->consoleBlogApi('POST', $blog, '/theme/file', [
            'folder' => 'templates',
            'name' => 'index.twig',
            'content' => 'test',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('index.twig', $json['name']);
        $this->assertSame('test', $json['content']);
    }

    public function test_creates_a_file_from_blob(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-create-blob'],
            [],
        );

        $tmp = tempnam(sys_get_temp_dir(), 'asset-');
        file_put_contents((string) $tmp, 'binary-content');
        $file = new UploadedFile((string) $tmp, 'test.jpg', 'image/jpeg', test: true);

        $this->consoleBlogApi('POST', $blog, '/theme/file', [
            'folder' => 'assets',
            'name' => 'test.jpg',
        ], files: ['file' => $file], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('test.jpg', $json['name']);

        $this->getEd()->assertDispatched(AssetEditedEvent::class);

        unlink((string) $tmp);
    }

    public function test_validates_file_size(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-too-large'],
            [],
        );

        $tmp = tempnam(sys_get_temp_dir(), 'asset-large-');
        file_put_contents((string) $tmp, str_repeat('a', Limit::MAX_ASSET_FILE_SIZE + 1));
        $file = new UploadedFile((string) $tmp, 'test.jpg', 'image/jpeg', test: true);

        $this->consoleBlogApi('POST', $blog, '/theme/file', [
            'folder' => 'assets',
            'name' => 'test.jpg',
        ], files: ['file' => $file], user: $user);

        $this->assertResponseStatusCodeSame(422);

        unlink((string) $tmp);
    }

    public function test_does_not_create_a_file_if_one_already_exists(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-exists'],
            [],
        );

        ThemeFileFactory::createOne([
            'blog' => $blog,
            'folder' => ThemeFileFolder::TEMPLATES,
            'name' => 'index.twig',
            'content' => 'none',
        ]);

        $this->consoleBlogApi('POST', $blog, '/theme/file', [
            'folder' => 'templates',
            'name' => 'index.twig',
            'content' => 'test',
        ], user: $user);

        $this->assertResponseFailed(422, 'File with name \'index.twig\' already exists in the specified folder');
    }

    public function test_does_not_create_a_file_if_it_is_not_allowed_in_the_folder(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'theme-file-not-allowed'],
            [],
        );

        $this->consoleBlogApi('POST', $blog, '/theme/file', [
            'folder' => 'templates',
            'name' => 'index.css',
            'content' => 'test',
        ], user: $user);

        $this->assertResponseFailed(422, "The file 'index.css' is not allowed in the specified folder");
    }
}
