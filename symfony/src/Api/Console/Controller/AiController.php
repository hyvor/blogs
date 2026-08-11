<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Ai\AgentPromptInput;
use App\Api\Console\Input\Ai\TranslatePostInput;
use App\Api\Console\Object\PostVariantObject;
use App\Service\Ai\Agent\AiAgentService;
use App\Service\Ai\Translate\AiPostTranslator;
use App\Service\Ai\Translate\TranslateException;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Route\PermalinkService;
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
        private PostContentService $postContentService,
        private PermalinkService $permalinkService,
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
    public function agent(
        #[MapRequestPayload] AgentPromptInput $input
    ): StreamedResponse
    {

        $blog = $this->authListener->getBlog();

        $postVariant = $this->postService->getPostVariantByBlogAndId($blog, 117);

        if (!$postVariant) {
            throw new BadRequestHttpException('No published post found to run the agent on.');
        }

        $postVariantObject = new PostVariantObject(
            $postVariant,
            $postVariant->getPost(),
            $blog,
            $this->permalinkService,
            $this->postContentService
        );

        $agentCallResult = $this->aiAgentService->callForPost($postVariant, $input->prompt);

        $response = new StreamedResponse(function () use ($agentCallResult, $postVariantObject, $postVariant) {
            $send = function (array $event) {
                echo 'data: '.json_encode($event)."\n\n";
                flush();
            };

            $send(['type' => 'post_variant', 'post_variant' => $postVariantObject]);

            foreach ($agentCallResult->getResult()->getContent() as $delta) {
                $event = match (true) {
                    $delta instanceof ThinkingDelta => ['type' => 'thinking', 'content' => $delta->getThinking()],
                    $delta instanceof ThinkingComplete => ['type' => 'thinking_done'],
                    $delta instanceof ToolCallStart => ['type' => 'tool_call', 'tool' => $delta->getName()],
                    $delta instanceof ToolCallComplete => ['type' => 'tool_result', 'status' => 'done'],
                    $delta instanceof TextDelta => ['type' => 'text', 'content' => (string) $delta],
                    default => null,
                };

                if ($event !== null) {
                    $send($event);
                }
            }

            $documentOpsTool = $agentCallResult->getDocumentOpsTool();
            $fetchedDocument = $documentOpsTool->getCachedDocuments()[$postVariant->getId()] ?? null;

            if ($fetchedDocument !== null && count($fetchedDocument->getOps()) > 0) {
                $finalDocument = $documentOpsTool->getFinalDocument($postVariant->getId());

                $send([
                    'type' => 'document_change',
                    'post_variant_id' => $postVariant->getId(),
                    'content' => json_encode($finalDocument->toArray()),
                ]);
            }

            $send(['type' => 'done']);
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

}
