<?php

namespace App\Domains\Import;

use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Models\Import;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\PostVariant;
use App\Models\Tag;
use App\Models\TagVariant;
use App\Models\User;
use App\Models\UserVariant;

class Importer
{
    /**
    * @var array<array<string,mixed>>
    */
    public array $tagIdArray = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $authorIdArray = [];

    public function __construct(Repository $repository, Blog $blog, Import $import)
    {
        $this->repository = $repository;
        $this->blog = $blog;
        $this->import = $import;
    }

    public function import()
    {
        dd($this->repository->user);
        
        $authorCount = count($this->repository->authors);
        $tagCount = count($this->repository->tags);
        $postCount = count($this->repository->posts);
        // $pageCount = count($this->repository->pages);s

        // $test = $this->import->setMeta([
        //     'authors_count' => $authorCount,
        //     'tags_count' => $tagCount,
        //     'posts_count' => $postCount,
        //     'pages_count' => $pageCount,
        // ]);

        // dd($this->import->setMeta(['authors_count' => 12]));

        foreach ($this->repository->lang as $lang) {
            // dd($tag);
            // dd($tag['name']);
            self::language(
                language: $lang['language'],
                languageCode: $lang['languageCode'],
            );
        }

        foreach ($this->repository->tags as $tag) {
            // dd($tag);
            // dd($tag['name']);
            self::tag(
                id: $tag['id'],
                createdAt: $tag['createdAt'],
                updatedAt: $tag['updatedAt'],
                slug: $tag['slug'],
                postsCount: $tag['postsCount'],
                codeHead: $tag['codeHead'],
                codeFoot: $tag['codeFoot'],
                featuredImageUrl: $tag['featuredImageUrl'],
                name: $tag['name'],
                description: $tag['description'],
            );
        }

        foreach ($this->repository->authors as $author) {
            // dd($author);
            // dd($author['name']);
            self::author(
                id: $author['id'],
                createdAt: $author['createdAt'],
                updatedAt: $author['updatedAt'],
                pictureUrl: $author['pictureUrl'],
                status: $author['status'],
                role: $author['role'],
                slug: $author['slug'],
                email: $author['email'],
                url: $author['url'],
                socialFacebook: $author['socialFacebook'],
                socialTwitter: $author['socialTwitter'],
                socialLinkedin: $author['socialLinkedin'],
                socialYoutube: $author['socialYoutube'],
                socialInstagram: $author['socialInstagram'],
                name: $author['name'],
                bio: $author['bio'],
                location: $author['location'],
            );
        }

        foreach ($this->repository->posts as $post) {
            // dd($post);
            // dd($post['authors']);
            self::post(
                createdAt: $post['createdAt'],
                updatedAt: $post['updatedAt'],
                publishedAt: $post['publishedAt'],
                isPage: $post['isPage'],
                slug: $post['slug'],
                featuredImageUrl: $post['featuredImageUrl'],
                canonicalUrl: $post['canonicalUrl'],
                codeHead: $post['codeHead'],
                codeFoot: $post['codeFoot'],
                status: $post['status'],
                title: $post['title'],
                description: $post['description'],
                tags: $post['tags'],
                authors: $post['authors'],
                content: $post['content'],
                isFeatured: $post['isFeatured'],
            );
        }

        // foreach($this->repository->pages as $page) {
        //     // dd($page);
        //     // dd($page['content']);
        //     self::page(
        //         created_at: $page['created_at'],
        //         updated_at: $page['updated_at'],
        //         published_at: $page['published_at'],
        //         is_page: $page['is_page'],
        //         slug: $page['slug'],
        //         featured_image_url: $page['featured_image_url'],
        //         canonical_url: $page['canonical_url'],
        //         code_head: $page['code_head'],
        //         code_foot: $page['code_foot'],
        //         status: $page['status'],
        //         title: $page['title'],
        //         description: $page['description'],
        //         tags: $page['tags'],
        //         authors: $page['authors'],
        //         content: $page['content'],
        //         is_featured: $page['is_featured'],
        //     );
        // }
    }

    public function language(
        ?string $language,
        ?string $languageCode,
    ) {
        // dd($language);
        // $test = LanguageRepository::createLanguage($this->blog, $languageCode, $language);
    }

    public function tag(
        int $id,
        string $createdAt = null,
        string $updatedAt = null,
        string $slug,
        string $codeHead = null,
        string $codeFoot = null,
        string $featuredImageUrl = null,
        string $name = null,
        string $description = null,
        int $postsCount = 0,
    ) {
        // dd($updated_at);
        $tag = Tag::create([
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'blog_id' => $this->blog->id,
            'slug' => $slug,
            'posts_count' => $postsCount,
            'code_head' => $codeHead,
            'code_foot' => $codeFoot,
        ]);

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();
        $primaryLanguage = $getLanguage->id;

        TagVariant::create([
            'tag_id' => $tag->id,
            'language_id' => $primaryLanguage,
            'name' => $name,
            'description' => $description,
        ]);

        $this->tagsArray[] = [$id => $tag->id];
    }

    public function author(
        int $id,
        string $createdAt = null,
        string $updatedAt = null,
        string $pictureUrl = null,
        $status,
        $role,
        string $slug,
        string $email,
        string $url = null,
        string $socialFacebook = null,
        string $socialTwitter = null,
        string $socialLinkedin = null,
        string $socialYoutube = null,
        string $socialInstagram = null,
        string $name = null,
        string $bio = null,
        string $location = null,
    ) {
        // dd($status);

        $user = User::create([
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'blog_id' => $this->blog->id,
            'picture_url' => $pictureUrl,
            'status' => $status,
            'role' => $role,
            'slug' => $slug,
            'email' => $email,
            'website_url' => $url,
            'social_facebook' => $socialFacebook,
            'social_twitter' => $socialTwitter,
            'social_linkedin' => $socialLinkedin,
            'social_youtube' => $socialYoutube,
            'social_instagram' => $socialInstagram,
        ]);

        $getLanguage = $this->blog->languages()->where('is_primary', true)->first();

        UserVariant::create([
            'user_id' => $user->id,
            'language_id' => $getLanguage->id,
            'name' => $name,
            'bio' => $bio,
            'location' => $location,
        ]);

        $this->authorsArray[] = [$id => $user->id];
    }

    public function post(
        string $createdAt = null,
        string $updatedAt = null,
        string $publishedAt = null,
        bool $isPage,
        string $slug,
        string $featuredImageUrl = null,
        string $canonicalUrl = null,
        string $codeHead = null,
        string $codeFoot = null,
        string $status, // checking whether the post is published or not
        string $title = null,
        string $description = null,
        array $tags = null,
        array $authors = null,
        string $content = null,
        bool $isFeatured = false,
    ) {
        // dd($featured_image_url);
        // dd($this->tagsArray);

        $slugExist = Post::where('blog_id', $this->blog->id)
            ->where('slug', $slug)
            ->first();

        if ($slugExist) {
            $slug = $slug.' 1';
        }

        $post = Post::create([
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'published_at' => $publishedAt,
            'blog_id' => $this->blog->id,
            'is_page' => $isPage,
            'is_featured' => $isFeatured,
            'slug' => $slug,
            'featured_image_url' => $featuredImageUrl,
            'canonical_url' => $canonicalUrl,
            'code_head' => $codeHead,
            'code_foot' => $codeFoot,
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
        foreach ($this->tagsArray as $tagData) {
            foreach ($tagData as $tagKey => $tagValue) {
                if ($tags != null) {
                    foreach ($tags as $tag) {
                        // dd($tagKey, $tag);
                        if ($tagKey == $tag) {
                            $postTag = PostTag::create([
                                'post_id' => $post->id,
                                'tag_id' => $tagValue,
                            ]);
                        }
                        // dd($postTag);
                    }
                }
            }
        }

        // post_authors section
        foreach ($this->authorsArray as $authorData) {
            foreach ($authorData as $authorKey => $authorValue) {
                foreach ($authors as $author) {
                    // dd($authorKey, $author);
                    if ($authorKey == $author) {
                        $postAuthor = PostAuthor::create([
                            'post_id' => $post->id,
                            'user_id' => $authorValue,
                        ]);
                    }
                    // dd($postAuthor);
                }
            }
        }
    }
}
