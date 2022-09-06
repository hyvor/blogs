<?php

namespace App\Domains\Blog\Listeners;

use App\Domains\Blog\Events\BlogUpdatedEvent;
use App\Domains\Blog\Jobs\UpdateContentHtmlOfAllPostsJob;

/**
 * Re-calculates all post content HTML when certain blog settings (meta) changes
 */
class UpdateContentHtmlOfAllPostsListener
{

    public function handle(BlogUpdatedEvent $event)
    {

        $keys = [
            'seo_external_links_follow',
            'syntax_on',
            'syntax_line_numbers',
            'syntax_theme'
        ];

        foreach ($keys as $key) {
            if ($event->blog->getMeta($key) !== $event->blogOriginal->getMeta($key)) {
                UpdateContentHtmlOfAllPostsJob::dispatch($event->blog);
                return;
            }
        }

    }

}