<?php

namespace App\Tests\Api\Console\Blog\Tag;

use App\Api\Console\Controller\TagController;
use App\Api\Console\Object\TagVariantObject;
use App\Api\Console\Object\TagVariantObjectFactory;
use App\Entity\Enum\UserStatus;
use App\Entity\TagVariant;
use App\Service\Tag\TagService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagController::class)]
#[CoversClass(TagService::class)]
#[CoversClass(TagVariantObject::class)]
#[CoversClass(TagVariantObjectFactory::class)]
class CreateTagVariantTest extends ApiTestCase
{
    // this generally does not happen in practise
    public function test_creates_a_tag_variant_for_primary_language(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-create-1']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        $tag = TagFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertArrayHasKey('name', $json);
        $this->assertSame($language->getId(), $json['language_id']);

        $variants = $this->getEm()->getRepository(TagVariant::class)->findBy(['tag' => $tag->getId()]);
        $this->assertCount(1, $variants);
    }

    public function test_creates_a_tag_variant_for_second_language(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-create-2']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        LanguageFactory::createOnePrimaryFor($blog);
        $language = LanguageFactory::createOneFor($blog, ['code' => 'fr']);
        $tag = TagFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => $language->getId(),
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertArrayHasKey('name', $json);
        $this->assertSame($language->getId(), $json['language_id']);

        $variants = $this->getEm()->getRepository(TagVariant::class)->findBy(['tag' => $tag->getId()]);
        $this->assertCount(1, $variants);
    }

    public function test_language_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-create-nolang']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $tag = TagFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('POST', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => 99999,
        ], user: $user);

        $this->assertResponseFailed(404, 'Language not found');
    }
}
