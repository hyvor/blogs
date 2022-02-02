<?php

namespace App\Data\Objects\DataAPI;

use App\Domains\Blog\BlogRepository;
use App\Models\Blog;
use App\Models\User;

class AuthorObject
{
    public int $id;
    public string $slug;
    public string $url;
    public string $name;
    public ?string $profile_image;
    public ?string $bio;
    public ?string $website_url;
    public ?string $location;
    public SocialMediaObject $social;
    public int $posts_count;

    public function __construct(User $user, Blog $blog)
    {

        $this->id = $user->id;
        $this->slug = $user->slug;
        $this->url = BlogRepository::getFullUrlFromSlug($blog, 'author/' . $user->slug);
        $this->name = $user->name;
        $this->profile_image = $user->profile_image;
        $this->bio = $user->bio;
        $this->website_url = $user->website_url;
        $this->location = $user->location;

        $this->social = new SocialMediaObject(
            $user->social_facebook,
            $user->social_twitter,
            $user->social_linkedin,
            $user->social_youtube,
            $user->social_instagram,
            $user->social_github
        );

        $this->posts_count = $user->posts_count;
    }
}
