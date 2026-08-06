<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Ai\TranslatePostInput;
use App\Service\Ai\Agent\AiAgentService;
use App\Service\Ai\Translate\AiPostTranslator;
use App\Service\Ai\Translate\TranslateException;
use App\Service\Post\PostService;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ThinkingDelta;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallComplete;
use Symfony\AI\Platform\Result\Stream\Delta\ToolCallStart;
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
        private AiAgentService $aiAgentService
    ) {}

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

    #[Route('/ai/agent', methods: ['POST'])]
    #[ScopeRequired(Scope::AI_USE)]
    public function agent(): StreamedResponse
    {

        $blog = $this->authListener->getBlog();

        $result = $this->aiAgentService->call($blog);

        $response = new StreamedResponse(function () use ($result) {
            foreach ($result->getContent() as $delta) {
                $event = match (true) {
                    $delta instanceof ThinkingDelta => ['type' => 'thinking', 'content' => $delta->getThinking()],
                    $delta instanceof ThinkingComplete => ['type' => 'thinking_done'],
                    $delta instanceof ToolCallStart => ['type' => 'tool_call', 'tool' => $delta->getName()],
                    $delta instanceof ToolCallComplete => ['type' => 'tool_result', 'status' => 'done'],
                    $delta instanceof TextDelta => ['type' => 'text', 'content' => (string) $delta],
                    default => null,
                };

                if ($event !== null) {
                    echo 'data: '.json_encode($event)."\n\n";
                    flush();
                }
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

}
