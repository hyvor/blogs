<?php

declare(strict_types=1);

namespace App\Http\ConsoleApi\Controllers;

use App\Domains\Billing\LicenseService;
use App\Domains\Billing\Usage\AiTokensUsage;
use App\Domains\Billing\Usage\AutoTranslateCharsUsage;
use App\Domains\Billing\Usage\StorageUsage;
use App\Domains\Billing\Usage\UsersUsage;
use App\Domains\Blog\TempBlogService;
use App\Domains\User\UserBlogRepository;
use App\Domains\User\UserRepository;
use App\Http\ConsoleApi\Objects\Blog\BlogListObject;
use App\Http\ConsoleApi\Objects\User\AuthUserObject;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\License\License;
use Hyvor\Internal\Billing\Usage\UsageAbstract;
use Hyvor\Internal\Http\Middleware\AccessAuthUser;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsoleController
{

    public function init(AccessAuthUser $user): JsonResponse
    {
        $blogs = UserBlogRepository::getBlogsOfUser($user)
            ->mapInto(BlogListObject::class);

        return response()->json([
            'user' => new AuthUserObject($user),
            'blogs' => $blogs,
            'is_blocked' => UserRepository::isBlocked($user->id),
            'config' => $this->config()
        ]);
    }

    public function initTemp(Request $request): JsonResponse
    {
        $tempSubdomain = $request->input('temp_subdomain');

        $blog = BlogListObject::fromTempBlog(
            TempBlogService::getTempBlog(
                $tempSubdomain,
                $request->ip(),
            )
        );

        return response()->json([
            'user' => [
                'id' => 0,
                'name' => 'Temp User',
                'picture_url' => null,
                'username' => null,
            ],
            'blogs' => [$blog],
            'is_blocked' => false,
            'config' => $this->config()
        ]);
    }

    public function getConfig(): JsonResponse
    {
        return response()->json($this->config());
    }

    /**
     * @return string[]
     */
    private function config(): array
    {
        return [
            'hyvor' => [
                'instance' => config('internal.instance'),
            ],
            'domains' => [
                'app' => config('blogs.domain_app'),
                'delivery' => config('blogs.delivery_domain'),
            ],
            'limits' => [
                'max_upload_size' => config('limits.max_media_upload_size_kb') * 1000,
                'max_theme_zip_size' => config('limits.max_theme_zip_size_kb') * 1000,
                'max_asset_file_size' => config('limits.max_asset_file_size'),
            ],
            'highlight_themes' => Highlighter::getAllThemes(),

            'services' => [
                'paddle' => [
                    'sandbox' => (bool)config('services.paddle.sandbox'),
                    'vendor_id' => (int)config('services.paddle.vendor_id'),
                ]
            ]
        ];
    }

    public function getUsage(
        AccessAuthUser $user,
        Billing $billing,
        UsersUsage $usersUsage,
        StorageUsage $storageUsage,
        AutoTranslateCharsUsage $autoTranslateCharsUsage,
        AiTokensUsage $aiTokensUsage
    ): JsonResponse {
        $license = $billing->license($user->id, null);

        return response()->json([
            'users' => $this->usageOf($usersUsage, $license, $user->id),
            'storage' => $this->usageOf($storageUsage, $license, $user->id),
            'auto_translate_chars' => $this->usageOf($autoTranslateCharsUsage, $license, $user->id),
            'ai_tokens' => $this->usageOf($aiTokensUsage, $license, $user->id),
        ]);
    }

    /**
     * @return array{used: int, limit: int}
     */
    private function usageOf(UsageAbstract $usage, ?License $license, int $userId): array
    {
        $used = $usage->usageOfUser($userId);
        $limit = $license ? $license->{$usage->getKey()} : 0;

        return [
            'used' => $used,
            'limit' => $limit,
        ];
    }

    public function changeBlogSort(Request $request, AccessAuthUser $user): JsonResponse
    {
        $request->validate([
            'blog_ids' => 'required|array',
            'blog_ids.*' => 'integer',
        ]);

        $blogIds = $request->input('blog_ids');

        UserBlogRepository::changeBlogSorts($user, $blogIds);

        return response()->json();
    }

}
