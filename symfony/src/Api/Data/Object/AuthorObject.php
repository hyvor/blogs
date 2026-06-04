<?php

namespace App\Api\Data\Object;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\User;
use App\Service\Route\PermalinkService;

class AuthorObject
{
    public int $id;
    public string $slug;
    public ?string $name;
    public ?string $bio;
    public ?string $picture_url;
    public ?string $website_url;
    public string $url;
    public int $posts_count;
    public LanguageObject $language;
    /** @var VariantObject[] */
    public array $variants = [];

    /**
     * @param array<array{language: Language, name: ?string, bio: ?string}> $variantData
     */
    public function __construct(User $user, Blog $blog, Language $language, PermalinkService $permalinkService, array $variantData = [])
    {
        $this->id = $user->getId();
        $this->slug = $user->getSlug();
        $this->picture_url = $user->getPictureUrl();
        $this->website_url = $user->getWebsiteUrl();
        $this->posts_count = $user->getPostsCount();
        $this->url = $permalinkService->getAuthorPermalink($user, $blog, $language);
        $this->language = new LanguageObject($language);

        $this->name = null;
        $this->bio = null;
        foreach ($variantData as $vd) {
            if ($vd['language']->getId() === $language->getId()) {
                $this->name = $vd['name'];
                $this->bio = $vd['bio'];
            } else {
                $url = $permalinkService->getAuthorPermalink($user, $blog, $vd['language']);
                $this->variants[] = new VariantObject(new LanguageObject($vd['language']), $url);
            }
        }
    }
}
