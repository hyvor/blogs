<?php

namespace App\Tests\Api\Console\Blog\Document;

use App\Api\Console\Controller\DocumentsController;
use App\Service\Post\Document\DocumentService;
use App\Service\Post\Document\Exception\CheckpointClientAheadException;
use App\Service\Post\Document\Exception\CheckpointClientBehindException;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\PostVariantStepFactory;
use PHPUnit\Framework\Attributes\CoversClass;

use function Zenstruck\Foundry\Persistence\refresh;

#[CoversClass(DocumentsController::class)]
#[CoversClass(CheckpointClientAheadException::class)]
#[CoversClass(CheckpointClientBehindException::class)]
#[CoversClass(DocumentService::class)]
class DocumentCheckpointTest extends ApiTestCase
{

    public function test_saves_checkpoint(): void
    {

        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOnePublishedFor($post, [
            'language' => $language,
            'document_version' => 1,
        ]);

        $content = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hello, world!"}]}]}';

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/documents/checkpoint',
            [
                'post_variant_id' => $variant->getId(),
                'version' => 1,
                'content' => $content,
            ],
            user: $user
        );

        $this->assertResponseIsSuccessful();

        refresh($variant);
        $this->assertSame($content, $variant->getContentUnsaved());
        $this->assertSame(1, $variant->getContentUnsavedVersion());
    }

    public function test_sends_steps_when_client_is_behind(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOnePublishedFor($post, [
            'language' => $language,
            'document_version' => 3,
        ]);

        PostVariantStepFactory::createOneFor($variant, [
            'version' => 2,
            'client_id' => 'other-client-id',
            'step' => ['type' => 'insertText', 'position' => 0, 'text' => 'Hello,'],
        ]);
        PostVariantStepFactory::createOneFor($variant, [
            'version' => 3,
            'client_id' => 'other-client-id',
            'step' => ['type' => 'insertText', 'position' => 6, 'text' => ' world!'],
        ]);

        $content = '{"type":"doc","content":[]}';

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/documents/checkpoint',
            [
                'post_variant_id' => $variant->getId(),
                'version' => 1,
                'content' => $content,
            ],
            user: $user
        );

        $this->assertResponseFailed(409, 'client_behind');

        $json = $this->getJson();
        $this->assertSame(3, $json['version']);
        $this->assertSame(
            [
                [
                    'version' => 2,
                    'step' => ['type' => 'insertText', 'position' => 0, 'text' => 'Hello,'],
                    'client_id' => 'other-client-id',
                ],
                [
                    'version' => 3,
                    'step' => ['type' => 'insertText', 'position' => 6, 'text' => ' world!'],
                    'client_id' => 'other-client-id',
                ],
            ],
            $json['steps']
        );

        refresh($variant);
        $this->assertNull($variant->getContentUnsaved());
        $this->assertSame(0, $variant->getContentUnsavedVersion());
    }

    public function test_fails_when_client_is_ahead(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOnePublishedFor($post, [
            'language' => $language,
            'document_version' => 1,
        ]);

        $content = '{"type":"doc","content":[]}';

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/documents/checkpoint',
            [
                'post_variant_id' => $variant->getId(),
                'version' => 5,
                'content' => $content,
            ],
            user: $user
        );

        $this->assertResponseFailed(409, 'client_ahead');

        $json = $this->getJson();
        $this->assertSame(
            'PostVariant document_version is 1, but checkpoint version is 5',
            $json['message_full']
        );

        refresh($variant);
        $this->assertNull($variant->getContentUnsaved());
        $this->assertSame(0, $variant->getContentUnsavedVersion());
    }

}
