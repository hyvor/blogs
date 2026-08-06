<?php

namespace App\Service\Ai\Agent;

use App\Entity\Blog;
use App\Entity\BlogVariant;
use App\Entity\Language;
use App\Entity\Meta\BlogMeta;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Ai\AiProvider;
use App\Service\Post\Content\PostContentService;
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
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
        $blog->getVariants()->add(new BlogVariant()->setName('Supun Blog'));

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

        $post = new Post();
        $post->setBlog($blog);

        $language = new Language();
        $language->setCode('en');
        $language->setName('English');

        $postVariant = new PostVariant();
        $postVariant->setPost($post);
        $postVariant->setLanguage($language);

        $postContentMarkdown = <<<MD
        #[p-1] Hello World
        #[p-2] This is a test post for the AI agent.
        MD;

        $prompt = <<<PROMPT
        Post content:
        $postContentMarkdown

        Remove the "This is a test..." paragraph and add two paragraphs on a random topic.
        PROMPT;

        $result = $this->aiAgentService->callForPost($postVariant, $prompt);

        $output = '';
        foreach ($result->getContent() as $delta) {
            if ($delta instanceof TextDelta) {
                $output .= $delta->getText();
            }
        }
        dd($output);

        dump($doc->toJson());

        return Command::SUCCESS;
    }

}
