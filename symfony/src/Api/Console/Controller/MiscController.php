<?php declare(strict_types=1);

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Service\Post\Content\PostContentService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class MiscController
{
    public function __construct(
        private ConsoleApiAuthorizationListener $blogAuthListener,
        private PostContentService $postContentService,
    ) {}

    #[Route('/misc/prosemirror/json', methods: ['GET'])]
    public function getProsemirrorJson(
        #[MapQueryParameter] string $html,
    ): JsonResponse {
        $blog = $this->blogAuthListener->getBlog();
        $json = $this->postContentService->getJsonFromHtml($html, $blog);

        return new JsonResponse(['json' => $json]);
    }
}
