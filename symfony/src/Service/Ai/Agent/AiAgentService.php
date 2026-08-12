<?php

namespace App\Service\Ai\Agent;

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
    - use get_tags, get_authors and get_post_variants to look up tags, authors and other posts in the blog (e.g. to link to them or tag/attribute this post)

    Editing posts:
    - use document_get tool to get markdown content of a post variant
    - each markdown node is prefixed with an ID in the format #[id] (e.g. #[p-1] for paragraph 1)
    - call tools with the node ID: e.g., document_replace(postVariantId, 'p-1', 'new *content*')
    - when creating a lot of content, generate all and use document_insert(postVariantId, 'p-1' with all the content)
    - use document_delete(postVariantId, 'p-1') to remove a node

    Markdown schema for the post content:
    {markdown_schema}
    PROMPT;


    public function __construct(
        private AiPlatformService $aiPlatformService,
        private PostContentService $postContentService,
        private PostService $postService,
        private TagService $tagService,
        private UserService $userService,
        private LanguageService $languageService,
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
                '{post_variant_id}',
                '{markdown_schema}'
            ],
            [
                $blog->getVariants()->first()->getName(),
                $titlePrompt,
                $language->getName(),
                $language->getCode(),
                (string)$postVariant->getId(),
                MarkdownSerializer::SCHEMA_FOR_AI_AGENTS
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
        $queryTool = new QueryTool(
            $blog,

            $this->tagService,
            $this->userService,
            $this->postService,
            $this->languageService,
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

        $systemPrompt = $this->getSystemPromptForPost($postVariant);

        $messages = new MessageBag(
            Message::forSystem($systemPrompt),
            Message::ofUser($prompt),
        );

        $callResult = $agent->call($messages, [
            'stream' => true,
            'max_tokens' => 5000,
            // 'reasoning' => ['summary' => 'auto'],
        ]);

        return new AgentCallResult($callResult, $documentOpsTool);
    }

}
