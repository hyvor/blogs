<?php

namespace App\Service\Blog\Hosting;

use App\Entity\Blog;
use App\Entity\Post;
use App\Entity\User;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\Content\UrlUpdater;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Rewrites absolute URLs stored in a blog's content (meta images, post featured images,
 * post variant content, author pictures) when the blog's hosting URL changes.
 *
 * Port of Laravel's App\Domains\Blog\Jobs\UpdateUrlsInBlogJob.
 */
class UpdateBlogUrlsService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PostContentService $postContentService,
    ) {
    }

    public function updateUrls(Blog $blog, string $oldUrl, string $newUrl): void
    {
        if ($oldUrl === '' || $oldUrl === $newUrl) {
            return;
        }

        $this->updateBlogMetaUrls($blog, $oldUrl, $newUrl);
        $this->updatePostsUrls($blog, $oldUrl, $newUrl);
        $this->updateUsersUrls($blog, $oldUrl, $newUrl);
    }

    /**
     * Returns the replaced URL if $url starts with $oldUrl, or null if no replacement is needed.
     */
    private function replaceIfHasOldUrl(?string $url, string $oldUrl, string $newUrl): ?string
    {
        if ($url === null || !str_starts_with($url, $oldUrl)) {
            return null;
        }

        return str_replace($oldUrl, $newUrl, $url);
    }

    private function updateBlogMetaUrls(Blog $blog, string $oldUrl, string $newUrl): void
    {
        $meta = clone $blog->getMeta();
        $changed = false;

        foreach (['logo_url', 'cover_url', 'icon_url'] as $field) {
            $replaced = $this->replaceIfHasOldUrl($meta->$field, $oldUrl, $newUrl);
            if ($replaced !== null) {
                $meta->$field = $replaced;
                $changed = true;
            }
        }

        if ($changed) {
            $blog->setMeta($meta);
        }
    }

    private function updatePostsUrls(Blog $blog, string $oldUrl, string $newUrl): void
    {
        $posts = $this->em->getRepository(Post::class)->findBy(['blog' => $blog]);

        foreach ($posts as $post) {
            $featuredImageUrl = $this->replaceIfHasOldUrl($post->getFeaturedImageUrl(), $oldUrl, $newUrl);
            if ($featuredImageUrl !== null) {
                $post->setFeaturedImageUrl($featuredImageUrl);
            }

            foreach ($post->getVariants() as $variant) {
                $content = $variant->getContent();
                if ($content !== null) {
                    $variant->setContent($this->updateContentUrls($content, $oldUrl, $newUrl));
                }

                $contentUnsaved = $variant->getContentUnsaved();
                if ($contentUnsaved !== null) {
                    $variant->setContentUnsaved($this->updateContentUrls($contentUnsaved, $oldUrl, $newUrl));
                }
            }
        }
    }

    private function updateContentUrls(string $content, string $oldUrl, string $newUrl): string
    {
        $document = $this->postContentService->getDocumentFromJson($content);
        $updated = (new UrlUpdater($document))->updateFromOldToNew($oldUrl, $newUrl);
        return $updated->toJson();
    }

    private function updateUsersUrls(Blog $blog, string $oldUrl, string $newUrl): void
    {
        $users = $this->em->getRepository(User::class)->findBy(['blog' => $blog]);

        foreach ($users as $user) {
            $pictureUrl = $this->replaceIfHasOldUrl($user->getPictureUrl(), $oldUrl, $newUrl);
            if ($pictureUrl !== null) {
                $user->setPictureUrl($pictureUrl);
            }
        }
    }
}
