<?php

namespace App\Tests\Api\Console\Blog\Language;

use App\Api\Console\Controller\LanguageController;
use App\Api\Console\Object\LanguageObject;
use App\Entity\Enum\LanguageDirection;
use App\Entity\Enum\UserStatus;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Language\LanguageService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
#[CoversClass(LanguageObject::class)]
#[CoversClass(LanguageService::class)]
#[CoversClass(LanguageChangedEvent::class)]
class UpdateLanguageTest extends ApiTestCase
{
    public function test_update_language(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-update'],
            ['status' => UserStatus::ACTIVE],
        );
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'en',
            'name' => 'English',
            'direction' => LanguageDirection::LTR,
            'is_primary' => false,
        ]);

        $this->consoleBlogApi('PATCH', 'lang-update', '/language/' . $lang->getId(), [
            'code' => 'en-US',
            'name' => 'English (US)',
            'direction' => 'ltr',
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $json = $this->getJson();
        $this->assertSame('en-US', $json['code']);
        $this->assertSame('English (US)', $json['name']);
        $this->getEd()->assertDispatched(LanguageChangedEvent::class);
    }

    public function test_update_language_wrong_blog(): void
    {
        [$blog1, $user1] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-upd-b1'],
            ['status' => UserStatus::ACTIVE],
        );
        [$blog2, $user2] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-upd-b2'],
            ['status' => UserStatus::ACTIVE],
        );
        $lang = LanguageFactory::createOne([
            'blog' => $blog2,
            'code' => 'fr',
        ]);

        $this->consoleBlogApi('PATCH', 'lang-upd-b1', '/language/' . $lang->getId(), [
            'code' => 'fr',
            'name' => 'French',
            'direction' => 'ltr',
        ], user: $user1);

        $this->assertResponseStatusCodeSame(404);
    }
}
