<?php declare(strict_types=1);

namespace App\Domains\Redirect;

use App\Data\Enums\RedirectTypeEnum;
use App\Domains\Redirect\Events\RedirectChangedEvent;
use App\Models\Blog;
use App\Models\Redirect;
use Illuminate\Support\Collection;
use App\Exceptions\SafetyException;
use App\Exceptions\TrustedException;

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
    * @return
    * array{
    *   to: string,
    *   type: RedirectTypeEnum
    * } | null
    */
    public static function findRedirectForPath(Blog $blog, string $path): array|null
    {
        // first check for dynamic redirects
        $dynamicRedirects = $blog->redirects()
                                ->where('dynamic', true)
                                ->get();

        // iterates through all dynamic redirects for the blog
        foreach ($dynamicRedirects as $dynamicRedirect) {

            $dynamicPath = $dynamicRedirect->path;
            $to = $dynamicRedirect->to;

            try {

                // checks if path matches the regex pattern of the dynamic path
                if (preg_match(self::getRegex($dynamicPath), $path)) {

                    // generates dynamic to path using the regex pattern
                    $dynamicTo = preg_replace(self::getRegex($dynamicPath), $to, $path);
                        
                    return [
                        'to' => $dynamicTo,
                        'type' => $dynamicRedirect->type,
                    ];
                }
            }
            catch (\Exception $e) {
                throw new SafetyException('Dynamic link parsing failed');
            }
        }
        
        // if no dynamic redirect is found, check for static redirects
        $staticRedirect = $blog->redirects()
                                ->where('path', $path)
                                ->first();

        return $staticRedirect ? [
            'to' => $staticRedirect->to,
            'type' => $staticRedirect->type,
        ] : null;
    }

    public static function hasRedirectForPath(Blog $blog, string $path): bool
    {
        return $blog->redirects()
            ->where('path', $path)
            ->exists();
    }

    private static function getRegex(string $path): string
    {
        return ('~' . $path . '~');
    }

    public static function validateRegex(string $regex): bool
    {
        if (@preg_match(self::getRegex($regex), '') === false)
            return false;
        return true;
    }

    public static function getDynamicRedirectCount(Blog $blog): int
    {
        return $blog->redirects()
            ->where('dynamic', true)
            ->count();
    }
}