<?php

namespace App\Data\Objects\DataAPI;

use App\Data\Objects\DataAPI\Helpers\VariantsHelper;
use App\Domains\Route\PermalinkRepository;
use App\Models\Blog;
use App\Models\Language;
use App\Models\User;

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

    /**
     * @var VariantObject[]
     */
    public array $variants;

    public function __construct(User $user, Blog $blog, Language $language)
    {
        $variants = $user->variants;

        $this->id = $user->id;
        $this->created_at = $user->created_at->timestamp;
        $this->slug = $user->slug;
        $this->url = PermalinkRepository::getAuthorPermalink($user, $blog, $language);
        $this->name = VariantsHelper::getVariantValue('name', $variants, $language);
        $this->picture_url = $user->picture_url;
        $this->bio = VariantsHelper::getVariantValue('bio', $variants, $language);
        $this->website_url = $user->website_url;
        $this->location = VariantsHelper::getVariantValue('location', $variants, $language);

        $this->social = new SocialMediaObject(
            $user->social_facebook,
            $user->social_twitter,
            $user->social_linkedin,
            $user->social_youtube,
            $user->social_instagram,
            $user->social_github,
            $user->social_tiktok
        );

        $this->language = new LanguageObject($language);

        $this->variants = $variants
            ->where('language_id', '!=', $language->id)
            ->map(function ($variant) use ($user, $blog) {
                $variantLanguage = $variant->language;
                $url = PermalinkRepository::getAuthorPermalink($user, $blog, $variantLanguage);

                return new VariantObject($variantLanguage, $url);
            })->toArray();

        $this->posts_count = $user->posts_count;
    }
}
