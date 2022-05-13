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
    
    /**
    * @var array<array<string,mixed>>
    */
    public array $pages = [];

    public function language(
        ?string $language,
        ?string $languageCode,
    )
    {
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
        ?string $created_at = null,
        ?string $updated_at = null,
        ?int $posts_count = 0,
        ?string $code_head = null,
        ?string $code_foot = null,
        ?string $featured_image = null,
        string $name = null,
        ?string $description = null,
    )
    {
        $this->tags[] = [
            'id' => $id,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'slug' => $slug,
            'posts_count' => $posts_count,
            'code_head' => $code_head,
            'code_foot' => $code_foot,
            'featured_image_url' => $featured_image,
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
        ?string $created_at = null,
        ?string $updated_at = null,
        ?string $picture_url = null,
        ?string $url = null,
        ?string $social_facebook = null,
        ?string $social_twitter = null,
        ?string $social_linkedin = null,
        ?string $social_youtube = null,
        ?string $social_instagram = null,
        ?string $name = null,
        ?string $bio = null,
        ?string $location = null,
    )
    {
        $this->authors[] = [
            'id' => $id,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'picture_url' => $picture_url,
            'status' => $status->value,
            'role' => $role->value,
            'slug' => $slug,
            'email' => $email,
            'url' => $url,
            'social_facebook' => $social_facebook,
            'social_twitter' => $social_twitter,
            'social_linkedin' => $social_linkedin,
            'social_youtube' => $social_youtube,
            'social_instagram' => $social_instagram,
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
        bool $is_page,
        string $slug,
        string $status, // checking whether the post is published or not
        array $authors = null,
        ?string $created_at = null,
        ?string $updated_at = null,
        ?string $published_at = null,
        ?bool $is_featured = false,
        ?string $featured_image_url = null,
        ?string $canonical_url = null,
        ?string $code_head = null,
        ?string $code_foot = null,
        ?string $content = null,
        ?string $title = null,
        ?string $description = null, 
        ?array $tags = null,      
    )
    {
        $this->posts[] = [
            'id' => $id,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'published_at' => $published_at,
            'is_page' => $is_page,
            'is_featured' => $is_featured,
            'slug' => $slug,
            'featured_image_url' => $featured_image_url,
            'canonical_url' => $canonical_url,
            'code_head' => $code_head,
            'code_foot' => $code_foot,
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

    public function page(
        int $id,
        bool $is_page,
        string $slug,
        string $status, // checking whether the post is published or not
        array $authors = null,

        ?string $created_at = null,
        ?string $updated_at = null,
        ?string $published_at = null,
        ?bool $is_featured = false,
        ?string $featured_image_url = null,
        ?string $canonical_url = null,
        ?string $code_head = null,
        ?string $code_foot = null,
        ?string $content = null,
        ?string $title = null,
        ?string $description = null, 
        ?array $tags = null,     
    )
    {
        $this->pages[] = [
            'id' => $id,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'published_at' => $published_at,
            'is_page' => $is_page,
            'is_featured' => $is_featured,
            'slug' => $slug,
            'featured_image_url' => $featured_image_url,
            'canonical_url' => $canonical_url,
            'code_head' => $code_head,
            'code_foot' => $code_foot,
            'status' => $status,
            'title' => $title,
            'description' => $description,
            'tags' => $tags,
            'authors' => $authors,
            'content' => $content,
        ];
        // dd($title);
        // dd($this->pages);
        return $this->pages;
    }
} 