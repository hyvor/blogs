<?php
namespace App\Types\DataAPI\Output;

use App\Domains\Blog\BlogRepository;
use App\Models\Blog;
use App\Models\User;

class AuthorType {

    public int $id;
    public string $slug;
    public string $url;
    public string $name;
    public ?string $profile_image;
    public ?string $bio;
    public ?string $website_url;
    public ?string $location;
    public AuthorSocialType $social;
    public int $posts_count;

    public function __construct(User $user, Blog $blog) {

        $this->id = $user->id;
        $this->slug = $user->slug;
        $this->url = BlogRepository::getFullUrlFromSlug($blog, 'author/' . $user->slug);
        $this->name = $user->name;
        $this->profile_image = $user->profile_image;
        $this->bio = $user->bio;
        $this->website_url = $user->website_url;
        $this->location = $user->location;

        $this->social = new AuthorSocialType(
            $user->facebook,
            $user->twitter,
            $user->linkedin,
            $user->youtube,
            $user->instagram,
            $user->github
        );

        $this->posts_count = $user->posts_count;
        
    }

}


class AuthorSocialType {

    public ?string $facebook;
    public ?string $twitter;
    public ?string $linkedin;
    public ?string $youtube;
    public ?string $instagram;
    public ?string $github;

    public function __construct($facebook ,$twitter ,$linkedin ,$youtube, $instagram, $github) {
        $this->facebook = $facebook;
        $this->twitter = $twitter;
        $this->linkedin = $linkedin;
        $this->youtube = $youtube;
        $this->instagram = $instagram;
        $this->github = $github;
    }

}