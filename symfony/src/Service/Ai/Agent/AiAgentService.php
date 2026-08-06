<?php

namespace App\Service\Ai\Agent;

use App\Entity\Blog;
use App\Service\Ai\AiPlatformService;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ResultInterface;

class AiAgentService
{

    public function __construct(
        private AiPlatformService $aiPlatformService
    ) {}

    public function call(Blog $blog): ResultInterface
    {

        $provider = $blog->getMeta()->ai_provider;
        $platform = $this->aiPlatformService->getPlatformForProvider($provider);

        $agent = new Agent($platform, $provider->model(), name: 'hyvor-blogs-agent');

        $messages = new MessageBag(
            Message::forSystem('You are a helpful chatbot answering questions about LLM agent.'),
            Message::ofUser('Hello, how are you?'),
        );

        return $agent->call($messages, [
            'stream' => true,
            'reasoning' => ['summary' => 'auto'],
        ]);

    }

}
