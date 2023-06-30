<?php declare(strict_types=1);

namespace App\Domains\Blog\Fillers;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\Content\PostContentService;
use App\Domains\Post\PostRepository;
use App\Exceptions\SafetyException;
use App\Models\Blog;
use App\Models\Post;
use App\Models\PostAuthor;
use App\Models\PostTag;
use App\Models\PostVariant;

class PostFiller implements FillerInterface
{

    /**
     * @var array<array<string, string>>
     */
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

    public function fill() : void
    {
        $language = $this->blog->languages[0];

        if (!$language) {
            throw new SafetyException;
        }

        foreach ($this->data as $row) {
            $isPage = $row['type'] === 'page';
            $post = PostRepository::createPost($this->blog, [
                'is_page' => $isPage
            ]);

            $content = strval(file_get_contents(resource_path("posts/{$row['file']}")));
            $content = PostContentService::getJsonFromHtml($content, $this->blog);

            PostRepository::updatePost($post, [
                'published_at' => now()->getTimestamp(),
            ]);

            PostRepository::updatePostVariant($post, $language, [
                'slug' => $row['slug'],
                'status' => PostStatusEnum::PUBLISHED,
                'content' => $content,
                'title' => $row['title'],
                'description' => $row['description'] ?? '',
            ]);

            if (! $isPage) {

                if ($this->blog->tags[0])
                    PostTag::create(['post_id' => $post->id, 'tag_id' => $this->blog->tags[0]->id]);

                if ($this->blog->users[0])
                    PostAuthor::create(['post_id' => $post->id, 'user_id' => $this->blog->users[0]->id]);

            }
        }

        if (
            $this->blog->type === BlogTypeEnum::DEV ||
            $this->blog->type === BlogTypeEnum::PREVIEW
        ) {
            $posts = Post::factory()
                ->count(50)
                ->create(['blog_id' => $this->blog->id]);

            foreach ($posts as $post) {
                $languages = $this->blog->languages;

                foreach ($languages as $lang) {
                    // add variant
                    $variant = PostVariant::factory()->create([
                        'post_id' => $post->id,
                        'language_id' => $lang->id,
                        'status' => 'published',
                    ]);
                    PostRepository::updateVariantHtml($variant);
                }

                // add 1-3 post tags
                $tagIds = $this->blog->tags->pluck('id');

                for ($i = 0; $i < rand(1, 3); $i++) {
                    $randomId = $tagIds->random();
                    PostTag::create([
                        'post_id' => $post->id,
                        'tag_id' => $randomId,
                    ]);
                    $tagIds = $tagIds->reject(fn ($id) => $id === $randomId);
                }

                // add 1-3 post authors
                $userIds = $this->blog->users->pluck('id');
                for ($i = 0; $i < rand(1, 3); $i++) {
                    $randomId = $userIds->random();
                    PostAuthor::create([
                        'post_id' => $post->id,
                        'user_id' => $randomId,
                    ]);
                    $userIds = $userIds->reject(fn ($id) => $id === $randomId);
                }
            }
        }
    }
}
