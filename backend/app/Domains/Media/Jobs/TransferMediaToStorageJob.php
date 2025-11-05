<?php

namespace App\Domains\Media\Jobs;

use App\Domains\Media\MediaRepository;
use App\Models\Blog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TransferMediaToStorageJob implements ShouldQueue
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $blog_id,
        public bool $fromPlatformToCustom
    )
    {
    }

    public function handle(): void
    {
        $blog = Blog::findOrFail($this->blog_id);
        MediaRepository::transferMediaToStorage($blog, $this->fromPlatformToCustom);
    }
}

