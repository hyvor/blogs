<?php

namespace App\Tests\Service\Blog\UpdateBlogUrls;

use App\Entity\Blog;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Entity\User;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlEvent;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessage;
use App\Service\Blog\UpdateBlogUrls\UpdateBlogUrlsMessageHandler;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory;

#[CoversClass(UpdateBlogUrlsMessageHandler::class)]
class UpdateBlogUrlsMessageHandlerTest extends KernelTestCase
{

    private function handler(): UpdateBlogUrlsMessageHandler
    {
        return $this->getService(UpdateBlogUrlsMessageHandler::class);
    }

    /**
     * @param list<Key> $lockKeys
     */
    private function hostingMessage(
        Blog $blog,
        string $oldUrl = 'https://old.com',
        string $newUrl = 'https://new.com',
        array $lockKeys = [],
    ): UpdateBlogUrlsMessage {
        return new UpdateBlogUrlsMessage(
            blogId: $blog->getId(),
            event: UpdateBlogUrlEvent::HOSTING_CHANGED,
            lockKeys: $lockKeys,
            blogOldUrl: $oldUrl,
            blogNewUrl: $newUrl,
        );
    }

    /**
     * @param list<Key> $lockKeys
     */
    private function mediaMessage(
        Blog $blog,
        string $oldUrl,
        string $newUrl,
        array $lockKeys = [],
    ): UpdateBlogUrlsMessage {
        return new UpdateBlogUrlsMessage(
            blogId: $blog->getId(),
            event: UpdateBlogUrlEvent::MEDIA_URL_CHANGED,
            lockKeys: $lockKeys,
            mediaId: 1,
            mediaOldUrl: $oldUrl,
            mediaNewUrl: $newUrl,
        );
    }

    private function docJson(string $imageSrc, string $linkHref): string
    {
        return (string) json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                    'attrs' => ['src' => $imageSrc],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'link',
                            'marks' => [
                                ['type' => 'link', 'attrs' => ['href' => $linkHref]],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param list<int|string> $path
     */
    private function dig(mixed $data, array $path): mixed
    {
        foreach ($path as $key) {
            $this->assertIsArray($data);
            $data = $data[$key];
        }
        return $data;
    }

    /**
     * @return array{0: string, 1: string} [imageSrc, linkHref]
     */
    private function extractUrls(string $json): array
    {
        $decoded = json_decode($json, true);
        $imageSrc = $this->dig($decoded, ['content', 0, 'attrs', 'src']);
        $linkHref = $this->dig($decoded, ['content', 1, 'content', 0, 'marks', 0, 'attrs', 'href']);
        $this->assertIsString($imageSrc);
        $this->assertIsString($linkHref);
        return [$imageSrc, $linkHref];
    }

    public function test_throws_exception_when_blog_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Blog with ID 999999999 not found.');

        $this->handler()(new UpdateBlogUrlsMessage(
            blogId: 999999999,
            event: UpdateBlogUrlEvent::HOSTING_CHANGED,
            lockKeys: [],
            blogOldUrl: 'https://old.com',
            blogNewUrl: 'https://new.com',
        ));
    }

    public function test_hosting_changed_updates_blog_meta_urls_matching_prefix(): void
    {
        $blog = BlogFactory::createOne();
        $meta = clone $blog->getMeta();
        $meta->logo_url = 'https://old.com/logo.png';
        $meta->cover_url = 'https://old.com/cover.png';
        $meta->icon_url = null;
        $blog->setMeta($meta);
        $this->getEm()->flush();

        $this->handler()($this->hostingMessage($blog));

        $updated = $this->getEm()->find(Blog::class, $blog->getId());
        $this->assertNotNull($updated);
        $this->assertSame('https://new.com/logo.png', $updated->getMeta()->logo_url);
        $this->assertSame('https://new.com/cover.png', $updated->getMeta()->cover_url);
        $this->assertNull($updated->getMeta()->icon_url);
    }

    public function test_media_url_changed_only_updates_exact_matching_meta_url(): void
    {
        $blog = BlogFactory::createOne();
        $meta = clone $blog->getMeta();
        $meta->logo_url = 'https://cdn.com/media/logo.png';
        // prefix-matches the old media url, but is not an exact match -> must NOT change
        $meta->cover_url = 'https://cdn.com/media/logo.png/extra';
        $meta->icon_url = null;
        $blog->setMeta($meta);
        $this->getEm()->flush();

        $this->handler()($this->mediaMessage(
            $blog,
            'https://cdn.com/media/logo.png',
            'https://cdn.com/media/logo-2.png',
        ));

        $updated = $this->getEm()->find(Blog::class, $blog->getId());
        $this->assertNotNull($updated);
        $this->assertSame('https://cdn.com/media/logo-2.png', $updated->getMeta()->logo_url);
        $this->assertSame('https://cdn.com/media/logo.png/extra', $updated->getMeta()->cover_url);
        $this->assertNull($updated->getMeta()->icon_url);
    }

    public function test_hosting_changed_updates_post_featured_image_and_variant_content_with_links(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOneFor($blog);

        $postWithData = PostFactory::createOneFor($blog, [
            'featured_image_url' => 'https://old.com/uploads/featured.jpg',
        ]);
        $variant = PostVariantFactory::createOneFor($postWithData, [
            'content' => $this->docJson('https://old.com/media/pic.jpg', 'https://old.com/page'),
            'content_unsaved' => $this->docJson('https://old.com/media/draft.jpg', 'https://old.com/draft-page'),
        ], $language);

        // a post with nothing set should be left alone and not error
        $emptyPost = PostFactory::createOneFor($blog);
        $emptyVariant = PostVariantFactory::createOneFor($emptyPost, [], $language);

        $this->handler()($this->hostingMessage($blog));

        $updatedPost = $this->getEm()->find(Post::class, $postWithData->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame('https://new.com/uploads/featured.jpg', $updatedPost->getFeaturedImageUrl());

        $updatedVariant = $this->getEm()->find(PostVariant::class, $variant->getId());
        $this->assertNotNull($updatedVariant);
        [$imageSrc, $linkHref] = $this->extractUrls((string) $updatedVariant->getContent());
        $this->assertSame('https://new.com/media/pic.jpg', $imageSrc);
        $this->assertSame('https://new.com/page', $linkHref);

        [$unsavedImageSrc, $unsavedLinkHref] = $this->extractUrls((string) $updatedVariant->getContentUnsaved());
        $this->assertSame('https://new.com/media/draft.jpg', $unsavedImageSrc);
        $this->assertSame('https://new.com/draft-page', $unsavedLinkHref);

        $updatedEmptyPost = $this->getEm()->find(Post::class, $emptyPost->getId());
        $this->assertNotNull($updatedEmptyPost);
        $this->assertNull($updatedEmptyPost->getFeaturedImageUrl());

        $updatedEmptyVariant = $this->getEm()->find(PostVariant::class, $emptyVariant->getId());
        $this->assertNotNull($updatedEmptyVariant);
        $this->assertNull($updatedEmptyVariant->getContent());
        $this->assertNull($updatedEmptyVariant->getContentUnsaved());
    }

    public function test_media_url_changed_updates_only_matching_media_and_never_links(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOneFor($blog);

        $mediaOldUrl = 'https://cdn.com/media/pic.jpg';
        $mediaNewUrl = 'https://cdn.com/media/pic-2.jpg';

        $post = PostFactory::createOneFor($blog, [
            'featured_image_url' => $mediaOldUrl,
        ]);
        // the link href intentionally equals the media url exactly - it must still not be touched,
        // because MEDIA_URL_CHANGED never updates links regardless of match
        $variant = PostVariantFactory::createOneFor($post, [
            'content' => $this->docJson($mediaOldUrl, $mediaOldUrl),
        ], $language);

        $this->handler()($this->mediaMessage($blog, $mediaOldUrl, $mediaNewUrl));

        $updatedPost = $this->getEm()->find(Post::class, $post->getId());
        $this->assertNotNull($updatedPost);
        $this->assertSame($mediaNewUrl, $updatedPost->getFeaturedImageUrl());

        $updatedVariant = $this->getEm()->find(PostVariant::class, $variant->getId());
        $this->assertNotNull($updatedVariant);
        [$imageSrc, $linkHref] = $this->extractUrls((string) $updatedVariant->getContent());
        $this->assertSame($mediaNewUrl, $imageSrc);
        $this->assertSame($mediaOldUrl, $linkHref);
    }

    public function test_continues_processing_when_a_variant_content_throws_phrosemirror_exception(): void
    {
        $blog = BlogFactory::createOne();
        $language = LanguageFactory::createOneFor($blog);

        $brokenPost = PostFactory::createOneFor($blog);
        // top node is not "doc" -> Document::fromJson throws InvalidJsonException (a PhrosemirrorException)
        $invalidContent = '{"type":"paragraph","content":[]}';
        $validUnsavedContent = $this->docJson('https://old.com/media/draft.jpg', 'https://old.com/draft-page');
        $brokenVariant = PostVariantFactory::createOneFor($brokenPost, [
            'content' => $invalidContent,
            'content_unsaved' => $validUnsavedContent,
        ], $language);

        $healthyPost = PostFactory::createOneFor($blog);
        $healthyVariant = PostVariantFactory::createOneFor($healthyPost, [
            'content' => $this->docJson('https://old.com/media/pic.jpg', 'https://old.com/page'),
        ], $language);

        // should not throw, even though one variant's content is unparsable
        $this->handler()($this->hostingMessage($blog));

        $updatedBrokenVariant = $this->getEm()->find(PostVariant::class, $brokenVariant->getId());
        $this->assertNotNull($updatedBrokenVariant);
        $this->assertSame($invalidContent, $updatedBrokenVariant->getContent());
        // content_unsaved is untouched too: the exception from updating `content` aborts
        // the whole try block before content_unsaved is ever reached
        $this->assertSame($validUnsavedContent, $updatedBrokenVariant->getContentUnsaved());

        $updatedHealthyVariant = $this->getEm()->find(PostVariant::class, $healthyVariant->getId());
        $this->assertNotNull($updatedHealthyVariant);
        [$imageSrc] = $this->extractUrls((string) $updatedHealthyVariant->getContent());
        $this->assertSame('https://new.com/media/pic.jpg', $imageSrc);
    }

    public function test_updates_user_picture_url_and_skips_non_matching_or_null(): void
    {
        $blog = BlogFactory::createOne();

        $matchingUser = UserFactory::createOne([
            'blog' => $blog,
            'picture_url' => 'https://old.com/avatars/1.png',
        ]);
        $nonMatchingUser = UserFactory::createOne([
            'blog' => $blog,
            'picture_url' => 'https://other.com/avatars/2.png',
        ]);
        $noPictureUser = UserFactory::createOne([
            'blog' => $blog,
            'picture_url' => null,
        ]);

        $this->handler()($this->hostingMessage($blog));

        $updatedMatching = $this->getEm()->find(User::class, $matchingUser->getId());
        $this->assertNotNull($updatedMatching);
        $this->assertSame('https://new.com/avatars/1.png', $updatedMatching->getPictureUrl());

        $updatedNonMatching = $this->getEm()->find(User::class, $nonMatchingUser->getId());
        $this->assertNotNull($updatedNonMatching);
        $this->assertSame('https://other.com/avatars/2.png', $updatedNonMatching->getPictureUrl());

        $updatedNoPicture = $this->getEm()->find(User::class, $noPictureUser->getId());
        $this->assertNotNull($updatedNoPicture);
        $this->assertNull($updatedNoPicture->getPictureUrl());
    }

    public function test_paginates_through_more_posts_and_users_than_chunk_size(): void
    {
        // handler chunks in batches of 100 - use more than that to force it to loop
        $count = 105;

        $blog = BlogFactory::createOne();

        PostFactory::new([
            'blog' => $blog,
            'featured_image_url' => 'https://old.com/uploads/img.jpg',
        ])->many($count)->create();

        for ($i = 0; $i < $count; $i++) {
            UserFactory::createOne([
                'blog' => $blog,
                'picture_url' => 'https://old.com/avatars/pic.png',
                'hyvor_user_id' => $i + 1,
                'slug' => "paginated-user-{$i}",
            ]);
        }

        $this->handler()($this->hostingMessage($blog));

        $updatedPosts = $this->getEm()->getRepository(Post::class)->findBy(['blog' => $blog]);
        $this->assertCount($count, $updatedPosts);
        foreach ($updatedPosts as $post) {
            $this->assertSame('https://new.com/uploads/img.jpg', $post->getFeaturedImageUrl());
        }

        $updatedUsers = $this->getEm()->getRepository(User::class)->findBy(['blog' => $blog]);
        $this->assertCount($count, $updatedUsers);
        foreach ($updatedUsers as $user) {
            $this->assertSame('https://new.com/avatars/pic.png', $user->getPictureUrl());
        }
    }

    public function test_releases_acquired_lock_keys_and_ignores_unacquired_ones(): void
    {
        $blog = BlogFactory::createOne();

        /** @var LockFactory $lockFactory */
        $lockFactory = $this->getService(LockFactory::class);

        // autoRelease must stay false on every throwaway Lock instance below: isAcquired() marks
        // a Lock "dirty", and a default (autoRelease: true) Lock silently releases the real lock
        // in its destructor as soon as it goes out of scope - which would release it before the
        // handler even runs and make this test pass for the wrong reason.
        $acquiredKey = new Key('test-acquired-lock-' . bin2hex(random_bytes(8)));
        $lock = $lockFactory->createLockFromKey($acquiredKey, autoRelease: false);
        $this->assertTrue($lock->acquire());
        $this->assertTrue($lockFactory->createLockFromKey($acquiredKey, autoRelease: false)->isAcquired());

        $unacquiredKey = new Key('test-unacquired-lock-' . bin2hex(random_bytes(8)));
        $this->assertFalse($lockFactory->createLockFromKey($unacquiredKey, autoRelease: false)->isAcquired());

        // should not throw despite one of the keys never having been acquired
        $this->handler()($this->hostingMessage($blog, lockKeys: [$acquiredKey, $unacquiredKey]));

        $this->assertFalse($lockFactory->createLockFromKey($acquiredKey, autoRelease: false)->isAcquired());
    }

    public function test_dispatches_rerender_post_html_message_asynchronously_after_processing(): void
    {
        $blog = BlogFactory::createOne();

        $this->handler()($this->hostingMessage($blog));

        $dispatched = $this->transport('async')->dispatched();
        $dispatched->assertContains(ReRenderPostHtmlMessage::class, 1);

        /** @var ReRenderPostHtmlMessage $message */
        $message = $dispatched->first(ReRenderPostHtmlMessage::class)->getMessage();
        $this->assertSame($blog->getId(), $message->blogId);
    }

}
