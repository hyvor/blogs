<?php

namespace App\Api\Data\Object;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\User;
use App\Service\Route\PermalinkService;

class AuthorObject
{
    public int $id;
    public int $created_at;
    public string $slug;
    public string $url;
    public string $name;
    public ?string $picture_url;
    public ?string $bio;
    public ?string $website_url;
    public ?string $location;
    public SocialMediaObject $social;
    public int $posts_count;
    public LanguageObject $language;
    /** @var VariantObject[] */
    public array $variants = [];

    /**
     * @param array<array{language: Language, name: ?string, bio: ?string, location: ?string}> $variantData
     */
    public function __construct(User $user, Blog $blog, Language $language, PermalinkService $permalinkService, array $variantData = [])
    {
        $this->id = $user->getId();
        $this->created_at = $user->getCreatedAt()?->getTimestamp() ?? 0;
        $this->slug = $user->getSlug();
        $this->url = $permalinkService->getAuthorPermalink($user, $blog, $language);
        $this->picture_url = $user->getPictureUrl();
        $this->website_url = $user->getWebsiteUrl();
        $this->posts_count = $user->getPostsCount();
        $this->language = new LanguageObject($language);

        $this->social = new SocialMediaObject(
            $user->getSocialFacebook(),
            $user->getSocialTwitter(),
            $user->getSocialLinkedin(),
            $user->getSocialYoutube(),
            $user->getSocialInstagram(),
            $user->getSocialGithub(),
            $user->getSocialTiktok(),
        );

        $matched = null;
        $fallback = $variantData[0] ?? null;
        foreach ($variantData as $vd) {
            if ($vd['language']->getId() === $language->getId()) {
                $matched = $vd;
            } else {
                $url = $permalinkService->getAuthorPermalink($user, $blog, $vd['language']);
                $this->variants[] = new VariantObject(new LanguageObject($vd['language']), $url);
            }
        }

        $resolved = $matched ?? $fallback;
        $this->name = $resolved['name'] ?? '';
        $this->bio = $resolved['bio'] ?? null;
        $this->location = $resolved['location'] ?? null;
    }
}
