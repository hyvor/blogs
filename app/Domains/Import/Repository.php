<?php

namespace App\Domains\Import;

use App\Data\Enums\UserRoleEnum;
use App\Data\Enums\UserStatusEnum;


class Repository
{
    /**
    * @var array<array<string,mixed>>
    */
    public array $lang = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $authors = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $tags = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $posts = [];


    public array $userModels = [];

    public array $tagModels = [];

    public array $postModels = [];
    /**
    * @var array<array<string,mixed>>
    */
    // public array $pages = [];

    public function userModels(?object $user){
        $this->userModels[] = $user;
    }

    public function tagModels(?object $tags){
        $this->tagModels[] = $tags;
    }

    public function postModels(?object $post){
        $this->postModels[] = $post;
    }


    public function language(
        ?string $language,
        ?string $languageCode,
    ) {
        // dd($language);
        $this->lang[] = [
            'language' => $language,
            'languageCode' => $languageCode,
        ];

        return $this->lang;
    }

    // Not null :- slug, name
    public function tag(
        int $id,
        string $slug,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?int $postsCount = 0,
        ?string $codeHead = null,
        ?string $codeFoot = null,
        ?string $featuredImageUrl = null,
        string $name = null,
        ?string $description = null,
    ) {
        $this->tags[] = [
            'id' => $id,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
            'slug' => $slug,
            'postsCount' => $postsCount,
            'codeHead' => $codeHead,
            'codeFoot' => $codeFoot,
            'featuredImageUrl' => $featuredImageUrl,
            'name' => $name,
            'description' => $description,
        ];
        // dd($slug);
        // dd($this->tags);
        return $this->tags;
    }

    // Not null :- status, role, slug, email,
    public function author(
        int $id,
        UserStatusEnum $status,
        UserRoleEnum $role,
        string $slug,
        string $email,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $pictureUrl = null,
        ?string $url = null,
        ?string $socialFacebook = null,
        ?string $socialTwitter = null,
        ?string $socialLinkedin = null,
        ?string $socialYoutube = null,
        ?string $socialInstagram = null,
        ?string $name = null,
        ?string $bio = null,
        ?string $location = null,
    ) {
        $this->authors[] = [
            'id' => $id,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
            'pictureUrl' => $pictureUrl,
            'status' => $status->value,
            'role' => $role->value,
            'slug' => $slug,
            'email' => $email,
            'url' => $url,
            'socialFacebook' => $socialFacebook,
            'socialTwitter' => $socialTwitter,
            'socialLinkedin' => $socialLinkedin,
            'socialYoutube' => $socialYoutube,
            'socialInstagram' => $socialInstagram,
            'name' => $name,
            'bio' => $bio,
            'location' => $location,
        ];
        // dd($this->authors);
        // dd($slug);
        return $this->authors;
    }

    // Not null :- status, content(NA), is_page, is_featured, slug
    public function post(
        int $id,
        bool $isPage,
        string $slug,
        string $status, // checking whether the post is published or not
        array $authors = null,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $publishedAt = null,
        ?bool $isFeatured = false,
        ?string $featuredImageUrl = null,
        ?string $canonicalUrl = null,
        ?string $codeHead = null,
        ?string $codeFoot = null,
        ?string $content = null,
        ?string $title = null,
        ?string $description = null,
        ?array $tags = null,
    ) {
        $this->posts[] = [
            'id' => $id,
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
            'publishedAt' => $publishedAt,
            'isPage' => $isPage,
            'isFeatured' => $isFeatured,
            'slug' => $slug,
            'featuredImageUrl' => $featuredImageUrl,
            'canonicalUrl' => $canonicalUrl,
            'codeHead' => $codeHead,
            'codeFoot' => $codeFoot,
            'status' => $status,
            'title' => $title,
            'description' => $description,
            'tags' => $tags,
            'authors' => $authors,
            'content' => $content,
        ];
        // dd($published_at);
        // dd($this->posts);
        return $this->posts;
    }
}
