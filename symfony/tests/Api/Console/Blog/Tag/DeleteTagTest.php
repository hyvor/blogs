<?php

namespace App\Tests\Api\Console\Blog\Tag;

use App\Api\Console\Controller\TagController;
use App\Entity\Enum\UserStatus;
use App\Entity\Tag;
use App\Entity\TagVariant;
use App\Service\Tag\TagService;
use App\Tests\Case\ApiTestCase;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\TagFactory;
use App\Tests\Factory\TagVariantFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagController::class)]
#[CoversClass(TagService::class)]
class DeleteTagTest extends ApiTestCase
{
    public function test_deletes_the_tag_its_variants_and_post_tags(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-delete']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);
        $language1 = LanguageFactory::createOnePrimaryFor($blog);
        $language2 = LanguageFactory::createOneFor($blog, ['code' => 'fr']);
        $language3 = LanguageFactory::createOneFor($blog, ['code' => 'es']);

        $tag = TagFactory::createOne(['blog' => $blog]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $language1]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $language2]);
        TagVariantFactory::createOne(['tag' => $tag, 'language' => $language3]);

        $post = PostFactory::createOne(['blog' => $blog]);
        $post->getTags()->add($tag);
        $this->getEm()->flush();

        $tagId = $tag->getId();

        $this->consoleBlogApi('DELETE', $blog, '/tag/' . $tagId, user: $user);

        $this->assertResponseIsSuccessful();

        $this->assertNull($this->getEm()->getRepository(Tag::class)->find($tagId));
        $this->assertCount(0, $this->getEm()->getRepository(TagVariant::class)->findBy(['tag' => $tagId]));

        $postTagCount = $this->getEm()->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM post_tag WHERE tag_id = ?',
            [$tagId],
        );
        $this->assertSame(0, (int) $postTagCount);
    }

    public function test_entity_not_found(): void
    {
        $blog = BlogFactory::createOne(['subdomain' => 'tag-delete-nf']);
        $user = UserFactory::createOne(['blog' => $blog, 'status' => UserStatus::ACTIVE]);

        $this->consoleBlogApi('DELETE', $blog, '/tag/99999', user: $user);

        $this->assertResponseFailed(404, 'Entity not found');
    }
}
