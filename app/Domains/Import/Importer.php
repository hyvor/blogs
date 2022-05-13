<?php

namespace App\Domains\Import;

use App\Models\Import;
use App\Models\Blog;
use App\Models\User;
use App\Models\UserVariant;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\Post;
use App\Models\PostVariant;
use App\Models\PostTag;
use App\Models\PostAuthor;
use App\Domains\Import\Repository;
use App\Domains\Language\LanguageRepository;
class Importer
{
    /**
    * @var array<array<string,mixed>>
    */
    public array $tagsArray = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $authorsArray = [];

    public function __construct(Repository $repository, Blog $blog, Import $import)
    {
        $this->repository = $repository;
        $this->blog = $blog;
        $this->import = $import;
    }

    public function import()
    {
        // dd($this->repository);
        // dd($this->blog);

        $authorCount = count($this->repository->authors);
        $tagCount = count($this->repository->tags);
        $postCount = count($this->repository->posts);
        $pageCount = count($this->repository->pages);

        // $test = $this->import->setMeta([
        //     'authors_count' => $authorCount,
        //     'tags_count' => $tagCount,
        //     'posts_count' => $postCount,
        //     'pages_count' => $pageCount,
        // ]);

        // dd($this->import->setMeta(['authors_count' => 12]));

        foreach($this->repository->lang as $lang) {
            // dd($tag);
            // dd($tag['name']);
            self::language(
                language: $lang['language'],
                languageCode: $lang['languageCode'],
            );
        }
        
        foreach($this->repository->tags as $tag) {
            // dd($tag);
            // dd($tag['name']);
            self::tag(
                created_at: $tag['created_at'],
                updated_at: $tag['updated_at'],
                slug: $tag['slug'], 
                posts_count: $tag['posts_count'],
                code_head: $tag['code_head'],
                code_foot: $tag['code_foot'],
                featured_image_url: $tag['featured_image_url'],
                name: $tag['name'],
                description: $tag['description'],
            );
        }

        foreach($this->repository->authors as $author) {
            // dd($author);
            // dd($author['name']);
            self::author(
                created_at: $author['created_at'],
                updated_at: $author['updated_at'],
                picture_url: $author['picture_url'],
                status: $author['status'],
                role: $author['role'],
                slug: $author['slug'],
                email: $author['email'],
                url: $author['url'],
                social_facebook: $author['social_facebook'],
                social_twitter: $author['social_twitter'],
                social_linkedin: $author['social_linkedin'],
                social_youtube: $author['social_youtube'],
                social_instagram: $author['social_instagram'],
                name: $author['name'],
                bio: $author['bio'],
                location: $author['location'],
            );
        }

        foreach($this->repository->posts as $post) {
            // dd($post);
            // dd($post['authors']);
            self::post(
                created_at: $post['created_at'],
                updated_at: $post['updated_at'],
                published_at: $post['published_at'],
                is_page: $post['is_page'],
                slug: $post['slug'],
                featured_image_url: $post['featured_image_url'],
                canonical_url: $post['canonical_url'],
                code_head: $post['code_head'],
                code_foot: $post['code_foot'],
                status: $post['status'],
                title: $post['title'],
                description: $post['description'],
                tags: $post['tags'],
                authors: $post['authors'],
                content: $post['content'],
                is_featured: $post['is_featured'],
            );
        }

        foreach($this->repository->pages as $page) {
            // dd($page);
            // dd($page['content']);
            self::page(
                created_at: $page['created_at'],
                updated_at: $page['updated_at'],
                published_at: $page['published_at'],
                is_page: $page['is_page'],
                slug: $page['slug'],
                featured_image_url: $page['featured_image_url'],
                canonical_url: $page['canonical_url'],
                code_head: $page['code_head'],
                code_foot: $page['code_foot'],
                status: $page['status'],
                title: $page['title'],
                description: $page['description'],
                tags: $page['tags'],
                authors: $page['authors'],
                content: $page['content'],
                is_featured: $page['is_featured'],
            );
        }

    }

    public function language (
        ?string $language,
        ?string $languageCode,
    )
    {
        // dd($language);
        // $test = LanguageRepository::createLanguage($this->blog, $languageCode, $language);
    }

    public function tag(
        string $created_at = null,
        string $updated_at = null,
        string $slug,
        string $code_head = null,
        string $code_foot = null,
        string $featured_image_url = null,
        string $name = null,
        string $description = null,
        int $posts_count = 0,
    )
    {
        // dd($updated_at);
        $tag = Tag::create([
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'blog_id' => $this->blog->id,
            'slug' => $slug,
            'posts_count' => $posts_count,
            'code_head' => $code_head,
            'code_foot' => $code_foot,
        ]);

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();
        $primaryLanguage = $getLanguage->id;

        TagVariant::create([
            'tag_id' => $tag->id,
            'language_id' => $primaryLanguage,
            'name' => $name,
            'description' => $description,
        ]);

        $this->tagsArray[] = [$name => $tag->id];
    }

    public function author(
        string $created_at = null,
        string $updated_at = null,
        string $picture_url = null,
        $status,
        $role,
        string $slug,
        string $email,
        string $url = null,
        string $social_facebook = null,
        string $social_twitter = null,
        string $social_linkedin = null,
        string $social_youtube = null,
        string $social_instagram = null,
        string $name = null,
        string $bio = null,
        string $location = null,
    )
    {
        // dd($status);

        $user = User::create([
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'blog_id' => $this->blog->id,
            'picture_url' => $picture_url,
            'status' => $status,
            'role' => $role,
            'slug' => $slug, 
            'email' => $email,
            'url' => $url,
            'social_facebook' => $social_facebook,
            'social_twitter' => $social_twitter,
            'social_linkedin' => $social_linkedin,
            'social_youtube' => $social_youtube,
            'social_instagram' => $social_instagram,
        ]);

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();

        UserVariant::create([
            'user_id' => $user->id,
            'language_id' => $getLanguage->id,
            'name' => $name,
            'bio' => $bio,
            'location' => $location,
        ]);

        $this->authorsArray[] = [$name => $user->id];
    }

    public function post(
        string $created_at = null,
        string $updated_at = null,
        string $published_at = null,
        bool $is_page,
        string $slug,
        string $featured_image_url = null,
        string $canonical_url = null,
        string $code_head = null,
        string $code_foot = null,
        string $status, // checking whether the post is published or not
        string $title = null,
        string $description = null, 
        array $tags = null,
        array $authors = null,
        string $content = null,
        bool $is_featured = false,
    )
    {
        // dd($featured_image_url);
        // dd($this->tagsArray);

        $slugExist = Post::where('blog_id', $this->blog->id)
            ->where('slug', $slug)
            ->first();
        
        if ($slugExist) {
                $slug = $slug.' 1';
        }

        $post = Post::create([
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'published_at' => $published_at,
            'blog_id' => $this->blog->id,
            'is_page' => $is_page,
            'is_featured' => $is_featured,
            'slug' => $slug,
            'featured_image_url' => $featured_image_url,
            'canonical_url' => $canonical_url,
            'code_head' => $code_head,
            'code_foot' => $code_foot,
        ]);

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();

        PostVariant::create([
            'post_id' => $post->id,
            'language_id' => $getLanguage->id,
            'status' => $status,
            'content' => $content,
            'title' => $title,
            'description' => $description,
        ]);

        // post_tags section
        foreach($this->tagsArray as $tagData) {
            foreach($tagData as $tagKey=>$tagValue){
                foreach($tags as $tag) {
                    if( $tagKey == $tag){
                        $postTag = PostTag::create([
                            'post_id' => $post->id,
                            'tag_id' => $tagValue,
                        ]);
                    }
                }
            }
        }

        // post_authors section
        foreach($this->authorsArray as $authorData) {
            foreach($authorData as $authorKey=>$authorValue){
                foreach($authors as $author) {
                    if( $authorKey == $author){
                        $postAuthor = PostAuthor::create([
                            'post_id' => $post->id,
                            'user_id' => $authorValue,
                        ]);
                    }
                }
            }
        }
    }

    public function page(
        string $created_at = null,
        string $updated_at = null,
        string $published_at = null,
        bool $is_page,
        string $slug,
        string $featured_image_url = null,
        string $canonical_url = null,
        string $code_head = null,
        string $code_foot = null,
        string $status, // checking whether the post is published or not
        string $title = null,
        string $description = null, 
        array $tags = null,
        array $authors = null,
        string $content = null,
        bool $is_featured = false,
    )
    {
        // dd($is_page);

        $slugExist = Post::where('blog_id', $this->blog->id)
            ->where('slug', $slug)
            ->first();
        
        if ($slugExist) {
                $slug = $slug.' 1';
        }

        $page = Post::create([
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'published_at' => $published_at,
            'blog_id' => $this->blog->id,
            'is_page' => $is_page,
            'is_featured' => $is_featured,
            'slug' => $slug,
            'featured_image_url' => $featured_image_url,
            'canonical_url' => $canonical_url,
            'code_head' => $code_head,
            'code_foot' => $code_foot,
        ]);

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();

        PostVariant::create([
            'post_id' => $page->id,
            'language_id' => $getLanguage->id,
            'status' => $status,
            'content' => $content,
            'title' => $title,
            'description' => $description,
        ]);

        // post_tags section
        foreach($this->tagsArray as $tagData) {
            foreach($tagData as $tagKey=>$tagValue){
                foreach($tags as $tag) {
                    if( $tagKey == $tag){
                        $postTag = PostTag::create([
                            'post_id' => $page->id,
                            'tag_id' => $tagValue,
                        ]);
                    }
                }
            }
        }

        // post_authors section
        foreach($this->authorsArray as $authorData) {
            foreach($authorData as $authorKey=>$authorValue){
                foreach($authors as $author) {
                    if( $authorKey == $author){
                        $postAuthor = PostAuthor::create([
                            'post_id' => $page->id,
                            'user_id' => $authorValue,
                        ]);
                    }
                }
            }
        }

        return 'Import successful';
    }
}