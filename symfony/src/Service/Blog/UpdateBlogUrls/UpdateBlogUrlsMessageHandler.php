<?php

namespace App\Service\Blog\UpdateBlogUrls;

use App\Entity\Blog;
use App\Entity\Post;
use App\Entity\User;
use App\Service\Blog\BlogService;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use App\Service\Blog\UpdateBlogUrls\Updater\UpdaterInterface;
use App\Service\Post\Content\DocUrlUpdater;
use App\Service\Post\Content\PostContentService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Phrosemirror\Exception\PhrosemirrorException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class UpdateBlogUrlsMessageHandler
{

    private const int CHUNK_SIZE = 100;

    public function __construct(
        private EntityManagerInterface $em,
        private BlogService $blogService,
        private LoggerInterface $logger,
        private PostContentService $postContentService,
        private LockFactory $lockFactory,
        private MessageBusInterface $bus,
    ) {}

    public function __invoke(UpdateBlogUrlsMessage $message): void
    {
        $blog = $this->blogService->getBlogById($message->blogId);

        if ($blog === null) {
            throw new \RuntimeException("Blog with ID {$message->blogId} not found.");
        }

        $updater = match ($message->event) {
            UpdateBlogUrlEvent::HOSTING_CHANGED => new Updater\HostingUpdater((string) $message->blogOldUrl, (string) $message->blogNewUrl),
            UpdateBlogUrlEvent::MEDIA_URL_CHANGED => new Updater\MediaUpdater((string) $message->mediaOldUrl, (string) $message->mediaNewUrl),
        };

        $this->em->wrapInTransaction(function() use ($blog, $updater, $message) {
            $this->updateBlog($blog, $updater);
            $this->updatePosts($blog, $updater, $message->event === UpdateBlogUrlEvent::HOSTING_CHANGED);
            $this->updateUsers($blog, $updater);
        });

        foreach ($message->lockKeys as $lockKey) {
            $lock = $this->lockFactory->createLockFromKey($lockKey);
            if ($lock->isAcquired()) {
                $lock->release();
            }
        }

        /**
         * dispatches asynchronously to re-render post HTML
         * in hosting change, this message handler is called synchronously, because, it is critical to update
         * all post content in a single transaction.
         *
         * however, we can safely recalculate post HTML asynchronously, because, when
         * we have content and content_unsaved saved correctly, the post HTML can be re-rendered later without any issues.
         * if it fails, we can retry anytime.
         */
        $this->bus->dispatch(new ReRenderPostHtmlMessage($blog->getId()));
    }

    private function updateBlog(Blog $blog, UpdaterInterface $updater): void
    {
        $meta = clone $blog->getMeta();
        $changed = false;

        foreach (['logo_url', 'cover_url', 'icon_url'] as $field) {
            if ($meta->$field === null) {
                continue;
            }

            $replaced = $updater->update($meta->$field);
            if ($replaced !== false) {
                $meta->$field = $replaced;
                $changed = true;
            }
        }

        if ($changed) {
            $blog->setMeta($meta);
        }

        $this->em->persist($blog);
    }

    private function updatePosts(
        Blog $blog,
        UpdaterInterface $updater,
        bool $updateLinks,
    ): void
    {
        $lastId = 0;

        while (true) {
            /** @var int[] $ids */
            $ids = $this->em->createQueryBuilder()
                ->select('p.id')
                ->from(Post::class, 'p')
                ->where('p.blog = :blog')
                ->andWhere('p.id > :lastId')
                ->orderBy('p.id', 'ASC')
                ->setMaxResults(self::CHUNK_SIZE)
                ->setParameter('blog', $blog)
                ->setParameter('lastId', $lastId)
                ->getQuery()
                ->getSingleColumnResult();

            if (empty($ids)) {
                break;
            }

            /** @var Post[] $posts */
            $posts = $this->em->createQueryBuilder()
                ->select('p', 'v')
                ->from(Post::class, 'p')
                ->leftJoin('p.variants', 'v')
                ->where('p.id IN (:ids)')
                ->orderBy('p.id', 'ASC')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->getResult();

            foreach ($posts as $post) {
                if ($post->getFeaturedImageUrl()) {
                    $featuredImageUrl = $updater->update($post->getFeaturedImageUrl());
                    if ($featuredImageUrl !== false) {
                        $post->setFeaturedImageUrl($featuredImageUrl);
                    }
                }

                foreach ($post->getVariants() as $variant) {
                    // TODO: use lock for updating variants
                    try {
                        $content = $variant->getContent();
                        if ($content !== null) {
                            $variant->setContent($this->updateContentUrls($content, $updater, $updateLinks));
                        }

                        $contentUnsaved = $variant->getContentUnsaved();
                        if ($contentUnsaved !== null) {
                            $variant->setContentUnsaved($this->updateContentUrls($contentUnsaved, $updater, $updateLinks));
                        }
                    } catch (PhrosemirrorException $e) {
                        // ignore and continue
                        $this->logger->error('Failed to update content URLs for post variant', [
                            'post_id' => $post->getId(),
                            'variant_id' => $variant->getId(),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $lastId = end($ids);
        }
    }

    /**
     * @throws PhrosemirrorException
     */
    private function updateContentUrls(string $content, UpdaterInterface $updater, bool $updateLinks): string
    {
        $document = $this->postContentService->getDocumentFromJson($content);
        $updated = new DocUrlUpdater($document)->updateFromUpdater($updater, updateMedia: true, updateLinks: $updateLinks);
        return $updated->toJson();
    }

    private function updateUsers(Blog $blog, UpdaterInterface $updater): void
    {
        $lastId = 0;

        while (true) {
            /** @var User[] $users */
            $users = $this->em->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u')
                ->where('u.blog = :blog')
                ->andWhere('u.id > :lastId')
                ->orderBy('u.id', 'ASC')
                ->setMaxResults(self::CHUNK_SIZE)
                ->setParameter('blog', $blog)
                ->setParameter('lastId', $lastId)
                ->getQuery()
                ->getResult();

            if (empty($users)) {
                break;
            }

            foreach ($users as $user) {
                if ($user->getPictureUrl() === null) {
                    continue;
                }

                $pictureUrl = $updater->update($user->getPictureUrl());
                if ($pictureUrl !== false) {
                    $user->setPictureUrl($pictureUrl);
                }
            }

            $this->em->flush();

            $lastId = end($users)->getId();
        }
    }

}
