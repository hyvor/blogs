<?php declare(strict_types=1);

namespace App\Http\ConsoleApi\Controllers;

use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\Blog\TempBlogService;
use App\Domains\User\UserBlogRepository;
use App\Domains\User\UserRepository;
use App\Http\ConsoleApi\Objects\Blog\BlogListObject;
use App\Http\ConsoleApi\Objects\User\AuthUserObject;
use Hyvor\Helper\Http\Middleware\AccessAuthUser;
use Hyvor\HyvorConnecter\HyvorUser;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConsoleController
{

    public function init(AccessAuthUser $user) : JsonResponse
    {

        $blogs = UserBlogRepository::getBlogsOfUser($user->id)
            ->mapInto(BlogListObject::class);

        return response()->json([
            'user' => new AuthUserObject($user),
            'blogs' => $blogs,
            'is_blocked' => UserRepository::isBlocked($user->id),
            'config' => $this->config()
        ]);

    }

    public function initTemp(Request $request) : JsonResponse
    {

        $tempUniqueId = $request->input('temp_unique_id');

        if (!$tempUniqueId) {
            $tempUniqueId = Str::random(32);
        }

        $blog = BlogListObject::fromTempBlog(TempBlogService::getTempBlog($tempUniqueId));

        return response()->json([
            'user' => [
                'id' => 0,
                'name' => 'Temp User',
                'picture_url' => null,
                'username' => null,
            ],
            'temp_unique_id' => $tempUniqueId,
            'blogs' => [$blog],
            'is_blocked' => false,
            'config' => $this->config()
        ]);

    }

    public function getConfig() : JsonResponse
    {
        return response()->json($this->config());
    }

    private function config() : array
    {
        return [
            'domains' => [
                'app' => config('blogs.domain_app'),
                'delivery' => config('blogs.domain_delivery'),
            ],
            'limits' => [
                'max_upload_size' => config('limits.max_media_upload_size_kb') * 1000,
                'max_theme_zip_size' => config('limits.max_theme_zip_size_kb') * 1000,
                'max_asset_file_size' => config('limits.max_asset_file_size'),
            ],
            'highlight_themes' => Highlighter::getAllThemes(),

            'services' => [
                'paddle' => [
                    'sandbox' => (bool) config('services.paddle.sandbox'),
                    'vendor_id' => (int) config('services.paddle.vendor_id'),
                ]
            ]
        ];
    }

    public function changeBlogSort(Request $request, AccessAuthUser $user) : JsonResponse
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