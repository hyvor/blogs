<?php

declare(strict_types=1);

namespace App\Http\ConsoleApi\Controllers;

use App\Domains\Blog\TempBlogService;
use App\Http\ConsoleApi\Objects\Blog\BlogListObject;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsoleController
{

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
     * @return array<string, mixed>
     */
    private function config(): array
    {
        return [
            'hyvor' => [
                'instance' => config('internal.instance'),
            ],
            'domains' => [
                'app' => config('blogs.domain_app'),
                'delivery' => preg_replace('/https?:\/\//', '', config('blogs.delivery_url')),
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
}
