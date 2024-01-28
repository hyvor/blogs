<?php

namespace Tests\Unit\Domains\Post\Content\Nodes;

use App\Domains\Post\Content\PostContentService;

it('to HTML', function() {

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'table',
                'content' => [
                    [
                        'type' => 'table_row',
                        'content' => [
                            [
                                'type' => 'table_header',
                                'attrs' => [
                                    'colspan' => 1,
                                    'rowspan' => 1,
                                ],
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'content' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'A',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'type' => 'table_cell',
                                'attrs' => [
                                    'colspan' => 1,
                                    'rowspan' => 1,
                                ],
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'content' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'B',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        ]
    ];

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toEqual('<div class="table-container"><table><tr><th><p>A</p></th><td><p>B</p></td></tr></table></div>');

});

it('to HTML with colspan and rowspan', function() {

    $json = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'table',
                'content' => [
                    [
                        'type' => 'table_row',
                        'content' => [
                            [
                                'type' => 'table_header',
                                'attrs' => [
                                    'colspan' => 2,
                                    'rowspan' => 1,
                                ],
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'content' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'A',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'type' => 'table_cell',
                                'attrs' => [
                                    'colspan' => 1,
                                    'rowspan' => 2,
                                ],
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'content' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'B',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ]
                ]
            ]
        ]
    ];

    $html = PostContentService::getHtml($json, blog());

    expect($html)->toEqual('<div class="table-container"><table><tr><th colspan="2"><p>A</p></th><td rowspan="2"><p>B</p></td></tr></table></div>');

});