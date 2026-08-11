<?php

namespace App\Service\Ai\Agent;

use App\Entity\PostVariant;
use App\Service\Ai\Agent\Tool\AgentCallResult;
use App\Service\Ai\Agent\Tool\DocumentOps\DocumentOpsTool;
use App\Service\Ai\AiPlatformService;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

class AiAgentService
{

    private const string SYSTEM_PROMPT_FOR_POST = <<<PROMPT
    You are a helpful writing assistant for the blog '{blog_name}' running on Hyvor Blogs blogging platform.

    {title_prompt}
    Post is in {language} ({language_code}) language.

    Current post variant ID: {post_variant_id}

    You can:
    - edit the post content if user asks
    - answer questions about the post content
    - search other posts in the blog to find relevant information

    Editing posts:
    - use document_get tool to get markdown content of a post variant
    - each markdown node is prefixed with an ID in the format #[id] (e.g. #[p-1] for paragraph 1)
    - call tools with the node ID: e.g., document_replace(postVariantId, 'p-1', 'new *content*')
    PROMPT;


    public function __construct(
        private AiPlatformService $aiPlatformService,
        private PostContentService $postContentService,
        private PostService $postService,
    ) {}

    private function getSystemPromptForPost(PostVariant $postVariant): string
    {
        $blog = $postVariant->getPost()->getBlog();
        $title = $postVariant->getTitle();
        $language = $postVariant->getLanguage();

        $titlePrompt = $title ? "Post title is: '$title'." : "Post has no title.";

        return str_replace(
            [
                '{blog_name}',
                '{title_prompt}',
                '{language}',
                '{language_code}',
                '{post_variant_id}'
            ],
            [
                $blog->getVariants()->first()->getName(),
                $titlePrompt,
                $language->getName(),
                $language->getCode(),
                (string)$postVariant->getId()
            ],
            self::SYSTEM_PROMPT_FOR_POST
        );
    }

    public function callForPost(
        PostVariant $postVariant,
        string $prompt
    ): AgentCallResult
    {
        $blog = $postVariant->getPost()->getBlog();

        $provider = $blog->getMeta()->ai_provider;
        $platform = $this->aiPlatformService->getPlatformForProvider($provider);

        $documentOpsTool = new DocumentOpsTool(
            $blog,

            $this->postService,
            $this->postContentService
        );
        $toolbox = new Toolbox([$documentOpsTool]);
        $toolProcessor = new AgentProcessor($toolbox);

        $agent = new Agent(
            $platform,
            $provider->model(),
            inputProcessors: [$toolProcessor],
            outputProcessors: [$toolProcessor],
            name: 'hyvor-blogs-agent'
        );

        $systemPrompt = $this->getSystemPromptForPost($postVariant);

        $messages = new MessageBag(
            Message::forSystem($systemPrompt),
            Message::ofUser($prompt),
        );

        $callResult = $agent->call($messages, [
            'stream' => true,
            // 'reasoning' => ['summary' => 'auto'],
        ]);

        return new AgentCallResult($callResult, $documentOpsTool);
    }

}
