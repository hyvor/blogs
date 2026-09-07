<?php

namespace App\Api\Console\ControllerOrg;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\OrganizationOptional;
use App\Api\Console\ConsoleSubrequest;
use App\Api\Console\Input\Blog\SortBlogsInput;
use App\Api\Console\Object\AuthUserObject;
use App\Api\Console\Object\BlogListObjectFactory;
use App\Service\AppConfig;
use App\Service\Billing\UsageService;
use App\Service\CodeHighlight\Highlighter;
use App\Service\Ai\AiModel;
use App\Service\Limit;
use App\Service\User\UserService;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Mercure\HubInterface;
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
        private ConsoleSubrequest $consoleSubrequest,
        private InstanceUrlResolver $instanceUrlResolver,
        private HubInterface $mercureHub,
    ) {}

    #[Route('/init', methods: ['GET'])]
    #[OrganizationOptional]
    public function init(Request $request): JsonResponse
    {
        $user = $this->authListener->getUser();
        $org = $this->authListener->hasOrganization()
            ? $this->authListener->getOrganization() : null;

        $blogHint = $request->query->get('blog_hint');
        // TODO: handle post_hint

        $userBlogs = [];
        $blogListObjects = [];
        if ($org !== null) {
            $userBlogs = $this->userService->getBlogsForUser($user->id, $org->id);
            foreach ($userBlogs as $entry) {
                $blogListObjects[] = $this->blogListObjectFactory->create($entry);
            }
        }

        $preloadedBlog = null;
        if (count($userBlogs) > 0) {
            $userBlogToPreload = $userBlogs[0];
            if ($blogHint !== null) {
                foreach ($userBlogs as $entry) {
                    if ($entry->getBlog()->getSubdomain() === $blogHint) {
                        $userBlogToPreload = $entry;
                        break;
                    }
                }
            }

            $preloadedBlog = $this->consoleSubrequest->callBlogEndpoint(
                $userBlogToPreload->getBlog(),
                'GET',
                '/blog',
                $userBlogToPreload
            );
        }

        $resolvedLicense = null;
        if ($this->internalConfig->getDeployment()->isCloud() && $org) {
            try {
                $resolvedLicense = $this->billing->license($org->id);
            } catch (CommsApiFailedException $e) {
                throw new UnprocessableEntityHttpException('unable to fetch the license. please try again later');
            }
        }

        return new JsonResponse([
            'user' => new AuthUserObject($user),
            'organization' => $org,
            'blogs' => $blogListObjects,
            'config' => [
                'deployment' => $this->internalConfig->getDeployment()->value,
                'hyvor' => [
                    'instance' => $this->internalConfig->getInstance(),
                    'hyvor_post_url' => $this->instanceUrlResolver->publicUrlOf(Component::POST),
                    'hyvor_talk_url' => $this->instanceUrlResolver->publicUrlOf(Component::TALK),
                ],
                'domains' => [
                    'app' => $this->appConfig->getDomainApp(),
                    'delivery' => $this->appConfig->getDeliveryDomain(),
                ],
                'mercure' => [
                    'public_url' => $this->mercureHub->getPublicUrl(),
                ],
                'limits' => [
                    'max_upload_size' => Limit::MAX_MEDIA_UPLOAD_SIZE,
                    'max_theme_zip_size' => Limit::MAX_THEME_ZIP_SIZE,
                    'max_asset_file_size' => Limit::MAX_ASSET_FILE_SIZE,
                ],
                'highlight_themes' => $this->highlighter->getAllThemes(),
                'ai_models' => array_map(fn(AiModel $model) => [
                    'value' => $model->value,
                    'provider' => $model->getProvider()->label(),
                    'usage_percent' => $model->getRelativeCostPercent(),
                ], AiModel::cases()),
            ],
            'preloaded' => [
                'blog' => $preloadedBlog ? json_decode((string) $preloadedBlog->getContent(), true) : null,
                'post' => null,
            ],
            'resolved_license' => $resolvedLicense,
        ]);
    }

    #[Route('/usage', methods: ['GET'])]
    public function usage(): JsonResponse
    {
        $org = $this->authListener->getOrganization();

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
            'ai' => [
                'used' => ($bl->aiCost ?? 0) > 0 ? $this->usageService->getAiTokensUsage($org->id, $bl) : 0,
                'limit' => ($bl->aiCost ?? 0) > 0 ? 100 : 0,
            ],
            'blogs' => [
                'used' => $this->usageService->getBlogsUsage($org->id),
                'limit' => $bl->blogs ?? 0,
            ],
        ]);
    }

    #[Route('/ping', methods: ['GET'])]
    public function ping(): JsonResponse
    {
        return new JsonResponse();
    }

    #[Route('/blogs/sort', methods: ['PATCH'])]
    public function sortBlogs(
        #[MapRequestPayload] SortBlogsInput $input,
    ): JsonResponse {
        $user = $this->authListener->getUser();
        $this->userService->changeBlogSorts($user->id, $input->blog_ids);

        return new JsonResponse();
    }
}
