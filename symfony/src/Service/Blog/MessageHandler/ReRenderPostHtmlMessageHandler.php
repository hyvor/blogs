<?php

namespace App\Service\Blog\MessageHandler;

use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\PostVariant;
use App\Service\Blog\BlogService;
use App\Service\Blog\Message\ReRenderPostHtmlMessage;
use App\Service\Post\PostService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
class ReRenderPostHtmlMessageHandler
{

    public function __construct(
        private BlogService $blogService,
        private EntityManagerInterface $em,
        private PostService $postService
    ) {}

    public function __invoke(ReRenderPostHtmlMessage $message): void
    {
        $blog = $this->blogService->getBlogById($message->blogId);

        if (!$blog) {
            throw new UnrecoverableMessageHandlingException('Blog not found for ID: ' . $message->blogId);
        }

        $this->em->wrapInTransaction(fn() => $this->reRenderAll($blog));
    }

    private function reRenderAll(Blog $blog): void
    {
        $batchSize = 100;
        $lastId = 0;

        while (true) {
            $qb = $this->em->createQueryBuilder();

            /** @var PostVariant[] $variants */
            $variants = $qb->select('pv')
                ->from(PostVariant::class, 'pv')
                ->join('pv.post', 'p')
                ->where('p.blog = :blog')
                ->andWhere('pv.id > :lastId')
                ->andWhere('pv.status IN (:status)')
                ->orderBy('pv.id', 'ASC')
                ->setMaxResults($batchSize)
                ->setParameter('blog', $blog)
                ->setParameter('lastId', $lastId)
                ->setParameter('status', [PostVariantStatus::PUBLISHED, PostVariantStatus::SCHEDULED])
                ->getQuery()
                ->getResult();

            if (empty($variants)) {
                break;
            }

            foreach ($variants as $variant) {
                if ($variant->getContent() === null) {
                    continue;
                }
                $this->postService->cachePostVariantHtmlAndText($variant, $blog);
            }

            $lastId = end($variants)->getId();

            $this->em->flush();
            $this->em->clear();
        }
    }

}
