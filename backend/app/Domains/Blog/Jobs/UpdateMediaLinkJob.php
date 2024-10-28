<?php

namespace App\Domains\Blog\Jobs;

use App\Models\PostVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateMediaLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $oldLink,
        public string $newLink
    )
    {
    }

    public function handle(): void
    {
        $posts = PostVariant::where('content', 'LIKE', "%{$this->oldLink}%")
            ->orWhere('content_unsaved', 'LIKE', "%{$this->newLink}%")
            ->get();

        foreach ($posts as $post) {
            $post->content = str_replace($this->oldLink, $this->newLink, $post->content);
            if ($post->content_unsaved) {
                $post->content_unsaved = str_replace($this->oldLink, $this->newLink, $post->content_unsaved);
            }
            $post->save();
        }
    }
}
