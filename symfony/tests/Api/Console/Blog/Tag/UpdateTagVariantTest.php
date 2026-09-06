<?php

namespace App\Tests\Api\Console\Blog\Tag;

use App\Api\Console\Controller\TagController;
use App\Api\Console\Object\TagVariantObject;
use App\Api\Console\Object\TagVariantObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Service\Tag\Event\TagVariantUpdatedEvent;
use App\Service\Tag\TagService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagController::class)]
#[CoversClass(TagService::class)]
#[CoversClass(TagVariantObject::class)]
#[CoversClass(TagVariantObjectFactory::class)]
#[CoversClass(TagVariantUpdatedEvent::class)]
class UpdateTagVariantTest extends ApiTestCase
{
    public function test_updates_tag_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-update']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $tag = TagFactory::createOne(['blog' => $blog]);
        $variant = TagVariantFactory::createOne(['tag' => $tag, 'language' => $language, 'name' => 'Old Name']);

        $name = 'Hey';
        $description = 'I am hey';

        $this->consoleBlogApi('PATCH', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => $language->getId(),
            'name' => $name,
            'description' => $description,
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame($name, $json['name']);
        $this->assertSame($description, $json['description']);

        $event = $this->getEd()->getFirstEvent(TagVariantUpdatedEvent::class);
        $this->assertSame($variant->getId(), $event->variant->getId());
        $this->assertSame($name, $event->variant->getName());
        $this->assertSame('Old Name', $event->variantOld->getName());
    }

    public function test_variant_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-update-nf']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $tag = TagFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('PATCH', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => $language->getId(),
            'name' => 'Name',
        ], user: $user);

        $this->assertResponseFailed(404, 'Variant not found');
    }

    public function test_language_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-update-nolang']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $tag = TagFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('PATCH', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => 99999,
            'name' => 'Name',
        ], user: $user);

        $this->assertResponseFailed(404, 'Language not found');
    }
}
