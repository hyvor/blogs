<?php

namespace App\Tests\Api\Console\Blog\Export;

use App\Api\Console\Controller\ExportController;
use App\Api\Console\Object\ExportObject;
use App\Entity\Enum\JobStatus;
use App\Entity\Enum\UserRole;
use App\Entity\Export;
use App\Message\ExportMessage;
use App\MessageHandler\ExportMessageHandler;
use App\Service\Export\ExportService;
use App\Service\Export\HyvorBlogsExporter;
use App\Service\Post\Content\PostContentService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\NavigationFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\RedirectFactory;
use App\Tests\Factory\RouteFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserVariantFactory;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ExportController::class)]
#[CoversClass(ExportService::class)]
#[CoversClass(ExportObject::class)]
#[CoversClass(HyvorBlogsExporter::class)]
#[CoversClass(ExportMessageHandler::class)]
#[CoversClass(ExportMessage::class)]
class HyvorBlogsExportTest extends ApiTestCase
{
    public function test_exports_in_hyvor_blogs_format(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'hyvor-blogs-export']);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $owner = UserFactory::createOne(['blog' => $blog, 'role' => UserRole::ADMIN]);
        UserVariantFactory::createOne(['user' => $owner, 'language' => $language]);

        $posts = [];
        for ($i = 0; $i < 3; $i++) {
            $post = PostFactory::createOne(['blog' => $blog]);
            PostVariantFactory::createOne([
                'post' => $post,
                'language' => $language,
                'content' => '{"type":"doc","content":[]}',
            ]);
            $posts[] = $post;
        }

        $users = [];
        for ($i = 0; $i < 3; $i++) {
            $user = UserFactory::createOne(['blog' => $blog]);
            UserVariantFactory::createOne(['user' => $user, 'language' => $language]);
            $users[] = $user;
        }

        $tags = [];
        for ($i = 0; $i < 3; $i++) {
            $tag = TagFactory::createOne(['blog' => $blog]);
            TagVariantFactory::createOne(['tag' => $tag, 'language' => $language]);
            $tags[] = $tag;
        }

        $media = MediaFactory::createMany(3, ['blog' => $blog]);

        $navigation = [];
        for ($i = 0; $i < 4; $i++) {
            $navigation[] = NavigationFactory::createOne(['blog' => $blog, 'sort' => $i]);
        }

        $routes = RouteFactory::createDefaultsFor($blog);
        RedirectFactory::createMany(5, ['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/data/export', user: $owner);
        $this->assertResponseIsSuccessful();

        $dispatched = $this->transport('async')->dispatched();
        $dispatched->assertContains(ExportMessage::class, 1);

        /** @var ExportMessage $message */
        $message = $dispatched->first(ExportMessage::class)->getMessage();

        $this->getService(ExportMessageHandler::class)($message);

        $export = $this->getEm()->find(Export::class, $message->exportId);
        $this->assertNotNull($export);
        $this->assertSame(JobStatus::COMPLETED, $export->getStatus());
        $this->assertStringStartsWith('https://blogs.hyvor.com/api/media/exports/', $export->getUrl());

        $filesystem = $this->getService(Filesystem::class);
        $path = 'exports/' . $blog->getId() . '/' . date('Y-m-d') . '-' . $export->getId() . '.json';
        $data = json_decode($filesystem->read($path), true);

        $this->assertSame($blog->getId(), $data['blog']['id']);
        $this->assertSame($blog->getSubdomain(), $data['blog']['subdomain']);

        $this->assertCount(1, $data['languages']);
        $this->assertSame($language->getId(), $data['languages'][0]['id']);

        $this->assertCount(3, $data['posts']);
        $this->assertSame($posts[0]->getId(), $data['posts'][0]['id']);

        $postContentService = $this->getService(PostContentService::class);
        $expectedHtml = $postContentService->getHtml('{"type":"doc","content":[]}', $blog);
        $this->assertSame($expectedHtml, $data['posts'][0]['variants'][0]['content_html']);

        $this->assertCount(4, $data['users']); // 3 + owner
        $this->assertCount(3, $data['tags']);
        $this->assertCount(3, $data['media']);
        // MediaService::getMedia returns newest first
        $this->assertSame($media[2]->getId(), $data['media'][0]['id']);

        $this->assertCount(4, $data['navigation']);
        $this->assertSame($navigation[0]->getId(), $data['navigation'][0]['id']);

        $this->assertCount(5, $data['redirects']);

        $this->assertCount(5, $data['routes']);
        $this->assertSame($routes[0]->getId(), $data['routes'][0]['id']);
        $this->assertSame($routes[0]->getName(), $data['routes'][0]['name']);
    }
}
