<?php

namespace App\Domains\Post\Content;

class ProsemirrorHelper
{
    public static function getArrayJson(mixed $json): array
    {
        if (is_string($json) && ! empty($json)) {
            return json_decode($json, true);
        }

        if (is_array($json)) {
            return $json;
        }

        if (is_object($json)) {
            return (array) $json;
        }

        return [
            'type' => 'doc',
            'content' => [],
        ];
    }

    public static function findBlocks(mixed $json, string $blockType): array
    {
        $obj = self::getArrayJson($json);

        $blocks = [];

        if (! isset($obj['content'])) {
            return $blocks;
        }

        foreach ($obj['content'] as $child) {
            if (isset($child['type']) && $child['type'] === $blockType) {
                $blocks[] = $child;
            }

            if (isset($child['content']) && is_array($child['content'])) {
                $blocks = [...$blocks, ...self::findBlocks($child, $blockType)];
            }
        }

        return $blocks;
    }
}
