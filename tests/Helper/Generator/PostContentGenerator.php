<?php declare(strict_types=1);

namespace Tests\Helper\Generator;

use Faker\Factory;

class PostContentGenerator
{

    public static function generateRandom() : string
    {
        $faker = Factory::create();
        $paragraphs = (array) $faker->paragraphs(rand(2, 6));
        $content = [
            'type' => 'doc',
            'content' => [],
        ];
        foreach ($paragraphs as $para) {
            $content['content'][] = [
                'type' => 'paragraph',
                'content' => [[
                    'type' => 'text',
                    'text' => $para,
                ]],
            ];
        }

        return strval(json_encode($content));
    }

    public static function generateParagraph(string $text = null) : string
    {

        $content = [
            'type' => 'doc',
            'content' => [],
        ];
        $content['content'][] = [
            'type' => 'paragraph',
            'content' => [[
                'type' => 'text',
                'text' => $text ?? Factory::create()->paragraph,
            ]],
        ];

        return strval(json_encode($content));

    }

}