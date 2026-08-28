<?php

namespace App\Service\Post\Document;

use App\Entity\PostVariant;
use App\Entity\PostVariantStep;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ClearStepsMessageHandler
{
    use ClockAwareTrait;

    private const int RETENTION_DAYS = 7;

    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(ClearStepsMessage $message): void
    {
        $before = $this->now()->modify('-' . self::RETENTION_DAYS . ' days');

        $contentUnsavedVersionSubQuery = $this->em->createQueryBuilder()
            ->select('pv.content_unsaved_version')
            ->from(PostVariant::class, 'pv')
            ->where('pv = s.post_variant')
            ->getDQL();

        $this->em->createQueryBuilder()
            ->delete(PostVariantStep::class, 's')
            ->where('s.created_at < :before')
            ->andWhere('s.version <= (' . $contentUnsavedVersionSubQuery . ')')
            ->setParameter('before', $before)
            ->getQuery()
            ->execute();
    }
}
