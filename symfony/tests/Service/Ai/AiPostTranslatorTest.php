<?php

namespace App\Tests\Service\Ai;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Meta\BlogMeta;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Ai\AiProvider;
use App\Service\Ai\Translate\AiPostTranslator;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\LanguageFactory;
use App\Tests\Factory\PostFactory;
use App\Tests\Factory\PostVariantFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiPostTranslatorTest extends KernelTestCase
{

    private function getTranslator(): AiPostTranslator
    {
        return $this->getService(AiPostTranslator::class);
    }

    /**
     * @param array<string, mixed> $content
     */
    private function getPostVariant(array $content): PostVariant
    {
        $blog = new Blog();
        $blog->getMeta()->ai_provider = AiProvider::OPENAI;

        $post = new Post();
        $post->setBlog($blog);

        $language = new Language();
        $language->setCode('en');
        $language->setBlog($blog);

        $postVariant = new PostVariant();
        $postVariant->setPost($post);
        $postVariant->setLanguage($language);
        $postVariant->setContent(json_encode($content, JSON_THROW_ON_ERROR));

        return $postVariant;
    }

    /**
     * @throws \App\Service\Ai\Translate\TranslateException
     */
    public function test_translates_post_variant_simple_paragraph(): void
    {
        $variant = $this->getPostVariant([
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
            ]
        ]);

        $mockResponse = new JsonMockResponse([
            'id' => 'resp_67890abcdef123456',
            'object' => 'response',
            'created_at' => 1712345678,
            'status' => 'completed',
            'error' => null,
            'incomplete_details' => null,
            'model' => 'gpt',
            'output' => [
                [
                    'type' => 'message',
                    'id' => 'msg_67890abcdef123456',
                    'status' => 'completed',
                    'role' => 'assistant',
                    'content' => [
                        [
                            'type' => 'output_text',
                            'text' => json_encode(['0' => '<p>Bonjour, le monde!</p>']),
                            'annotations' => [],
                        ],
                    ],
                ],
            ],
            'usage' => [
                'input_tokens' => 45,
                'output_tokens' => 12,
                'total_tokens' => 57,
            ],
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $this->getContainer()->set(HttpClientInterface::class, $httpClient);

        $translator = $this->getTranslator();
        $translator->translatePostVariant($variant, 'fr');
    }

    /**
     * @throws \App\Service\Ai\Translate\TranslateException
     */
    public function test_translates_post_variant_complex_paragraphs(): void
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
        $translator->translatePostVariant($this->getPostVariant($content), 'fr');

    }

}
