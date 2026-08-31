<?php

namespace App\Service\Ai\Agent;

use App\Entity\Blog;
use App\Entity\PostVariant;
use App\Service\Ai\Agent\Tool\AgentCallResult;
use App\Service\Ai\Agent\Tool\DocumentOps\DocumentOpsTool;
use App\Service\Ai\Agent\Tool\Query\QueryTool;
use App\Service\Ai\AiPlatformService;
use App\Service\Language\LanguageService;
use App\Service\Post\Content\Markdown\MarkdownSerializer;
use App\Service\Post\Content\PostContentService;
use App\Service\Post\PostService;
use App\Service\Tag\TagService;
use App\Service\User\UserService;
use Psr\Log\LoggerInterface;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

class AiAgentService
{

    private const string SYSTEM_PROMPT_FOR_POST = <<<PROMPT
    You are a helpful writing assistant for the blog '{blog_name}' running on Hyvor Blogs blogging platform.
    {current_post_prompt}

    You can:
    - edit the post content if user asks
    - answer questions about the post content
    - search other posts in the blog to find relevant information
    - use get_tags, get_authors and get_post_variants to look up tags, authors and other posts in the blog (e.g. to link to them or tag/attribute this post)

    Editing posts:
    - use document_get tool to get markdown content of a post variant
    - each markdown node is prefixed with an ID in the format #[id] (e.g. #[p-1] for paragraph 1)
    - call tools with the node ID: e.g., document_replace(postVariantId, 'p-1', 'new *content*')
    - when creating a lot of content, generate all and use document_insert(postVariantId, 'p-1' with all the content)
    - use document_delete(postVariantId, 'p-1') to remove a node
    - important rules when editing:
        - node IDs (e.g. #[p-1]) are an internal reference only. Use them solely as the `nodeId`/`afterNodeId` argument when calling document tools.
        - never include node ID prefixes (e.g. #[p-1]) inside the `contentMarkdown` argument itself - that argument must contain plain markdown content only.
        - only write actual post content into the document. Do not add your own thoughts, notes, plans, or other meta-commentary into the document content - put those in your reply to the user instead, never inside document_* tool calls.

    Markdown schema for post content:
    {markdown_schema}
    PROMPT;

    private const string CURRENT_POST_PROMPT = <<<PROMPT
    Currently editing post variant ID: {post_variant_id}
    Post is in {language} ({language_code}) language.
    Post title: {title_prompt}
    PROMPT;



    public function __construct(
        private AiPlatformService $aiPlatformService,
        private PostContentService $postContentService,
        private PostService $postService,
        private TagService $tagService,
        private UserService $userService,
        private LanguageService $languageService,
    ) {}

    private function getSystemPrompt(Blog $blog, ?PostVariant $postVariant): string
    {

        $currentPostPrompt = '';
        if ($postVariant) {
            $title = $postVariant->getTitle();
            $currentPostPrompt = str_replace(
                [
                    '{post_variant_id}',
                    '{language}',
                    '{language_code}',
                    '{title_prompt}'
                ],
                [
                    (string)$postVariant->getId(),
                    $postVariant->getLanguage()->getName(),
                    $postVariant->getLanguage()->getCode(),
                    $title ? "Post title is: '$title'." : "Post has no title."
                ],
                self::CURRENT_POST_PROMPT
            );
        }

        return str_replace(
            [
                '{blog_name}',
                '{current_post_prompt}',
                '{markdown_schema}'
            ],
            [
                $blog->getVariants()->toArray()[0]->getName() ?? '',
                $currentPostPrompt,
                MarkdownSerializer::SCHEMA_FOR_AI_AGENTS
            ],
            self::SYSTEM_PROMPT_FOR_POST
        );
    }

    public function callAgent(
        Blog $blog,
        string $prompt,
        ?PostVariant $postVariant,
        ?MessageBag $history = null,
    ): AgentCallResult
    {
        $provider = $blog->getMeta()->ai_provider;
        $platform = $this->aiPlatformService->getPlatformForProvider($provider);

        $documentOpsTool = new DocumentOpsTool(
            $blog,
            $this->postService,
            $this->postContentService
        );
        $queryTool = new QueryTool(
            $blog,
            $this->tagService,
            $this->userService,
            $this->postService,
            $this->languageService,
            // $this->logger,
        );
        $toolbox = new Toolbox([$documentOpsTool, $queryTool]);
        $toolProcessor = new AgentProcessor($toolbox);

        $agent = new Agent(
            $platform,
            $provider->model(),
            inputProcessors: [$toolProcessor],
            outputProcessors: [$toolProcessor],
            name: 'hyvor-blogs-agent'
        );

        $systemPrompt = $this->getSystemPrompt($blog, $postVariant);

        $messages = new MessageBag(Message::forSystem($systemPrompt));
        foreach ($history ?? [] as $historyMessage) {
            $messages->add($historyMessage);
        }
        $messages->add(Message::ofUser($prompt));

        $callResult = $agent->call($messages, [
            'stream' => true,
            'max_tokens' => 5000,
            // 'reasoning' => ['summary' => 'auto'],
        ]);

        return new AgentCallResult($callResult, $documentOpsTool);
    }

}
