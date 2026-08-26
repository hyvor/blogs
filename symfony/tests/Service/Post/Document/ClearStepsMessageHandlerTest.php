<?php

namespace App\Tests\Service\Post\Document;

use App\Entity\PostVariantStep;
use App\Service\Post\Document\ClearStepsMessage;
use App\Service\Post\Document\ClearStepsMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\PostVariantStepFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ClearStepsMessageHandler::class)]
class ClearStepsMessageHandlerTest extends KernelTestCase
{
    public function test_clears_old_checkpointed_steps_and_keeps_the_rest(): void
    {
        [$blog, ] = BlogFactory::createOneWithUser();
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $post = PostFactory::createOneFor($blog);

        $variant = PostVariantFactory::createOnePublishedFor($post, [
            'language' => $language,
            'content_unsaved_version' => 5,
            'document_version' => 10,
        ]);

        // old (>7 days) and checkpointed (version <= 5) - should be cleared
        $oldCheckpointed = PostVariantStepFactory::createOneFor($variant, [
            'version' => 3,
            'created_at' => new \DateTimeImmutable('-8 days'),
        ]);

        // old (>7 days) but NOT yet checkpointed (version > 5) - other clients may still catch up
        $oldNotCheckpointed = PostVariantStepFactory::createOneFor($variant, [
            'version' => 8,
            'created_at' => new \DateTimeImmutable('-8 days'),
        ]);

        // recent (<7 days) and checkpointed (version <= 5) - not old enough yet
        $recentCheckpointed = PostVariantStepFactory::createOneFor($variant, [
            'version' => 4,
            'created_at' => new \DateTimeImmutable('-1 day'),
        ]);

        // recent and not checkpointed - kept
        $recentNotCheckpointed = PostVariantStepFactory::createOneFor($variant, [
            'version' => 9,
            'created_at' => new \DateTimeImmutable('-1 day'),
        ]);

        $handler = $this->getService(ClearStepsMessageHandler::class);
        $handler(new ClearStepsMessage());

        $this->getEm()->clear();

        $repository = $this->getEm()->getRepository(PostVariantStep::class);

        $this->assertNull($repository->find($oldCheckpointed->getId()));
        $this->assertNotNull($repository->find($oldNotCheckpointed->getId()));
        $this->assertNotNull($repository->find($recentCheckpointed->getId()));
        $this->assertNotNull($repository->find($recentNotCheckpointed->getId()));
    }
}
