<?php

namespace App\Tests\Api\Console\Blog\Tag;

use App\Api\Console\Controller\TagController;
use App\Entity\TagVariant;
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
class DeleteTagVariantTest extends ApiTestCase
{
    public function test_deletes_a_tag_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-delete']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $primaryLanguage = LanguageFactory::createOnePrimaryFor($blog);
        $secondLanguage = LanguageFactory::createOneFor($blog, ['code' => 'fr']);

        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $primaryLanguage]);
        $secondVariant = TagVariantFactory::createOne(['tag' => $tag, 'language' => $secondLanguage]);
        $secondVariantId = $secondVariant->getId();

        $this->consoleBlogApi('DELETE', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => $secondLanguage->getId(),
        ], user: $user);

        $this->assertResponseIsSuccessful();
        $this->assertNull($this->getEm()->getRepository(TagVariant::class)->find($secondVariantId));
    }

    public function test_does_not_delete_primary_language_variant(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-delete-primary']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $primaryLanguage = LanguageFactory::createOnePrimaryFor($blog);
        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $primaryLanguage]);

        $this->consoleBlogApi('DELETE', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => $primaryLanguage->getId(),
        ], user: $user);

        $this->assertResponseFailed(422, 'Primary language variant cannot be deleted');
    }

    public function test_variant_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-delete-nf']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $primaryLanguage = LanguageFactory::createOnePrimaryFor($blog);
        $secondLanguage = LanguageFactory::createOneFor($blog, ['code' => 'fr']);
        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $primaryLanguage]);

        $this->consoleBlogApi('DELETE', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => $secondLanguage->getId(),
        ], user: $user);

        $this->assertResponseFailed(404, 'Variant not found');
    }

    public function test_language_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-var-delete-nolang']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => 'active']);
        $tag = TagFactory::createOne(['blog' => $blog]);

        $this->consoleBlogApi('DELETE', $blog, '/tag/' . $tag->getId() . '/variant', [
            'language_id' => 99999,
        ], user: $user);

        $this->assertResponseFailed(404, 'Language not found');
    }
}
