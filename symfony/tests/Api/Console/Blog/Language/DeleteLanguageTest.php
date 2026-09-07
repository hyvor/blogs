<?php

namespace App\Tests\Api\Console\Blog\Language;

use App\Api\Console\Controller\LanguageController;
use App\Entity\Enum\UserStatus;
use App\Entity\PostVariant;
use App\Entity\TagVariant;
use App\Service\Language\Event\LanguageChangedEvent;
use App\Service\Language\LanguageService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageController::class)]
#[CoversClass(LanguageService::class)]
#[CoversClass(LanguageChangedEvent::class)]
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

        $post = PostFactory::createOne(['blog' => $blog]);
        $postVariant = PostVariantFactory::createOne(['post' => $post, 'language' => $lang]);
        $postVariantId = $postVariant->getId();

        $tag = TagFactory::createOne(['blog' => $blog]);
        $tagVariant = TagVariantFactory::createOne(['tag' => $tag, 'language' => $lang]);
        $tagVariantId = $tagVariant->getId();

        $this->consoleBlogApi('DELETE', 'lang-delete', '/language/' . $lang->getId(), user: $user);

        $this->assertResponseIsSuccessful();
        $this->getEd()->assertDispatched(LanguageChangedEvent::class);

        $this->getEm()->clear();
        $this->assertNull($this->getEm()->getRepository(PostVariant::class)->find($postVariantId));
        $this->assertNull($this->getEm()->getRepository(TagVariant::class)->find($tagVariantId));
    }

    public function test_delete_primary_language_fails(): void
    {
        [$blog, $user] = BlogFactory::createOneWithUser(
            ['subdomain' => 'lang-del-primary'],
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
