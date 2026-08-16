<?php

namespace App\Repository;

use App\Entity\PostSuggestion;
use App\Entity\PostVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PostSuggestion>
 */
class PostSuggestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PostSuggestion::class);
    }

    /**
     * Suggestions are post_variant-scoped: an id must belong to the given variant to be
     * returned here, so one variant's editing session can never read another variant's
     * (or another post's) suggestions/comments by guessing an id.
     *
     * @param string[] $ids
     * @return PostSuggestion[]
     */
    public function findByIdsAndVariant(PostVariant $variant, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        /** @var PostSuggestion[] $result */
        $result = $this->createQueryBuilder('s')
            ->where('s.id IN (:ids)')
            ->andWhere('s.post_variant = :variant')
            ->setParameter('ids', $ids)
            ->setParameter('variant', $variant)
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function findOneByIdAndVariant(PostVariant $variant, string $id): ?PostSuggestion
    {
        return $this->findOneBy([
            'id' => $id,
            'post_variant' => $variant,
        ]);
    }
}
