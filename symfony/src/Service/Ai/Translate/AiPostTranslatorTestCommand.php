<?php

namespace App\Service\Ai\Translate;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Ai\AiProvider;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('app:ai-post-translator-test', description: 'Test command for AiPostTranslator')]
class AiPostTranslatorTestCommand
{

    public function __construct(
        private AiPostTranslator $aiPostTranslator
    ) {}

    public function __invoke()
    {

        $content = [
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
        ];

        $content = [
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
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Today, we wil discusss about '
                        ],
                        [
                            'type' => 'text',
                            'text' => 'the importance of AI in modern technology.',
                            'marks' => [
                                [
                                    'type' => 'strong'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'History of AI'
                        ]
                    ]
                ],
                [
                    'type' => 'button',
                    'attrs' => [
                        'url' => 'https://example.com',
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Click here'
                        ]
                    ]
                ]
            ]
        ];

        $blog = new Blog();
        $blog->getMeta()->ai_provider = AiProvider::OPENAI;

        $post = new Post();
        $post->setBlog($blog);

        $language = new Language();
        $language->setCode('en');

        $postVariant = new PostVariant();
        $postVariant->setPost($post);
        $postVariant->setLanguage($language);
        $postVariant->setContent(json_encode($content, JSON_THROW_ON_ERROR));

        $translatedData = $this->aiPostTranslator->translate($postVariant, 'fr');
    }

}
