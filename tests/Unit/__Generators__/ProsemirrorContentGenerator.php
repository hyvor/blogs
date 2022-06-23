<?php

namespace Tests\Unit\__Generators__;

use Faker\Factory;

class ProsemirrorContentGenerator
{
    public static function getParas(): string
    {
        $faker = Factory::create();
        $paragraphs = $faker->paragraphs(rand(2, 6));
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

        return json_encode($content);
    }
}
