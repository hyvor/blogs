<?php

namespace App\Api\Console\Object;

use App\Entity\Enum\UserRole;
use App\Entity\User;

class UserObject
{
    public int $id;

    public int $created_at;

    public int $updated_at;

    public ?int $hyvor_user_id;

    public string $status;

    public UserRole $role;

    public string $slug;

    public int $posts_count;

    public ?string $email;

    public ?string $picture_url;

    public ?string $website_url;

    public ?string $social_facebook;
    public ?string $social_twitter;
    public ?string $social_linkedin;
    public ?string $social_youtube;
    public ?string $social_tiktok;
    public ?string $social_instagram;
    public ?string $social_github;

    /**
     * @var UserVariantObject[]
     */
    public array $variants;

    /**
     * @param UserVariantObject[] $variants
     */
    public function __construct(User $user, array $variants)
    {
        $this->id = $user->getId();
        $this->created_at = $user->getCreatedAt()->getTimestamp();
        $this->updated_at = $user->getUpdatedAt()?->getTimestamp() ?? 0;
        $this->hyvor_user_id = $user->getHyvorUserId();

        $this->status = $user->getStatus();
        $this->role = $user->getRole();
        $this->slug = $user->getSlug();
        $this->posts_count = $user->getPostsCount();

        $this->email = $user->getEmail();

        $this->picture_url = $user->getPictureUrl();
        $this->website_url = $user->getWebsiteUrl();

        $this->social_facebook = $user->getSocialFacebook();
        $this->social_twitter = $user->getSocialTwitter();
        $this->social_linkedin = $user->getSocialLinkedin();
        $this->social_youtube = $user->getSocialYoutube();
        $this->social_tiktok = $user->getSocialTiktok();
        $this->social_instagram = $user->getSocialInstagram();
        $this->social_github = $user->getSocialGithub();

        $this->variants = $variants;
    }
}
