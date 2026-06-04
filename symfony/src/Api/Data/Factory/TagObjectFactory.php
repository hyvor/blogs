<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\TagObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Tag;
use App\Entity\TagVariant;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class TagObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
    ) {}

    /**
     * @param array<array{language: Language, name: ?string, description: ?string}> $variantData
     */
    public function create(Tag $tag, Blog $blog, Language $language, array $variantData = []): TagObject
    {
        return new TagObject($tag, $blog, $language, $this->permalinkService, $variantData);
    }

    /**
     * Loads all TagVariants from DB and builds TagObject.
     */
    public function createFromEntity(Tag $tag, Blog $blog, Language $language): TagObject
    {
        /** @var TagVariant[] $tagVariants */
        $tagVariants = $this->em->getRepository(TagVariant::class)->findBy(['tag' => $tag]);

        $variantData = [];
        foreach ($tagVariants as $tv) {
            $lang = $this->em->getRepository(Language::class)->find($tv->getLanguageId());
            if ($lang === null) {
                continue;
            }
            $variantData[] = [
                'language' => $lang,
                'name' => $tv->getName(),
                'description' => $tv->getDescription(),
            ];
        }

        return new TagObject($tag, $blog, $language, $this->permalinkService, $variantData);
    }
}
