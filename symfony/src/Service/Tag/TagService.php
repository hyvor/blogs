<?php

namespace App\Service\Tag;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Tag;
use App\Entity\TagVariant;
use App\Service\Language\LanguageService;
use App\Service\Tag\Event\TagCreatedEvent;
use App\Service\Tag\Event\TagDeletedEvent;
use App\Service\Tag\Event\TagUpdatedEvent;
use App\Service\Tag\Event\TagVariantCreatedEvent;
use App\Service\Tag\Event\TagVariantDeletedEvent;
use App\Service\Tag\Event\TagVariantUpdatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\FilterQ\FilterQ;
use Hyvor\FilterQ\Keys;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class TagService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private LanguageService $languageService,
        private EventDispatcherInterface $ed,
    ) {}

    public function getTagById(Blog $blog, int $id): ?Tag
    {
        /** @var Tag|null */
        return $this->em->getRepository(Tag::class)->findOneBy(['id' => $id, 'blog' => $blog]);
    }

    /**
     * @param int[] $ids
     * @return Tag[]
     */
    public function getTagsByIds(Blog $blog, array $ids): array
    {
        /** @var Tag[] */
        return $this->em->getRepository(Tag::class)->findBy(['id' => $ids, 'blog' => $blog]);
    }

    public function getTagBySlug(Blog $blog, string $slug): ?Tag
    {
        /** @var Tag|null */
        return $this->em->getRepository(Tag::class)->findOneBy(['slug' => $slug, 'blog' => $blog]);
    }

    /**
     * @return Tag[]
     */
    public function getTags(Blog $blog, int $limit, int $offset = 0): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('t')
            ->from(Tag::class, 't')
            ->leftJoin('t.variants', 'tv')
            ->addSelect('tv')
            ->where('t.blog = :blog')
            ->setParameter('blog', $blog)
            ->orderBy('t.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        /** @var Tag[] */
        return $qb->getQuery()->getResult();
    }

    /**
     * @return Tag[]
     */
    public function searchTags(Blog $blog, string $search, int $limit = 10): array
    {
        $primaryLanguage = $this->languageService->getPrimaryLanguage($blog);

        $search = str_replace('%', '', $search) . '%';

        $qb = $this->em->createQueryBuilder();
        $qb->select('t')
            ->from(Tag::class, 't')
            ->join('t.variants', 'tv')
            ->where('t.blog = :blog')
            ->andWhere('tv.language = :language')
            ->andWhere('tv.name LIKE :search')
            ->setParameter('blog', $blog)
            ->setParameter('language', $primaryLanguage)
            ->setParameter('search', $search)
            ->setMaxResults($limit);

        /** @var Tag[] */
        return $qb->getQuery()->getResult();
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
                ->keys(function (Keys $keys) {
                    $keys->add('id', 't.id')->valueType('int');
                    $keys->add('slug', 't.slug')->valueType('string')->operators('=,!=');
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

    public function createTag(Blog $blog, string $name, bool $isPrivate = false, bool $flush = true, ?Language $primaryLanguage = null): Tag
    {
        $now = $this->now();

        $tag = new Tag();
        $tag->setBlog($blog);
        $tag->setSlug($this->generateUniqueSlug($blog, $name));
        $tag->setIsPrivate($isPrivate);
        $tag->setCreatedAt($now);
        $tag->setUpdatedAt($now);

        $this->em->persist($tag);

        $primaryLanguage ??= $this->languageService->getPrimaryLanguage($blog);

        $variant = new TagVariant();
        $variant->setTag($tag);
        $variant->setLanguage($primaryLanguage);
        $variant->setName($name);
        $variant->setCreatedAt($now);
        $variant->setUpdatedAt($now);

        $this->em->persist($variant);
        $tag->getVariants()->add($variant);

        if ($flush) {
            $this->em->flush();
            $this->ed->dispatch(new TagCreatedEvent($tag));
        }

        $blog->getTags()->add($tag);

        return $tag;
    }

    /**
     * @param array{is_private?: bool, slug?: string, code_head?: string, code_foot?: string} $updates
     */
    public function updateTag(Tag $tag, array $updates): Tag
    {
        $tagOld = clone $tag;

        if (isset($updates['is_private'])) {
            $tag->setIsPrivate($updates['is_private']);
        }
        if (isset($updates['slug'])) {
            $tag->setSlug($updates['slug']);
        }
        if (isset($updates['code_head'])) {
            $tag->setCodeHead($updates['code_head']);
        }
        if (isset($updates['code_foot'])) {
            $tag->setCodeFoot($updates['code_foot']);
        }

        $tag->setUpdatedAt($this->now());

        $this->em->flush();

        $this->ed->dispatch(new TagUpdatedEvent($tag, $tagOld));

        return $tag;
    }

    public function deleteTag(Tag $tag): void
    {
        foreach ($tag->getVariants() as $variant) {
            $this->em->remove($variant);
        }

        $this->em->remove($tag);
        $this->em->flush();

        $this->ed->dispatch(new TagDeletedEvent($tag));
    }

    public function getTagVariant(Tag $tag, Language $language): ?TagVariant
    {
        /** @var TagVariant|null */
        return $this->em->getRepository(TagVariant::class)->findOneBy([
            'tag' => $tag,
            'language' => $language,
        ]);
    }

    public function createTagVariant(Tag $tag, Language $language): TagVariant
    {
        $now = $this->now();

        $variant = new TagVariant();
        $variant->setTag($tag);
        $variant->setLanguage($language);
        $variant->setCreatedAt($now);
        $variant->setUpdatedAt($now);

        $this->em->persist($variant);
        $this->em->flush();

        $tag->getVariants()->add($variant);

        $this->ed->dispatch(new TagVariantCreatedEvent($variant));

        return $variant;
    }

    public function updateTagVariant(TagVariant $variant, ?string $name, ?string $description): TagVariant
    {
        $variantOld = clone $variant;

        if ($name !== null) {
            $variant->setName($name);
        }
        if ($description !== null) {
            $variant->setDescription($description);
        }

        $variant->setUpdatedAt($this->now());

        $this->em->flush();

        $this->ed->dispatch(new TagVariantUpdatedEvent($variant, $variantOld));

        return $variant;
    }

    public function deleteTagVariant(TagVariant $variant): void
    {
        $this->em->remove($variant);
        $this->em->flush();

        $this->ed->dispatch(new TagVariantDeletedEvent($variant));
    }

    private function generateUniqueSlug(Blog $blog, string $name): string
    {
        $slugger = new AsciiSlugger();
        $checks = [
            $name,
            $name . '-1',
            $name . '-' . bin2hex(random_bytes(6))
        ];

        foreach ($checks as $check) {
            $slug = (string) $slugger->slug($check)->lower();

            if ($this->getTagBySlug($blog, $slug) === null) {
                return $slug;
            }
        }

        throw new \RuntimeException('Unable to generate unique slug for tag.');
    }
}
