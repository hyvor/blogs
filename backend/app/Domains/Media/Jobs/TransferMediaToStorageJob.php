<?php

namespace App\Domains\Media\Jobs;

use App\Domains\Media\MediaRepository;
use App\Domains\Media\Services\MediaTransfer;
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
        // True if transferring from platform storage to custom storage, false for the reverse
        public bool $fromPlatformToCustom
    )
    {
    }

    public function handle(): void
    {
        $blog = Blog::findOrFail($this->blog_id);
        MediaTransfer::transferMediaToStorage($blog, $this->fromPlatformToCustom);
    }
}

