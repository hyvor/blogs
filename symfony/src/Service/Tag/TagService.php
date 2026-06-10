<?php

namespace App\Service\Tag;

use App\Entity\Blog;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;

class TagService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function getTagById(Blog $blog, int $id): ?Tag
    {
        /** @var Tag|null */
        return $this->em->getRepository(Tag::class)->findOneBy(['id' => $id, 'blog' => $blog]);
    }

    public function getTagBySlug(Blog $blog, string $slug): ?Tag
    {
        /** @var Tag|null */
        return $this->em->getRepository(Tag::class)->findOneBy(['slug' => $slug, 'blog' => $blog]);
    }

    /**
     * @param array<array{0: string, 1: string}> $orderBys
     * @param 'public'|'private'|'any' $visibility
     * @return array{tags: Tag[], total: int}
     * @throws FilterQException
     */
    public function getTagsWithFilterQ(
        Blog $blog,
        ?string $filter,
        int $limit,
        int $offset,
        array $orderBys,
        string $visibility = 'public',
    ): array {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t')
            ->from(Tag::class, 't')
            ->where('t.blog = :blog')
            ->setParameter('blog', $blog);

        if ($visibility === 'public') {
            $qb->andWhere('t.is_private = false');
        } elseif ($visibility === 'private') {
            $qb->andWhere('t.is_private = true');
        }

        if ($filter !== null && $filter !== '') {
            FilterQ::expression($filter)
                ->queryBuilder($qb)
                ->keys(function ($keys) {
                    $keys->add('id', 't.id')->valueType('int');
                    $keys->add('slug', 't.slug')->valueType('string');
                    $keys->add('posts_count', 't.posts_count')->valueType('int');
                    $keys->add('created_at', 't.created_at')->valueType('date');
                })
                ->addWhere();
        }

        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT t.id)');
        $total = (int ) $countQb->getQuery()->getSingleScalarResult();

        if ($total === 0) {
            return ['tags' => [], 'total' => 0];
        }

        foreach ($orderBys as [$column, $direction]) {
            $qb->addOrderBy($column, $direction);
        }

        $qb->setMaxResults($limit)->setFirstResult($offset);

        /** @var Tag[] $tags */
        $tags = $qb->getQuery()->getResult();

        return ['tags' => $tags, 'total' => $total];
    }
}
