<?php

namespace App\Tests\Api\Cli;

use App\Api\Cli\Controller\CliController;
use App\Entity\Enum\BlogType;
use App\Entity\Enum\ThemeFileFolder;
use App\Entity\ThemeFile;
use App\Service\Theme\ThemeFilesService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CliController::class)]
class CliControllerTest extends ApiTestCase
{
    /**
     * @param array<mixed> $data
     */
    private function callCliApi(string $subdomain, array $data): \Symfony\Component\HttpFoundation\Response
    {
        $this->client->request(
            'PATCH',
            '/api/cli/' . $subdomain . '/files',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: (string) json_encode($data),
        );

        return $this->client->getResponse();
    }

    public function test_requires_a_valid_subdomain(): void
    {
        $response = $this->callCliApi('invalid-subdomain', []);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Invalid subdomain', (string) $response->getContent());
    }

    public function test_requires_a_dev_blog(): void
    {
        $blog = BlogFactory::createOne(['type' => BlogType::DEFAULT]);
        $response = $this->callCliApi($blog->getSubdomain(), []);
        $this->assertResponseFailed(422, 'Please use a DEV blog');
    }

    public function test_creates_files(): void
    {
        $blog = BlogFactory::createOne(['type' => BlogType::DEV]);
        $content = 'Hello, World!';

        $response = $this->callCliApi($blog->getSubdomain(), [
            'files' => [
                '/templates/index.twig' => base64_encode($content),
                'config.yaml' => base64_encode('name'),
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $file = $this->getService(ThemeFilesService::class)->getFile($blog, 'index.twig', ThemeFileFolder::TEMPLATES);
        $this->assertNotNull($file);
        $this->assertSame($content, $file->getContent());
    }

    public function test_updates_files(): void
    {
        $blog = BlogFactory::createOne(['type' => BlogType::DEV]);

        $this->getService(ThemeFilesService::class)->createOrUpdateFile(
            $blog,
            ThemeFileFolder::TEMPLATES,
            'index.twig',
            'Hi',
        );

        $response = $this->callCliApi($blog->getSubdomain(), [
            'files' => [
                '/templates/index.twig' => base64_encode('New string'),
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $file = $this->getService(ThemeFilesService::class)->getFile($blog, 'index.twig', ThemeFileFolder::TEMPLATES);
        $this->assertNotNull($file);
        $this->assertSame('New string', $file->getContent());
    }

    public function test_resets(): void
    {
        $blog = BlogFactory::createOne(['type' => BlogType::DEV]);

        $this->getService(ThemeFilesService::class)->createOrUpdateFile(
            $blog,
            ThemeFileFolder::TEMPLATES,
            'index.twig',
            'Hi',
        );

        $response = $this->callCliApi($blog->getSubdomain(), [
            'files' => [],
            'reset' => true,
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $count = $this->getEm()->getRepository(ThemeFile::class)->count(['blog' => $blog]);
        $this->assertSame(0, $count);
    }
}
