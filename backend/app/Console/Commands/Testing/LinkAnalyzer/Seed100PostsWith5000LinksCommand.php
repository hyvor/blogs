<?php

namespace App\Console\Commands\Testing\LinkAnalyzer;

use App\Models\Blog;
use App\Models\Post;
use App\Models\PostVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class Seed100PostsWith5000LinksCommand extends Command
{

    protected $signature = 'testing:link-analyzer:seed-100-posts-with-5000-links';
    protected $description = 'Seed 100 posts with 5000 links';

    public function handle(): void
    {
        assert(App::environment('local'));

        $blog = Blog::first();

        if (!$blog) {
            $this->error('No blog found');
            return;
        }

        $this->info('Seeding 100 posts with 5000 links for blog: ' . $blog->subdomain);

        for ($i = 0; $i < 100; $i++) {
            $post = Post::factory()->create([
                'blog_id' => $blog->id,
                'published_at' => now(),
            ]);
            PostVariant::factory()->create([
                'content' => $this->getContent(),
                'post_id' => $post->id,
                'status' => 'published',
                'language_id' => $blog->languages->first()?->id,
            ]);
            $this->info("Post $post->id seeded");
        }
    }

    private function getContent(): string
    {
        $links = [];
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 50; $i++) {
            $links[] = [
                'type' => 'text',
                'text' => "link",
                'marks' => [
                    [
                        'type' => 'link',
                        'attrs' => [
                            'href' => $faker->url,
                        ],
                    ],
                ],
            ];
        }

        return (string)json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => $links,
                ],
            ],
        ]);
    }

}