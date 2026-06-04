<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\PostObject;
use App\Entity\Blog;
use App\Entity\Enum\PostVariantStatus;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\PostAuthor;
use App\Entity\PostTag;
use App\Entity\PostVariant;
use App\Entity\Tag;
use App\Entity\User;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class PostObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
    ) {}

    /**
     * @param Tag[] $tags
     * @param User[] $authors
     * @param array<array{variant: PostVariant, language: Language}> $otherVariants
     */
    public function create(
        Post $post,
        PostVariant $variant,
        Blog $blog,
        Language $language,
        array $tags = [],
        array $authors = [],
        array $otherVariants = [],
    ): PostObject {
        return new PostObject($post, $variant, $blog, $language, $this->permalinkService, $tags, $authors, $otherVariants);
    }

    /**
     * Loads PostVariant, tags, authors, and other variants from DB, then builds PostObject.
     * Returns null if no published variant found for this language.
     */
    public function createFromEntity(Post $post, Blog $blog, Language $language): ?PostObject
    {
        // Load the variant for this language (published only)
        $variant = $this->em->getRepository(PostVariant::class)->findOneBy([
            'post' => $post,
            'language' => $language,
            'status' => PostVariantStatus::PUBLISHED,
        ]);

        if ($variant === null) {
            return null;
        }

        // Load tags via PostTag
        /** @var PostTag[] $postTags */
        $postTags = $this->em->getRepository(PostTag::class)->findBy(['post' => $post]);
        $tags = array_filter(array_map(fn(PostTag $pt) => $pt->getTag(), $postTags));

        // Load authors via PostAuthor
        /** @var PostAuthor[] $postAuthors */
        $postAuthors = $this->em->getRepository(PostAuthor::class)->findBy(['post' => $post]);
        $authors = array_filter(array_map(fn(PostAuthor $pa) => $pa->getUser(), $postAuthors));

        // Load other variants (all published variants except the current language)
        /** @var PostVariant[] $allVariants */
        $allVariants = $this->em->getRepository(PostVariant::class)->findBy([
            'post' => $post,
            'status' => PostVariantStatus::PUBLISHED,
        ]);

        $otherVariants = [];
        foreach ($allVariants as $pv) {
            if ($pv->getLanguageId() === $language->getId()) {
                continue;
            }
            $lang = $this->em->getRepository(Language::class)->find($pv->getLanguageId());
            if ($lang !== null) {
                $otherVariants[] = ['variant' => $pv, 'language' => $lang];
            }
        }

        return new PostObject(
            $post,
            $variant,
            $blog,
            $language,
            $this->permalinkService,
            array_values($tags),
            array_values($authors),
            $otherVariants,
        );
    }
}
