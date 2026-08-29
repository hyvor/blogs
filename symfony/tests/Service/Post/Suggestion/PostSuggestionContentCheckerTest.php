<?php

namespace App\Tests\Service\Post\Suggestion;

use App\Service\Post\Suggestion\PostSuggestionContentChecker;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(PostSuggestionContentChecker::class)]
class PostSuggestionContentCheckerTest extends KernelTestCase
{

    #[TestWith([
        [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [
                        'suggestions' => null,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'italic',
                            'marks' => [['type' => 'em']],
                        ],
                    ],
                ],
            ],
        ],
        false,
    ], 'no suggestions')]
    #[TestWith([
        [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [
                        'suggestions' => [
                            [
                                'type' => 'comment',
                                'id' => 1,
                            ],
                        ],
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'italic',
                            'marks' => [['type' => 'em']],
                        ],
                    ],
                ],
            ],
        ],
        false
    ], 'comment only in attr')]
    #[TestWith([
        [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'attrs' => [
                        'suggestions' => [
                            [
                                'type' => 'insert',
                                'id' => 1,
                            ],
                        ],
                    ],
                    'content' => [],
                ],
            ],
        ],
        true
    ], 'insert in attr')]
    #[TestWith([
        [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'italic',
                            'marks' => [
                                [
                                    'type' => 'suggestion',
                                    'attrs' => [
                                        'type' => 'insert',
                                        'id' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        true
    ], 'insert in mark')]
    #[TestWith([
        [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'italic',
                            'marks' => [
                                [
                                    'type' => 'suggestion',
                                    'attrs' => [
                                        'type' => 'comment',
                                        'id' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        false
    ], 'comment in mark')]
    public function test_check_suggestions(array $doc, bool $expectedHas): void
    {
        $json = json_encode($doc, JSON_THROW_ON_ERROR);
        $checker = $this->getService(PostSuggestionContentChecker::class);
        $has = $checker->hasPendingSuggestions($json);
        $this->assertSame($expectedHas, $has);
    }

}
