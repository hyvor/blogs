<?php

namespace App\Service\Ai;

use App\Entity\Blog;
use App\Service\AppConfig;
use Symfony\AI\Platform\Bridge\OpenAi\Factory as OpenAiFactory;
use Symfony\AI\Platform\Bridge\Anthropic\Factory as AnthropicFactory;
use Symfony\AI\Platform\Bridge\Mistral\Factory as MistralFactory;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiPlatformService
{

    public function __construct(
        private AppConfig $appConfig,
        private HttpClientInterface $httpClient
    ) {}

    public function getPlatformForBlog(Blog $blog): PlatformInterface
    {
        return $this->getPlatformForProvider($blog->getMeta()->ai_model->getProvider());
    }

    public function getPlatformForProvider(AiProvider $provider): PlatformInterface
    {
        return match ($provider) {
            AiProvider::OPENAI => OpenAiFactory::createPlatform(
                apiKey: $this->appConfig->getOpenAiApiKey(),
                httpClient: $this->httpClient
            ),
            AiProvider::ANTHROPIC => AnthropicFactory::createPlatform(
                apiKey: $this->appConfig->getAnthropicApiKey(),
                httpClient: $this->httpClient
            ),
            AiProvider::MISTRAL => MistralFactory::createPlatform(
                apiKey: $this->appConfig->getMistralApiKey(),
                httpClient: $this->httpClient
            ),
        };
    }

}
