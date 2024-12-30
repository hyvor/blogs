<?php

namespace App\Domains\Shared\Count;

use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class TagCountsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;

    public function __construct(public Blog $blog)
    {
    }

    public function handle() : void
    {
        $language = LanguageRepository::getPrimaryLanguage($this->blog);

        DB::statement('UPDATE tags as t SET posts_count =
            (
                SELECT COUNT(post_tag.id)
                FROM post_tag
                INNER JOIN post_variants on post_tag.post_id = post_variants.post_id
                INNER JOIN posts ON post_variants.post_id = posts.id
                WHERE
                      post_tag.tag_id = t.id AND
                      post_variants.language_id = ? AND
                      post_variants.status = "published" AND
                      posts.is_page = 0
            )
            WHERE t.blog_id = ?
        ', [$language->id, $this->blog->id]);
    }

    public function uniqueId() : int
    {
        return $this->blog->id;
    }
}
