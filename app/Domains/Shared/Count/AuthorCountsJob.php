<?php

namespace App\Domains\Shared\Count;

use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class AuthorCountsJob implements ShouldQueue, ShouldBeUnique
{

    use Dispatchable;

    public function __construct(public Blog $blog)
    {}

    public function handle()
    {

        $language = LanguageRepository::getPrimaryLanguage($this->blog);

        DB::statement('UPDATE users as u SET posts_count =
            (
                SELECT COUNT(post_author.id) 
                FROM post_author
                INNER JOIN post_variants on post_author.post_id = post_variants.post_id
                WHERE
                      post_author.user_id = u.id AND
                      post_variants.language_id = ? AND 
                      post_variants.status = ?
            )
            WHERE u.blog_id = ?
        ', [$language->id, 'published', $this->blog->id]);

    }

    public function uniqueId()
    {
        return $this->blog->id;
    }

}