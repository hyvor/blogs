<?php

namespace App\Service\Ai\Agent;

use App\Entity\Enum\PostVariantStatus;
use App\Entity\Meta\BlogMeta;
use App\Service\Ai\AiModel;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\BlogVariantFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use Symfony\AI\Platform\Result\Stream\Delta;
use Symfony\AI\Platform\TokenUsage\TokenUsageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Attribute\When;

#[AsCommand('app:ai:agent', description: 'Test command for AiAgentService')]
#[When(env: 'dev')]
class AiAgentTestCommand
{

    public function __construct(
        private AiAgentService $aiAgentService,
    ) {}

    public function __invoke(): int
    {
        $meta = new BlogMeta();
        $meta->ai_model = AiModel::GPT_5_6_LUNA;
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
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello, world!'
                        ],
                    ]
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Paris is the capital city of Germany.'
                        ],
                    ]
                ],
                [
                    'type' => 'bullet_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => 'Eggs'
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => 'Milk'
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $postVariant = PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'content_unsaved' => json_encode($content),
            'status' => PostVariantStatus::DRAFT
        ]);

        $prompt = 'Add a couple of content to given post. Use paragraphs, blockquotes, callouts, buttons, embeds, TOC, bookmark, etc. Add images and links. Make it engaging and informative. Fix any factual errors.';
        // $prompt = 'fix factual errors in the content. Make sure the content is accurate and informative';
        $prompt = 'test';

        $result = $this->aiAgentService->callAgent($blog, $prompt, $postVariant);

        $output = '';
        $content = $result->getResult()->getContent();
        assert(is_iterable($content));

        foreach ($content as $delta) {
            if ($delta instanceof Delta\TextDelta) {
                $output .= $delta->getText();
                echo $delta->getText();
            } elseif ($delta instanceof Delta\ThinkingDelta) {
                $output .= '[Thinking...] ' . $delta->getThinking();
                echo '[Thinking...](' . $delta->getThinking() . ')';
            } elseif ($delta instanceof Delta\ThinkingComplete) {
                $output .= '[Thinking complete]' . $delta->getThinking();
                echo '[Thinking complete](' . $delta->getThinking() . ')';
            } else if ($delta instanceof Delta\ToolCallStart) {
                $output .= '[Tool call: ' . $delta->getName() . ']';
                echo '[Tool call: ' . $delta->getName() . ']';
            } else if ($delta instanceof Delta\ToolCallComplete) {
                $output .= '[Tool call complete: ' . $delta->getToolCalls()[0]->getName() . ']';
                echo '[Tool call complete: ' . $delta->getToolCalls()[0]->getName() . ']';
            } else {
                dump($delta);
            }
        }

        dd($result->getResult()->getMetadata()->get('token_usage') instanceof TokenUsageInterface);

        // $content = $result->getDocumentOpsTool()->getFinalDocument($postVariant->getId())->toArray();
    }

}
