<?php

namespace App\Tests\Service\Import\Importer;

use App\Entity\Post;
use App\Service\Import\Importer\ImportingPost;
use App\Service\Import\Importer\ImportingPostVariant;
use App\Service\Import\Importer\Importer;
use App\Service\Import\Importer\ParserAbstract;
use App\Service\Language\LanguageService;
use App\Service\Media\MediaService;
use App\Service\Post\Content\Nodes\Audio\Audio;
use App\Service\Post\Content\Nodes\Image\Image;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;
use App\Service\User\UserService;
use App\Tests\Factory\BlogFactory;
use Doctrine\DBAL\Connection;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Importer::class)]
class ImporterTest extends KernelTestCase
{
    public function test_imports_local_files(): void
    {
        $blog = BlogFactory::createOneWithLanguageAndRoutes(['subdomain' => 'importer-local-files']);

        $content = json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello World'],
                    ],
                ],
                [
                    'type' => 'image',
                    'attrs' => [
                        'src' => 'file://' . __DIR__ . '/example.txt',
                        'alt' => 'Image',
                    ],
                ],
                [
                    'type' => 'audio',
                    'attrs' => [
                        'src' => 'file://' . __DIR__ . '/example.audio.txt',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $parser = new class($content) extends ParserAbstract {
            public function __construct(private readonly string $content)
            {
            }

            public function parse(): void
            {
                $this->addPost(
                    new ImportingPost(
                        publishedAt: new \DateTimeImmutable(),
                        featuredImageUrl: 'file://' . __DIR__ . '/example.featured.txt',
                        variants: [
                            new ImportingPostVariant(
                                slug: 'slug',
                                content: $this->content,
                                title: 'title',
                                description: 'description',
                            ),
                        ],
                    ),
                );
            }
        };

        $importer = new Importer(
            $blog,
            $parser,
            true,
            $this->getService(Connection::class),
            $this->getService(LanguageService::class),
            $this->getService(UserService::class),
            $this->getService(PostService::class),
            $this->getService(MediaService::class),
            $this->getService(PermalinkService::class),
            $this->getService(PostContentService::class),
        );
        $importer->import();

        $this->assertSame(1, $importer->postsCount);

        $posts = $this->getEm()->getRepository(Post::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $posts);
        $post = $posts[0];

        $mediaService = $this->getService(MediaService::class);

        $featuredImageUrl = $post->getFeaturedImageUrl();
        $this->assertNotNull($featuredImageUrl);
        $name = basename($featuredImageUrl);
        $media = $mediaService->getMediaByBlogAndName($blog, $name);
        $this->assertNotNull($media);
        $this->assertSame('Featured image', $mediaService->getContents($media));

        $variant = $post->getVariants()->first();
        $this->assertNotFalse($variant);

        $doc = $this->getService(PostContentService::class)->getDocumentFromJson((string)$variant->getContent(), $blog);

        $imageNodes = $doc->getNodes(Image::class);
        $this->assertCount(1, $imageNodes);
        $imageSrc = $imageNodes[0]->attrs->get('src');
        $this->assertIsString($imageSrc);
        $imageMedia = $mediaService->getMediaByBlogAndName($blog, basename($imageSrc));
        $this->assertNotNull($imageMedia);
        $this->assertSame('Image', $mediaService->getContents($imageMedia));

        $audioNodes = $doc->getNodes(Audio::class);
        $this->assertCount(1, $audioNodes);
        $audioSrc = $audioNodes[0]->attrs->get('src');
        $this->assertIsString($audioSrc);
        $audioMedia = $mediaService->getMediaByBlogAndName($blog, basename($audioSrc));
        $this->assertNotNull($audioMedia);
        $this->assertSame('Audio file', $mediaService->getContents($audioMedia));
    }
}
