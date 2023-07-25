<?php

namespace App\Domains\Post\Jobs;

use App\Data\Enums\PostStatusEnum;
use App\Domains\Post\PostRepository;
use App\Models\PostVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class PublishScheduledPosts implements ShouldQueue, ShouldBeUnique
{

    use Queueable;

    public function handle() : void
    {
        $time = now();

        PostVariant::select('post_variants.*')
            ->where('status', PostStatusEnum::SCHEDULED)
            ->whereRaw('
                (SELECT p.published_at FROM posts as p
                WHERE p.id = post_variants.post_id
                LIMIT 1) <= ?
            ', [$time])
            ->orderBy('post_variants.id')
            ->chunk(1000, function ($variants) {
                foreach ($variants as $variant) {
                    PostRepository::updatePostVariant($variant, [
                        'status' => PostStatusEnum::PUBLISHED
                    ]);
                }
            });
    }
}
