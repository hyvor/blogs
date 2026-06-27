<?php

namespace App\Tests\Api\Console\Blog\Language;

use App\Api\Console\Controller\LanguageController;
use App\Entity\Enum\UserStatus;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Language\LanguageService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
#[CoversClass(LanguageService::class)]
class DeleteLanguageTest extends ApiTestCase
{
    public function test_delete_language(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-delete'],
            ['status' => UserStatus::ACTIVE],
        );
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'fr',
            'is_primary' => false,
        ]);

        $this->consoleBlogApi('DELETE', 'lang-delete', '/language/' . $lang->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $this->getEd()->assertDispatched(LanguageChangedEvent::class);
    }

    public function test_delete_primary_language_fails(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-del-primary'],
            ['status' => UserStatus::ACTIVE],
        );
        $lang = LanguageFactory::createOne([
            'blog' => $blog,
            'code' => 'en',
            'is_primary' => true,
        ]);

        $this->consoleBlogApi('DELETE', 'lang-del-primary', '/language/' . $lang->getId(), user: $user);

        $this->assertResponseFailed(422, 'Cannot delete the primary language');
    }
}
