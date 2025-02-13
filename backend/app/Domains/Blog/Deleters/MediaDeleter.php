<?php

namespace App\Domains\Blog\Deleters;

use App\Domains\Media\MediaRepository;
use App\Models\Blog;
use App\Models\Media;

class MediaDeleter implements DeleterInterface
{
    public function __construct(private Blog $blog)
    {
    }

    public function delete() : void
    {
        Media::where('blog_id', $this->blog->id)
            ->chunk(100, function ($medias) {

                /**
                 * @var Media $media
                 */
                foreach ($medias as $media) {
                    MediaRepository::delete($media);
                }
            });
    }
}
