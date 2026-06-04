<?php

namespace App\Api\Data\Factory;

use App\Api\Data\Object\AuthorObject;
use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\User;
use App\Entity\UserVariant;
use App\Service\Route\PermalinkService;
use Doctrine\ORM\EntityManagerInterface;

class AuthorObjectFactory
{
    public function __construct(
        private PermalinkService $permalinkService,
        private EntityManagerInterface $em,
    ) {}

    /**
     * @param array<array{language: Language, name: ?string, bio: ?string, location: ?string}> $variantData
     */
    public function create(User $user, Blog $blog, Language $language, array $variantData = []): AuthorObject
    {
        return new AuthorObject($user, $blog, $language, $this->permalinkService, $variantData);
    }

    /**
     * Loads all UserVariants from DB and builds AuthorObject.
     */
    public function createFromEntity(User $user, Blog $blog, Language $language): AuthorObject
    {
        /** @var UserVariant[] $userVariants */
        $userVariants = $this->em->getRepository(UserVariant::class)->findBy(['user' => $user]);

        // Load Language entities for each variant
        $variantData = [];
        foreach ($userVariants as $uv) {
            $lang = $this->em->getRepository(Language::class)->find($uv->getLanguageId());
            if ($lang === null) {
                continue;
            }
            $variantData[] = [
                'language' => $lang,
                'name' => $uv->getName(),
                'bio' => $uv->getBio(),
                'location' => $uv->getLocation(),
            ];
        }

        return new AuthorObject($user, $blog, $language, $this->permalinkService, $variantData);
    }
}
