<?php declare(strict_types=1);

namespace App\Domains\Post\Content;

use Closure;

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

    /**
     * @param mixed $json
     * @param Closure $matchAndUpdate a function to match and update a node (return falsy if not updating)
     * @return array
     */
    public static function updateBlocks(mixed $json, Closure $update) : array
    {
        $json = self::getArrayJson($json);

        foreach ($json['content'] as &$child) {

            $child = $update($child);

            if (isset($child['content']) && is_array($child['content'])) {
                $child = self::updateBlocks($child, $update);
            }

        }

        return $json;

    }

    public static function updateUrls(mixed $json, string $oldUrl, string $newUrl) : array
    {
        return ProsemirrorHelper::updateBlocks($json, function (array $node) use ($oldUrl, $newUrl) {

            $oldPrefix = $oldUrl . '/media/';

            if ($node['type'] === 'image') {
                $src = $node['attrs']['src'] ?? null;

                if (str_starts_with($src, $oldPrefix)) {
                    $path = str_replace($oldUrl, '', $src);
                    $node['attrs']['src'] = $newUrl . $path;
                }
            }

            if ($node['type'] === 'text' && isset($node['marks'])) {

                foreach ($node['marks'] as &$mark) {

                    if ($mark['type'] === 'link') {
                        $href = $mark['attrs']['href'];

                        if (str_starts_with($href, $oldUrl)) {
                            $path = str_replace($oldUrl, '', $href);
                            $mark['attrs']['href'] = $newUrl . $path;
                        }
                    }

                }

            }

            return $node;
        });

    }

}
