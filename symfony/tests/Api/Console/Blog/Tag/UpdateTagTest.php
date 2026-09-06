<?php

namespace App\Tests\Api\Console\Blog\Tag;

use App\Api\Console\Controller\TagController;
use App\Api\Console\Object\TagObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Service\Tag\Event\TagUpdatedEvent;
use App\Service\Tag\TagService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagController::class)]
#[CoversClass(TagService::class)]
#[CoversClass(TagObject::class)]
#[CoversClass(TagObjectFactory::class)]
#[CoversClass(TagUpdatedEvent::class)]
class UpdateTagTest extends ApiTestCase
{
    public function test_updates_a_tag(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-update']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $tag = TagFactory::createOne(['blog' => $blog, 'is_private' => false]);

        $slug = 'hello-world';
        $codeHead = 'var x = head';
        $codeFoot = 'var y = foot';

        $this->consoleBlogApi('PATCH', $blog, '/tag/' . $tag->getId(), [
            'is_private' => true,
            'slug' => $slug,
            'code_head' => $codeHead,
            'code_foot' => $codeFoot,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertTrue($json['is_private']);
        $this->assertSame($slug, $json['slug']);
        $this->assertSame($codeHead, $json['code_head']);
        $this->assertSame($codeFoot, $json['code_foot']);

        $event = $this->getEd()->getFirstEvent(TagUpdatedEvent::class);
        $this->assertSame($tag->getId(), $event->tag->getId());
        $this->assertTrue($event->tag->isPrivate());
        $this->assertFalse($event->tagOld->isPrivate());
    }

    public function test_entity_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-update-nf']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('PATCH', $blog, '/tag/99999', [
            'slug' => 'hello-world',
        ], user: $user);

        $this->assertResponseFailed(404, 'Entity not found');
    }
}
