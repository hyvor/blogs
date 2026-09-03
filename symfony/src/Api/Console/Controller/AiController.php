<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\ConsoleApiAuthorizationListener;
use App\Api\Console\Authorization\MapBlogEntity;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\Ai\AgentPromptInput;
use App\Api\Console\Input\Ai\GetAiConversationsInput;
use App\Api\Console\Input\Ai\TranslatePostInput;
use App\Api\Console\Object\Ai\AiConversationObject;
use App\Api\Console\Object\Ai\AiConversationPostVariantObject;
use App\Api\Console\Object\Ai\AiMessageObject;
use App\Entity\AiConversation;
use App\Service\Ai\Agent\AiAgentConversationService;
use App\Service\Ai\Agent\AiConversationService;
use App\Service\Ai\Translate\AiPostTranslator;
use App\Service\Ai\Translate\TranslateException;
use App\Service\Post\PostService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class AiController extends AbstractController
{

    public function __construct(
        private ConsoleApiAuthorizationListener $authListener,
        private PostService $postService,
        private AiPostTranslator $aiPostTranslator,
        private AiAgentConversationService $aiAgentConversationService,
        private AiConversationService $aiConversationService,
    ) {}

    #[Route('/ai/agent', methods: ['POST'])]
    #[ScopeRequired(Scope::AI_USE)]
    public function callAgent(
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

        $conversation = null;
        if ($input->conversation_id) {
            $conversation = $this->aiAgentConversationService->getConversationForBlog($blog, $input->conversation_id);
            if ($conversation === null) {
                throw new BadRequestHttpException('Conversation not found');
            }
        }

        set_time_limit(0);

        $response = new StreamedResponse(function () use ($blog, $prompt, $postVariant, $conversation) {
            try {
                foreach (
                    $this->aiAgentConversationService->streamPrompt(
                        $blog,
                        $prompt,
                        $postVariant,
                        $conversation
                    ) as $event
                ) {
                    echo 'data: ' . json_encode($event) . "\n\n";
                    flush();
                }
            } catch (\Throwable) {
                // generate an error event manually
                // this can happen if the DB fails. so, we cannot actually save this event to the DB
                echo 'data: ' . json_encode(['type' => 'event', 'event' => ['type' => 'error']]) . "\n\n";
                flush();
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        // in case HB runs behind Nginx reverse proxy
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    #[Route('/ai/conversations', methods: ['GET'])]
    #[ScopeRequired(Scope::AI_USE)]
    public function getConversations(
        #[MapQueryString] GetAiConversationsInput $input,
    ): JsonResponse
    {
        $blog = $this->authListener->getBlog();
        $result = $this->aiConversationService->getConversationsForBlog($blog, $input->limit, $input->offset);

        return new JsonResponse(array_map(fn(AiConversation $c) => new AiConversationObject($c), $result['conversations']));
    }

    #[Route('/ai/conversation/{id}', methods: ['GET'])]
    #[ScopeRequired(Scope::AI_USE)]
    public function getConversation(#[MapBlogEntity] AiConversation $conversation): JsonResponse
    {
        $messages = $this->aiConversationService->getMessages($conversation);
        $postVariants = $this->aiConversationService->getInvolvedPostVariants($conversation);

        return new JsonResponse([
            'conversation' => new AiConversationObject($conversation),
            'messages' => array_map(fn($m) => new AiMessageObject($m), $messages),
            'post_variants' => array_map(fn($v) => new AiConversationPostVariantObject($v), $postVariants),
        ]);
    }

    #[Route('/ai/conversation/{id}', methods: ['DELETE'])]
    #[ScopeRequired(Scope::AI_USE)]
    public function deleteConversation(#[MapBlogEntity] AiConversation $conversation): JsonResponse
    {
        $this->aiConversationService->deleteConversation($conversation);

        return new JsonResponse();
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
