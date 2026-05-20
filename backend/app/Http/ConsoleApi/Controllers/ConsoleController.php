<?php

declare(strict_types=1);

namespace App\Http\ConsoleApi\Controllers;

use App\Domains\Billing\UsageService;
use App\Domains\Blog\TempBlogService;
use App\Domains\User\UserBlogRepository;
use App\Http\ConsoleApi\Middleware\ConsoleApiAuthMiddleware;
use App\Http\ConsoleApi\Objects\Blog\BlogListObject;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
        ConsoleApiAuthMiddleware $consoleApiAuthMiddleware,
        Request $request,
        BillingInterface $billing,
        UsageService $usageService,
    ): JsonResponse {

        $organization = $consoleApiAuthMiddleware->getOrganization($request);

        if (!$organization) {
            throw new BadRequestHttpException('no organization found');
        }

        try {
            $license = $billing->license($organization->id);
        } catch (CommsApiFailedException) {
            throw new UnprocessableEntityHttpException('unable to fetch the license. please try again later');
        }

        return response()->json([
            'users' => $this->usageOf($license, 'users', $usageService->getUsersUsage($organization->id)),
            'storage' => $this->usageOf($license, 'storage', $usageService->getStorageUsageBytes($organization->id)),
            'auto_translate_chars' => $this->usageOf($license, 'autoTranslationsChars', $usageService->getAutoTranslateCharsUsageThisMonth($organization->id)),
            'ai_tokens' => $this->usageOf($license, 'aiTokens', $usageService->getAiTokensUsage($organization->id)),
        ]);
    }

    /**
     * @return array{used: int, limit: int}
     */
    private function usageOf(ResolvedLicense $license, string $licenseKey, int $currentUsage): array
    {
        $limit = $license->license->{$licenseKey} ?? 0;

        return [
            'used' => $currentUsage,
            'limit' => $limit,
        ];
    }
}
