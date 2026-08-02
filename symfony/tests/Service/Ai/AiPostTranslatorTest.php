<?php

namespace App\Tests\Service\Ai;

use App\Entity\Meta\BlogMeta;
use App\Entity\PostVariant;
use App\Service\Ai\AiProvider;
use App\Service\Ai\Translate\AiPostTranslator;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;

class AiPostTranslatorTest extends KernelTestCase
{

    private function getTranslator(): AiPostTranslator
    {
        return $this->getService(AiPostTranslator::class);
    }

    private function getPostVariant(array $content): PostVariant
    {
        $blogMeta = new BlogMeta();
        $blogMeta->ai_provider = AiProvider::OPENAI;
        $blog = BlogFactory::createOne([
            'meta' => $blogMeta
        ]);

        $post = PostFactory::createOneFor($blog);
        $language = LanguageFactory::createOneFor($blog, ['code' => 'en']);

        return PostVariantFactory::createOne([
            'post' => $post,
            'language' => $language,
            'content' => json_encode($content, JSON_THROW_ON_ERROR)
        ]);
    }

    public function test_translates_post_variant_simple_paragraph(): void
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
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Today, we wil discusss about'
                        ],
                        [
                            'type' => 'text',
                            'text' => ' the importance of AI in modern technology.',
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

        $translator = $this->getTranslator();
        $translator->translate($this->getPostVariant($content), 'fr');

    }

}
