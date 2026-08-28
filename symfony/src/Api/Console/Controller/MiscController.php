<?php declare(strict_types=1);

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Service\Post\Content\PostSchema;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class MiscController
{
    public function __construct(
        private PostSchema $postSchema,
    ) {}

    #[Route('/misc/prosemirror/json', methods: ['GET'])]
    #[ScopeRequired(Scope::POSTS_WRITE)]
    public function getProsemirrorJson(
        #[MapQueryParameter] string $html,
    ): JsonResponse {
        $json = $this->postSchema->documentFromHtml($html)->toJson();

        return new JsonResponse(['json' => $json]);
    }
}
