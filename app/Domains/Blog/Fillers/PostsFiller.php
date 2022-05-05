<?php

namespace App\Domains\Blog\Fillers;

use App\Domains\Post\Content\PostContentRepository;
use App\Domains\Post\PostRepository;
use App\Models\Blog;
use Illuminate\Support\Facades\App;

class PostsFiller implements FillerInterface
{
    private array $data = [

        // posts
        [
            'type' => 'post',
            'slug' => 'welcome',
            'title' => 'Welcome to Hyvor Blogs',
            'file' => 'post-welcome.html',
            'description' => 'A warm welcome to Hyvor Blogs. We have put together a few resources to help you get started with Hyvor Blogs',
        ],
        [
            'type' => 'post',
            'slug' => 'content-style',
            'title' => 'Content Style Guide',
            'file' => 'post-content-style.html',
            'description' => 'A post to show you all content styles available on Hyvor Blogs',
        ],

        // pages
        [
            'type' => 'page',
            'slug' => 'about',
            'title' => 'About',
            'file' => 'page-about.html',
        ],
        [
            'type' => 'page',
            'slug' => 'privacy',
            'title' => 'Privacy Policy',
            'file' => 'page-privacy.html',
        ],
        [
            'type' => 'page',
            'slug' => 'contact',
            'title' => 'Contact',
            'file' => 'page-contact.html',
        ],

    ];

    public function __construct(private Blog $blog)
    {
    }

    public function fill()
    {
        if (App::environment('testing')) {
            return;
        }

        foreach ($this->data as $row) {
            $isPage = $row['type'] === 'page';
            $post = PostRepository::createPost($this->blog, $isPage);

            $language = $this->blog->languages[0];

            PostRepository::createPostVariant($post, $language);

            $content = file_get_contents(resource_path("posts/{$row['file']}"));
            $content = PostContentRepository::getJsonFromHtml($content, $this->blog);

            PostRepository::updatePost($post, [
                'slug' => $row['slug'],
                'published_at' => now()->timestamp,
            ]);

            PostRepository::updatePostVariant($post, $language, [
                'status' => 'published',
                'content' => $content,
                'title' => $row['title'],
                'description' => $row['description'] ?? '',
            ]);
        }
    }
}
