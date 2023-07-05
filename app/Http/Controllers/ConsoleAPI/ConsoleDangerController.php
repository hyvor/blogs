<?php declare(strict_types=1);

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\CacheClearTypeEnum;
use App\Domains\Blog\Jobs\DeleteBlogJob;
use App\Domains\Cache\CacheService;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleDangerController
{
    public function delete(Blog $blog) : JsonResponse
    {
        DeleteBlogJob::dispatch($blog);
        return response()->json();
    }

    public function deleteCache(Blog $blog, Request $request) : JsonResponse
    {

        $request->validate([
            'type' => ['required', new Enum(CacheClearTypeEnum::class)],
            'paths' => 'array',
            'paths.*' => 'string'
        ]);

        $type = CacheClearTypeEnum::from((string) $request->string('type'));
        /** @var string[] $paths */
        $paths = $request->has('paths') ? (array) $request->input('paths') : [];

        $cacheService = app(CacheService::class);
        $cacheService->blog($blog);

        if ($type === CacheClearTypeEnum::ALL) {
            $cacheService->clearAllCache();
        } elseif ($type === CacheClearTypeEnum::TEMPLATE) {
            $cacheService->clearTemplateCache();
        } elseif ($type === CacheClearTypeEnum::PATHS) {
            $cacheService->clearPathsCache($paths);
        }

        return response()->json();
    }

}
