<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\OrganizationLevelEndpoint;
use App\Api\Console\Authorization\OrganizationOptional;
use App\Api\Console\Input\Blog\SortBlogsInput;
use App\Api\Console\Object\AuthUserObject;
use App\Api\Console\Object\BlogListObjectFactory;
use App\Service\AppConfig;
use App\Service\Billing\UsageService;
use App\Service\CodeHighlight\Highlighter;
use App\Service\Limit;
use App\Service\User\UserService;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ConsoleController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $authListener,
        private UserService $userService,
        private BlogListObjectFactory $blogListObjectFactory,
        private AppConfig $appConfig,
        private Highlighter $highlighter,
        private InternalConfig $internalConfig,
        private BillingInterface $billing,
        private UsageService $usageService,
    ) {}

    #[Route('/init', methods: ['GET'])]
    #[OrganizationLevelEndpoint]
    #[OrganizationOptional]
    public function init(Request $request): JsonResponse
    {
        $user = $this->authListener->getUser($request);
        $org = $this->authListener->hasOrganization($request)
            ? $this->authListener->getOrganization($request) : null;

        $blogs = [];
        if ($org !== null) {
            foreach ($this->userService->getBlogsForUser($user->id, $org->id) as $entry) {
                $blogs[] = $this->blogListObjectFactory->create($entry);
            }
        }

        return new JsonResponse([
            'user' => new AuthUserObject($user),
            'organization' => $org,
            'blogs' => $blogs,
            'config' => [
                'hyvor' => ['instance' => $this->internalConfig->getInstance()],
                'domains' => [
                    'app' => $this->appConfig->getDomainApp(),
                    'delivery' => $this->appConfig->getDeliveryDomain(),
                ],
                'limits' => [
                    'max_upload_size' => Limit::MAX_MEDIA_UPLOAD_SIZE,
                    'max_theme_zip_size' => Limit::MAX_THEME_ZIP_SIZE,
                    'max_asset_file_size' => Limit::MAX_ASSET_FILE_SIZE,
                ],
                'highlight_themes' => $this->highlighter->getAllThemes(),
            ],
        ]);
    }

    #[Route('/usage', methods: ['GET'])]
    #[OrganizationLevelEndpoint]
    public function usage(Request $request): JsonResponse
    {
        $org = $this->authListener->getOrganization($request);

        try {
            $license = $this->billing->license($org->id);
        } catch (CommsApiFailedException) {
            throw new UnprocessableEntityHttpException('unable to fetch the license. please try again later');
        }

        $bl = $license->license instanceof BlogsLicense ? $license->license : null;

        return new JsonResponse([
            'users' => [
                'used' => $this->usageService->getUsersUsage($org->id),
                'limit' => $bl->users ?? 0,
            ],
            'storage' => [
                'used' => $this->usageService->getStorageUsageBytes($org->id),
                'limit' => $bl->storage ?? 0,
            ],
            'auto_translate_chars' => [
                'used' => $this->usageService->getAutoTranslateCharsUsageThisMonth($org->id),
                'limit' => $bl->autoTranslationsChars ?? 0,
            ],
            'ai_tokens' => [
                'used' => $this->usageService->getAiTokensUsage($org->id),
                'limit' => $bl->aiTokens ?? 0,
            ],
        ]);
    }

    #[Route('/ping', methods: ['GET'])]
    #[OrganizationLevelEndpoint]
    public function ping(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('/blogs/sort', methods: ['PATCH'])]
    #[OrganizationLevelEndpoint]
    public function sortBlogs(
        #[MapRequestPayload] SortBlogsInput $input,
        Request $request,
    ): JsonResponse {
        $user = $this->authListener->getUser($request);
        $this->userService->changeBlogSorts($user->id, $input->blog_ids);

        return new JsonResponse();
    }
}
