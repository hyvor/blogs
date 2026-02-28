<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\OrganizationLevelEndpoint;
use App\Api\Console\Authorization\OrganizationOptional;
use App\Api\Console\Object\AuthUserObject;
use App\Api\Console\Object\BlogListObjectFactory;
use App\Service\AppConfig;
use App\Service\CodeHighlight\Highlighter;
use App\Service\Limit;
use App\Service\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ConsoleController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $authListener,
        private UserService $userService,
        private BlogListObjectFactory $blogListObjectFactory,
        private AppConfig $appConfig,
        private Highlighter $highlighter,
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
            'user'         => new AuthUserObject($user),
            'organization' => $org,
            'blogs'        => $blogs,
            'config'       => [
                'hyvor'   => ['instance' => $this->appConfig->getHyvorInstance()],
                'domains' => [
                    'app'      => $this->appConfig->getDomainApp(),
                    'delivery' => $this->appConfig->getDeliveryDomain(),
                ],
                'limits'  => [
                    'max_upload_size'     => Limit::MAX_MEDIA_UPLOAD_SIZE,
                    'max_theme_zip_size'  => Limit::MAX_THEME_ZIP_SIZE,
                    'max_asset_file_size' => Limit::MAX_ASSET_FILE_SIZE,
                ],
                'highlight_themes' => $this->highlighter->getAllThemes(),
            ],
        ]);
    }
}
