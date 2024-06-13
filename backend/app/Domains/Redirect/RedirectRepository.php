<?php declare(strict_types=1);

namespace App\Domains\Redirect;

use App\Data\Enums\RedirectTypeEnum;
use App\Domains\Redirect\Events\RedirectChangedEvent;
use App\Models\Blog;
use App\Models\Redirect;
use Illuminate\Support\Collection;

class RedirectRepository
{
    /**
     * @return Collection<int, Redirect>
     */
    public static function getRedirects(Blog $blog, int $limit, int $offset = 0): Collection
    {
        return $blog->redirects()
            ->limit($limit)
            ->offset($offset)
            ->orderBy('dynamic', 'desc')
            ->latest()
            ->get();
    }

    public static function createRedirect(
        Blog $blog,
        bool $dynamic,
        string $path,
        string $to,
        RedirectTypeEnum $type
    ): Redirect {
        $redirect = $blog->redirects()->create([
            'dynamic' => $dynamic,
            'path' => $path,
            'to' => $to,
            'type' => $type,
        ]);

        RedirectChangedEvent::dispatch($redirect);

        return $redirect;
    }

    /**
     * @param array{path?: string, to?: string, type?: RedirectTypeEnum} $updates
     */
    public static function updateRedirect(Redirect $redirect, array $updates) : Redirect
    {
        $redirect->update($updates);
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

    public static function hasRedirectForPath(Blog $blog, string $path): bool
    {
        return $blog->redirects()
            ->where('path', $path)
            ->exists();
    }
}
