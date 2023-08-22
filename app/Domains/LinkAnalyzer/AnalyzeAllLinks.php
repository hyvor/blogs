<?php declare(strict_types=1);

namespace App\Domains\LinkAnalyzer;

use App\Models\Blog;
use App\Models\Post;

class AnalyzeAllLinks
{

    public int $postsCount = 0;

    public int $linksCount = 0;
    public int $linksOkCount = 0;
    public int $linksBrokenCount = 0;
    public int $linksRedirectCount = 0;
    public int $linksIgnoredCount = 0;

    public function __construct(
        private Blog $blog
    ) {}

    public function analyze() : void
    {

        Post::where('blog_id', $this->blog->id)
            ->chunk(1000, function ($posts) {
                foreach ($posts as $post) {
                    $this->analyzePost($post);
                }
            });

    }


    private function analyzePost(Post $post) : void
    {
        $this->postsCount++;
        foreach ($post->variants as $variant) {
            $this->analyzeVariant($variant);
        }
    }

}