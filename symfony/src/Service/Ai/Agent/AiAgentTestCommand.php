<?php

namespace App\Service\Ai\Agent;

use App\Entity\Enum\PostVariantStatus;
use App\Entity\Meta\BlogMeta;
use App\Service\Ai\AiProvider;
use App\Service\Post\Content\PostContentService;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use Symfony\AI\Platform\Result\Stream\Delta;
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
        $meta = new BlogMeta();
        $meta->ai_provider = AiProvider::ANTHROPIC;
        $blog = BlogFactory::createOne([
            'meta' => $meta
        ]);
        $post = PostFactory::createOneFor($blog);
        $language = LanguageFactory::createOnePrimaryFor($blog);
        BlogVariantFactory::createOneForBlog($blog);

        $content = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Paris, the world\'s most romantic city'
                        ],
                    ]
                ],
//                [
//                    'type' => 'paragraph',
//                    'content' => [
//                        [
//                            'type' => 'text',
//                            'text' => 'Hello, world!'
//                        ],
//                    ]
//                ],
//                [
//                    'type' => 'paragraph',
//                    'content' => [
//                        [
//                            'type' => 'text',
//                            'text' => 'Paris is the capital of Germany'
//                        ],
//                    ]
//                ],
//                [
//                    'type' => 'bullet_list',
//                    'content' => [
//                        [
//                            'type' => 'list_item',
//                            'content' => [
//                                [
//                                    'type' => 'paragraph',
//                                    'content' => [
//                                        [
//                                            'type' => 'text',
//                                            'text' => 'Eggs'
//                                        ],
//                                    ]
//                                ]
//                            ]
//                        ],
//                        [
//                            'type' => 'list_item',
//                            'content' => [
//                                [
//                                    'type' => 'paragraph',
//                                    'content' => [
//                                        [
//                                            'type' => 'text',
//                                            'text' => 'Milk'
//                                        ],
//                                    ]
//                                ]
//                            ]
//                        ]
//                    ]
//                ]
            ]
        ];


        $postVariant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'content' => json_encode($content),
            'status' => PostVariantStatus::DRAFT
        ]);

        $prompt = 'Write three paragraphs on the given topic';

        $result = $this->aiAgentService->callForPost($postVariant, $prompt);

        $output = '';
        foreach ($result->getResult()->getContent() as $delta) {
            if ($delta instanceof Delta\TextDelta) {
                $output .= $delta->getText();
            } elseif ($delta instanceof Delta\ThinkingDelta) {
                $output .= '[Thinking...] ' . $delta->getThinking();
            } else if ($delta instanceof Delta\ToolCallStart) {
                $output .= '[Tool call: ' . $delta->getName() . ']';
            } else if ($delta instanceof Delta\ToolCallComplete) {
                $output .= '[Tool call complete: ' . $delta->getToolCalls()[0]->getName() . ']';
            }
        }

        dd($output, $result->getDocumentOpsTool()->getCachedDocuments()[$postVariant->getId()]->getOps());

        return Command::SUCCESS;
    }

}
