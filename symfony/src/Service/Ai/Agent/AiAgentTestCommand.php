<?php

namespace App\Service\Ai\Agent;

use App\Entity\Blog;
use App\Entity\Meta\BlogMeta;
use App\Service\Ai\AiProvider;
use App\Service\Post\Content\PostContentService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Attribute\When;

#[AsCommand('app:ai:agent', description: 'Test command for AiAgentService')]
#[When(env: 'dev')]
class AiAgentTestCommand
{

    public function __construct(
        private AiAgentService $aiAgentService,
        private PostContentService $postContentService
    ) {}

    public function __invoke(): int
    {
        $blog = new Blog();
        $meta = new BlogMeta();
        $meta->ai_provider = AiProvider::OPENAI;
        $blog->setMeta($meta);

        $doc = $this->postContentService->getDocumentFromJson([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello, world!'
                        ]
                    ]
                ]
            ]
        ]);

        $result = $this->aiAgentService->call($blog, $doc, 'Please insert a new paragraph with some random');

        $output = '';
        foreach ($result->getContent() as $delta) {
            dump($delta);
        }
        // dd($output);

        dump($doc->toJson());

        return Command::SUCCESS;
    }

}
