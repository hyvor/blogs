<?php

namespace App\Tests\Service\Ai;

use App\Entity\Blog;
use App\Entity\Language;
use App\Entity\Post;
use App\Entity\PostVariant;
use App\Service\Ai\AiModel;
use App\Service\Ai\Translate\AiPostTranslator;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * TODO: this doesn't 100% test everything.
 * Migrate to test via the API endpoint
 */
#[CoversClass(AiPostTranslator::class)]
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
        $blog->getMeta()->ai_model = AiModel::GPT_5_6_LUNA;

        $post = new Post();
        $post->setBlog($blog);

        $language = new Language();
        $language->setCode('en');
        $language->setBlog($blog);

        $postVariant = new PostVariant();
        $postVariant->setPost($post);
        $postVariant->setLanguage($language);
        $postVariant->setContentUnsaved(json_encode($content, JSON_THROW_ON_ERROR));

        return $postVariant;
    }

    private function setMockResponse(array $responseJson): void
    {
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
                            'text' => json_encode($responseJson),
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

        $this->setMockResponse([
            'content' => [
                [
                    'id' => 0,
                    'html' => '<p>bonjour, le monde!</p>'
                ]
            ]
        ]);

        $translator = $this->getTranslator();
        $translated = $translator->translatePostVariant($variant, 'fr');

        $this->assertStringContainsString(
            'bonjour, le monde!',
            $translated['content']
        );
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

        $this->setMockResponse([
            'content' => [
                [
                    'id' => 0,
                    'html' => '<p>bonjour, le monde!</p>'
                ],
                [
                    'id' => 1,
                    'html' => '<p>aujourd\'hui, nous discuterons de <strong>l\'importance de l\'IA dans la technologie moderne.</strong></p>'
                ],
                [
                    'id' => 2,
                    'html' => '<h2>Histoire de l\'IA</h2>'
                ],
                [
                    'id' => 3,
                    'html' => '<p class="button-wrap"><a href="" target="_blank" class="button">Cliquez ici</a></p>'
                ]
            ]
        ]);

        $translator = $this->getTranslator();
        $response = $translator->translatePostVariant($this->getPostVariant($content), 'fr');

        $this->assertStringContainsString(
            'aujourd\'hui, nous discuterons de',
            $response['content']
        );

    }

}
