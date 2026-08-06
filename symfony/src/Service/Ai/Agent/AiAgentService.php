<?php

namespace App\Service\Ai\Agent;

use App\Entity\Blog;
use App\Entity\PostVariant;
use App\Service\Ai\Agent\Tool\DocumentOpsTool;
use App\Service\Ai\AiPlatformService;
use Hyvor\Phrosemirror\Document\Node;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ResultInterface;

class AiAgentService
{

    private const SYSTEM_PROMPT_FOR_POST = <<<PROMPT
    You are a helpful writing assistant for the blog '{blog_name}' running on Hyvor Blogs blogging platform.

    {title_prompt}
    Post is in {language} ({language_code}) language.

    You can:
    - edit the post content if user asks
    - answer questions about the post content
    - search other posts in the blog to find relevant information

    Editing posts:
    - you are given markdown content of the post, each node prefixed with an ID in the format #[id] (e.g. #[p-1] for paragraph 1)
    - return a JSON object with the following structure:
    {
        "edits": [
            {
                "node_id": "p-1",
                "action": "replace",
                "content": "new content for paragraph 1"
            },
            {
                "node_id": "p-2",
                "action": "delete"
            },
            {
                "node_id": "p-3",
                "action": "insert_after",
                "content": "new paragraph after paragraph 3"
            }
        ]
    }
    PROMPT;


    public function __construct(
        private AiPlatformService $aiPlatformService
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
                '{language_code}'
            ],
            [
                $blog->getVariants()->first()->getName(),
                $titlePrompt,
                $language->getName(),
                $language->getCode()
            ],
            self::SYSTEM_PROMPT_FOR_POST
        );
    }

    public function callForPost(
        PostVariant $postVariant,
        string $prompt
    ): ResultInterface
    {
        $blog = $postVariant->getPost()->getBlog();

        $provider = $blog->getMeta()->ai_provider;
        $platform = $this->aiPlatformService->getPlatformForProvider($provider);

       // $documentOpsTool = new DocumentOpsTool($node);
        $toolbox = new Toolbox([]);
        $toolProcessor = new AgentProcessor($toolbox);

        $agent = new Agent(
            $platform,
            $provider->model(),
            inputProcessors: [$toolProcessor],
            outputProcessors: [$toolProcessor],
            name: 'hyvor-blogs-agent'
        );

        $messages = new MessageBag(
            Message::forSystem($this->getSystemPromptForPost($postVariant)),
            Message::ofUser($prompt),
        );

        return $agent->call($messages, [
            'stream' => true,
            'reasoning' => ['summary' => 'auto'],
        ]);

    }

}
