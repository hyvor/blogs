<?php

declare(strict_types=1);

namespace App\Http\ConsoleApi\Controllers;

use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\JsonResponse;

class ConsoleController
{

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
