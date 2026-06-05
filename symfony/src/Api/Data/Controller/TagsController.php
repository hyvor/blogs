<?php

namespace App\Api\Data\Controller;

use App\Api\Data\DataApiHelper;
use App\Api\Data\Factory\TagObjectFactory;
use App\Api\Data\Input\GetTagInput;
use App\Api\Data\Input\GetTagsInput;
use App\Api\Data\KeysFilter;
use App\Api\Data\Object\PaginationObject;
use App\Api\Data\Resolver\MapBlogFromSubdomain;
use App\Entity\Blog;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class TagsController
{
    public const ALLOWED_SORTS = [
        'posts_count' => 't.posts_count',
        'created_at' => 't.created_at',
    ];

    public function __construct(
        private DataApiHelper $dataApiHelper,
        private TagObjectFactory $tagObjectFactory,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/tag', name: 'tag', methods: ['GET'])]
    public function tag(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetTagInput $input): JsonResponse
    {
        if ($input->id === null && $input->slug === null) {
            throw new UnprocessableEntityHttpException('Either id or slug is required');
        }

        $language = $this->dataApiHelper->getLanguage($blog, $input->language);

        $tag = null;
        if ($input->id !== null) {
            $tag = $this->em->getRepository(Tag::class)->findOneBy(['id' => $input->id, 'blog' => $blog]);
        } elseif ($input->slug !== null) {
            $tag = $this->em->getRepository(Tag::class)->findOneBy(['slug' => $input->slug, 'blog' => $blog]);
        }

        if ($tag === null) {
            throw new NotFoundHttpException('Tag not found');
        }

        $tagObject = $this->tagObjectFactory->createFromEntity($tag, $blog, $language);

        $filtered = KeysFilter::filter($tagObject, $input->keys);

        return new JsonResponse($filtered);
    }

    #[Route('/tags', name: 'tags', methods: ['GET'])]
    public function tags(#[MapBlogFromSubdomain] Blog $blog, #[MapQueryString] GetTagsInput $input): JsonResponse
    {
        $language = $this->dataApiHelper->getLanguage($blog, $input->language);
        $limit = $this->dataApiHelper->getLimit($input->limit);
        $page = $this->dataApiHelper->getPage($input->page);
        $offset = $this->dataApiHelper->getOffset($page, $limit);
        $orderBys = $this->dataApiHelper->getSort($input->sort, self::ALLOWED_SORTS);

        [$tags, $total] = $this->queryTags($blog, $input->filter, $limit, $offset, $orderBys, $input->visibility);

        $tagObjects = array_map(
            fn(Tag $tag) => $this->tagObjectFactory->createFromEntity($tag, $blog, $language),
            $tags
        );

        $filteredTags = KeysFilter::filter($tagObjects, $input->keys);

        return new JsonResponse([
            'data' => $filteredTags,
            'pagination' => new PaginationObject($limit, $page, $total),
        ]);
    }

    /**
     * @param array<array{0: string, 1: string}> $orderBys
     * @return array{0: Tag[], 1: int}
     */
    private function queryTags(Blog $blog, ?string $filter, int $limit, int $offset, array $orderBys, string $visibility): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t')
            ->from(Tag::class, 't')
            ->where('t.blog = :blog')
            ->setParameter('blog', $blog);

        if ($visibility === 'public') {
            $qb->andWhere('t.is_private = false OR t.is_private IS NULL');
        } elseif ($visibility === 'private') {
            $qb->andWhere('t.is_private = true');
        }

        if ($filter !== null && $filter !== '') {
            try {
                FilterQ::expression($filter)
                    ->queryBuilder($qb)
                    ->keys(function ($keys) {
                        $keys->add('id', 't.id')->valueType('int');
                        $keys->add('slug', 't.slug')->valueType('string');
                        $keys->add('posts_count', 't.posts_count')->valueType('int');
                        $keys->add('created_at', 't.created_at')->valueType('date');
                    })
                    ->addWhere();
            } catch (FilterQException $e) {
                throw new UnprocessableEntityHttpException($e->getMessage(), $e);
            }
        }

        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT t.id)');
        $totalFetch = $countQb->getQuery()->getSingleScalarResult();
        $total = is_numeric($totalFetch) ? (int)$totalFetch : 0;

        if ($total === 0) {
            return [[], 0];
        }

        foreach ($orderBys as [$column, $direction]) {
            $qb->addOrderBy($column, $direction);
        }

        $qb->setMaxResults($limit)->setFirstResult($offset);

        /** @var Tag[] $tags */
        $tags = $qb->getQuery()->getResult();

        return [$tags, $total];
    }
}
