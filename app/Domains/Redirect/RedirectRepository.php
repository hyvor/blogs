<?php

namespace App\Domains\Redirect;

use App\Data\Enums\RedirectTypeEnum;
use App\Domains\Redirect\Events\RedirectChangedEvent;
use App\Models\Blog;
use App\Models\Redirect;
use Illuminate\Support\Collection;

class RedirectRepository
{
    public static function getRedirects(Blog $blog, ?int $limit, int $offset = 0): Collection
    {
        return $blog->redirects()
            ->limit($limit)
            ->offset($offset)
            ->latest()
            ->get();
    }

    public static function createRedirect(
        Blog $blog,
        string $path,
        string $to,
        RedirectTypeEnum $type
    ): Redirect {
        $redirect = $blog->redirects()->create([
            'path' => $path,
            'to' => $to,
            'type' => $type,
        ]);

        RedirectChangedEvent::dispatch($redirect);

        return $redirect;
    }

    public static function updateRedirect(
        Redirect $redirect,
        string $path,
        string $to,
        RedirectTypeEnum $type
    ): Redirect {
        $redirect->path = $path;
        $redirect->to = $to;
        $redirect->type = $type;

        $redirect->save();

        RedirectChangedEvent::dispatch($redirect);

        return $redirect;
    }

    public static function deleteRedirect(Redirect $redirect): void
    {
        $redirect->delete();

        RedirectChangedEvent::dispatch($redirect);
    }

    /**
     * TODO: Update this to match wildcards
     */
    public static function findRedirectForPath(Blog $blog, string $path): Redirect|null
    {
        return $blog->redirects()
            ->where('path', $path)
            ->first();
    }
}
