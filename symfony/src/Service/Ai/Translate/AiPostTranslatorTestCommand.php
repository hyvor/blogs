<?php

namespace App\Service\Ai\Translate;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Meta\BlogMeta;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Ai\AiProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @codeCoverageIgnore
 */
#[AsCommand('app:ai:translate', description: 'Test command for AiPostTranslator')]
#[When(env: 'dev')]
class AiPostTranslatorTestCommand
{

    public function __construct(
        private AiPostTranslator $aiPostTranslator,
    ) {}

    /**
     * @throws TranslateException
     */
    public function __invoke(): void
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

        $meta = new BlogMeta();
        $meta->ai_provider = AiProvider::ANTHROPIC;
        $blog = new Blog();
        $blog->setMeta($meta);

        $post = new Post();
        $post->setBlog($blog);

        $language = new Language();
        $language->setCode('en');
        $language->setBlog($blog);

        $postVariant = new PostVariant();
        $postVariant->setPost($post);
        $postVariant->setLanguage($language);
        $postVariant->setContent(json_encode($content, JSON_THROW_ON_ERROR));
        $postVariant->setTitle('Hello, world!');
        $postVariant->setDescription('This is a test post for translation.');
        $postVariant->setSlug('hello-world');

        $translatedData = $this->aiPostTranslator->translatePostVariant($postVariant, 'fr');
        dd($translatedData);
    }

}
