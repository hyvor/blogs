<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Input\Ai\TranslatePostInput;
use App\Service\Ai\Translate\AiPostTranslator;
use App\Service\Post\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class AiController extends AbstractController
{

    public function __construct(
        private ConsoleApiAuthorizationListener $authListener,
        private PostService $postService,
        private AiPostTranslator $aiPostTranslator
    ) {}

    #[Route('/ai/translate/post', methods: ['POST'])]
    public function translate(
        #[MapRequestPayload] TranslatePostInput $input
    ): JsonResponse
    {
        $blog = $this->authListener->getBlog();

        $variant = $this->postService->getPostVariantByBlogAndId($blog, $input->post_variant_id);

        if (!$variant) {
            throw new BadRequestHttpException('Post variant not found');
        }

        $translatedData = $this->aiPostTranslator->translate($variant, $input->target_language_code);

        return new JsonResponse($translatedData);
    }

}
