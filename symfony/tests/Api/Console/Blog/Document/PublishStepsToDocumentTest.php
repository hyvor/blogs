<?php

namespace App\Tests\Api\Console\Blog\Document;

use App\Api\Console\Controller\DocumentsController;
use App\Entity\PostVariantStep;
use App\Service\Post\Document\DocumentService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\PostVariantStepFactory;
use App\Tests\Fake\FakeHub;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DocumentsController::class)]
#[CoversClass(DocumentService::class)]
class PublishStepsToDocumentTest extends ApiTestCase
{

    public function test_publishes_steps(): void
    {

        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOnePublishedFor($post, [
            'language' => $language,
            'document_version' => 1,
        ]);

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/documents/steps',
            [
                'post_variant_id' => $variant->getId(),
                'steps' => [
                    [
                        'type' => 'insertText',
                        'position' => 0,
                        'text' => 'Hello, world!',
                    ],
                ],
                'client_id' => 'test-client-id',
                'version' => 1,
            ],
            user: $user
        );

        $this->assertResponseIsSuccessful();

        $json = $this->getJson();
        $this->assertTrue($json['accepted']);
        $this->assertSame(2, $json['version']);
        $this->assertArrayNotHasKey('client_ids', $json);
        $this->assertSame(
            [
                [
                    'version' => 2,
                    'step' => [
                        'type' => 'insertText',
                        'position' => 0,
                        'text' => 'Hello, world!',
                    ],
                    'client_id' => 'test-client-id',
                ],
            ],
            $json['steps']
        );

        $steps = $this->getEm()->getRepository(PostVariantStep::class)->findAll();
        $this->assertCount(1, $steps);
        $step = $steps[0];
        $this->assertSame($variant->getId(), $step->getPostVariant()->getId());
        $this->assertSame(2, $step->getVersion());
        $this->assertSame('test-client-id', $step->getClientId());

        $hub = $this->getService(FakeHub::class);
        $hub->assertPublished('document:' . $variant->getId());
        $updates = $hub->getPublished();
        $this->assertCount(1, $updates);
        $update = $updates[0];
        $this->assertSame(
            json_encode([
                'type' => 'steps',
                'version' => 2,
                'steps' => [
                    [
                        'version' => 2,
                        'step' => [
                            'type' => 'insertText',
                            'position' => 0,
                            'text' => 'Hello, world!',
                        ],
                        'client_id' => 'test-client-id',
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
            $update->getData()
        );
    }

    public function test_increases_version_multiple_for_multiple_steps(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOnePublishedFor($post, [
            'language' => $language,
            'document_version' => 1,
        ]);

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/documents/steps',
            [
                'post_variant_id' => $variant->getId(),
                'steps' => [
                    [
                        'type' => 'insertText',
                        'position' => 0,
                        'text' => 'Hello, world!',
                    ],
                    [
                        'type' => 'insertText',
                        'position' => 13,
                        'text' => ' How are you?',
                    ],
                ],
                'client_id' => 'test-client-id',
                'version' => 1,
            ],
            user: $user
        );

        $this->assertResponseIsSuccessful();

        $json = $this->getJson();
        $this->assertTrue($json['accepted']);
        $this->assertSame(3, $json['version']);
        $this->assertArrayNotHasKey('client_ids', $json);
        $this->assertSame(
            [
                [
                    'version' => 2,
                    'step' => [
                        'type' => 'insertText',
                        'position' => 0,
                        'text' => 'Hello, world!',
                    ],
                    'client_id' => 'test-client-id',
                ],
                [
                    'version' => 3,
                    'step' => [
                        'type' => 'insertText',
                        'position' => 13,
                        'text' => ' How are you?',
                    ],
                    'client_id' => 'test-client-id',
                ],
            ],
            $json['steps']
        );

        $steps = $this->getEm()->getRepository(PostVariantStep::class)->findAll();
        $this->assertCount(2, $steps);
    }

    public function test_sends_missing_steps_when_client_is_behind(): void
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

        $this->consoleBlogApi(
            'POST',
            $blog,
            '/documents/steps',
            [
                'post_variant_id' => $variant->getId(),
                'steps' => [
                    [
                        'type' => 'insertText',
                        'position' => 0,
                        'text' => 'Hi there',
                    ],
                ],
                'client_id' => 'test-client-id',
                'version' => 1,
            ],
            user: $user
        );

        $this->assertResponseIsSuccessful();

        $json = $this->getJson();
        $this->assertFalse($json['accepted']);
        $this->assertSame(3, $json['version']);
        $this->assertArrayNotHasKey('client_ids', $json);
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

        $steps = $this->getEm()->getRepository(PostVariantStep::class)->findAll();
        $this->assertCount(2, $steps);
    }

}
