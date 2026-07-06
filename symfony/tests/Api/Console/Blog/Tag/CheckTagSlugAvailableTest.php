<?php

namespace App\Tests\Api\Console\Blog\Tag;

use App\Api\Console\Controller\TagController;
use App\Entity\Enum\UserStatus;
use App\Service\Tag\TagService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagController::class)]
#[CoversClass(TagService::class)]
class CheckTagSlugAvailableTest extends ApiTestCase
{
    public function test_slug_is_available(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-slug-avail']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $tag = TagFactory::createOne(['blog' => $blog, 'slug' => 'existing-tag']);

        $this->consoleBlogApi('GET', $blog, '/tag/' . $tag->getId() . '/slug-available?slug=new-slug', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertTrue($json['available']);
    }

    public function test_slug_is_not_available_when_used_by_another_tag(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-slug-taken']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $tag = TagFactory::createOne(['blog' => $blog, 'slug' => 'existing-tag']);
        TagFactory::createOne(['blog' => $blog, 'slug' => 'other-tag']);

        $this->consoleBlogApi('GET', $blog, '/tag/' . $tag->getId() . '/slug-available?slug=other-tag', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertFalse($json['available']);
    }

    public function test_slug_is_available_when_it_belongs_to_the_same_tag(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-slug-self']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $tag = TagFactory::createOne(['blog' => $blog, 'slug' => 'existing-tag']);

        $this->consoleBlogApi('GET', $blog, '/tag/' . $tag->getId() . '/slug-available?slug=existing-tag', user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertTrue($json['available']);
    }
}
