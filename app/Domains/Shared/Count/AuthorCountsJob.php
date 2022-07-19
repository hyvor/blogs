<?php

namespace App\Domains\Shared\Count;

use App\Domains\Language\LanguageRepository;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuthorCountsJob implements ShouldQueue, ShouldBeUnique
{

    public function __construct(public Blog $blog)
    {}

    public function handle()
    {

        $language = LanguageRepository::getPrimaryLanguage($this->blog);

        User::where('blog_id', $this->blog->id)
            ->leftJoin('post_author', fn ($join) =>
                $join->on('users.id', '=', 'post_author.id')
                    ->join('post_variants', fn ($join2) =>
                        $join2->on('post_variants.post_id', '=', 'post_author.post_id')
                            ->where('post_variants.language_id', $language->id)
                    )
            )
            ->groupBy('users.id')
            ->selectRaw('users.id as user_id, COUNT(post_author.id) as count')
            ->get();

    }

}