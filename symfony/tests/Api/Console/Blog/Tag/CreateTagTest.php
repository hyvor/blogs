<?php

namespace App\Tests\Api\Console\Blog\Tag;

use App\Api\Console\Controller\TagController;
use App\Api\Console\Object\TagObject;
use App\Api\Console\Object\TagObjectFactory;
use App\Entity\Tag;
use App\Service\Tag\TagService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagController::class)]
#[CoversClass(TagService::class)]
#[CoversClass(TagObject::class)]
#[CoversClass(TagObjectFactory::class)]
class CreateTagTest extends ApiTestCase
{
    public function test_creates_a_tag_with_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-create']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        LanguageFactory::createOnePrimaryFor($blog);

        $name = 'Blogging';

        $this->consoleBlogApi('POST', $blog, '/tag', [
            'name' => $name,
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);
        $json = $this->getJson();
        $this->assertSame($name, $json['variants'][0]['name']);
        $this->assertFalse($json['is_private']);
        $this->assertCount(1, $json['variants']);

        $tags = $this->getEm()->getRepository(Tag::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $tags);
        $this->assertFalse($tags[0]->isPrivate());
        $this->assertCount(1, $tags[0]->getVariants());
    }

    public function test_creates_a_private_tag(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-create-private']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        LanguageFactory::createOnePrimaryFor($blog);

        $this->consoleBlogApi('POST', $blog, '/tag', [
            'name' => 'Blogging',
            'is_private' => true,
        ], user: $user);

        $this->assertResponseStatusCodeSame(201);

        $tags = $this->getEm()->getRepository(Tag::class)->findBy(['blog' => $blog]);
        $this->assertCount(1, $tags);
        $this->assertTrue($tags[0]->isPrivate());
    }
}
