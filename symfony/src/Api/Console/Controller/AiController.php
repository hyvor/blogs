<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Ai\AgentPromptInput;
use App\Api\Console\Input\Ai\TranslatePostInput;
use App\Service\Ai\Agent\AiAgentConversationService;
use App\Service\Ai\Translate\AiPostTranslator;
use App\Service\Ai\Translate\TranslateException;
use App\Service\Post\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class AiController extends AbstractController
{

    public function __construct(
        private ConsoleApiAuthorizationListener $authListener,
        private PostService $postService,
        private AiPostTranslator $aiPostTranslator,
        private AiAgentConversationService $aiAgentConversationService
    ) {}

    #[Route('/ai/agent', methods: ['POST'])]
    #[ScopeRequired(Scope::AI_USE)]
    public function agent(
        #[MapRequestPayload] AgentPromptInput $input
    ): StreamedResponse
    {
        $blog = $this->authListener->getBlog();
        $prompt = $input->prompt;
        $postVariant = null;
        if ($input->post_variant_id) {
            $postVariant = $this->postService->getPostVariantByBlogAndId($blog, $input->post_variant_id);
            if ($postVariant === null) {
                throw new BadRequestHttpException('Post variant not found');
            }
        }

        $response = new StreamedResponse(function () use ($blog, $prompt, $postVariant) {
            foreach ($this->aiAgentConversationService->streamPrompt($blog, $prompt, $postVariant) as $event) {
                echo 'data: '.json_encode($event)."\n\n";
                flush();
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    #[Route('/ai/translate/post', methods: ['POST'])]
    #[ScopeRequired(Scope::AI_USE)]
    public function translate(
        #[MapRequestPayload] TranslatePostInput $input
    ): JsonResponse
    {
        $blog = $this->authListener->getBlog();

        $variant = $this->postService->getPostVariantByBlogAndId($blog, $input->post_variant_id);

        if (!$variant) {
            throw new BadRequestHttpException('Post variant not found');
        }

        try {
            $translatedData = $this->aiPostTranslator->translatePostVariant($variant, $input->target_language_code);
        } catch (TranslateException $e) {
            throw new BadRequestHttpException('Translation failed: ' . $e->getMessage());
        }

        return new JsonResponse($translatedData);
    }

}
