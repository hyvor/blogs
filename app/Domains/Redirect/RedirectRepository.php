<?php

namespace App\Domains\Redirect;

use App\Data\Enums\RedirectTypeEnum;
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
    ) : Redirect
    {

        return $blog->redirects()->create([
            'path' => $path,
            'to' => $to,
            'type' => $type,
        ]);

    }

    public static function updateRedirect(
        Redirect $redirect,
        string $path,
        string $to,
        RedirectTypeEnum $type
    ): Redirect
    {

        $redirect->path = $path;
        $redirect->to = $to;
        $redirect->type = $type;

        $redirect->save();

        return $redirect;

    }

    public static function deleteRedirect(Redirect $redirect): void
    {
        $redirect->delete();
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
