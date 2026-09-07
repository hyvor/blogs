<?php

namespace App\Service\Blog\MessageHandler;

use App\Entity\Blog;
use App\Service\Blog\BlogService;
use App\Service\Blog\Message\HardDeleteBlogsMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class HardDeleteBlogsMessageHandler
{
    use ClockAwareTrait;

    private const int RETENTION_DAYS = 30;

    public function __construct(
        private BlogService $blogService,
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(HardDeleteBlogsMessage $message): void
    {
        $blogs = $this->getBlogsToHardDelete($this->now()->modify('-' . self::RETENTION_DAYS . ' days'));

        foreach ($blogs as $blog) {
            $this->blogService->hardDeleteBlog($blog);
        }
    }

    /**
     * @return Blog[]
     */
    private function getBlogsToHardDelete(\DateTimeImmutable $before): array
    {
        /** @var Blog[] */
        return $this->em->getRepository(Blog::class)->createQueryBuilder('b')
            ->where('b.deleted_at IS NOT NULL')
            ->andWhere('b.deleted_at < :before')
            ->setParameter('before', $before)
            ->getQuery()
            ->getResult();
    }
}
