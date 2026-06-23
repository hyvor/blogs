<?php declare(strict_types=1);

namespace App\Service\Post\Content;

use Closure;

class ProsemirrorHelper
{
    /**
     * @return array<mixed>
     */
    public function getArrayJson(mixed $json): array
    {
        if (is_string($json) && !empty($json)) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return $decoded;
            }
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

    /**
     * @return array<mixed>
     */
    public function findBlocks(mixed $json, string $blockType): array
    {
        $obj = $this->getArrayJson($json);

        $blocks = [];

        if (!isset($obj['content']) || !is_array($obj['content'])) {
            return $blocks;
        }

        foreach ($obj['content'] as $child) {
            if (!is_array($child)) {
                continue;
            }

            if (isset($child['type']) && $child['type'] === $blockType) {
                $blocks[] = $child;
            }

            if (isset($child['content']) && is_array($child['content'])) {
                $blocks = [...$blocks, ...$this->findBlocks($child, $blockType)];
            }
        }

        return $blocks;
    }

    /**
     * @param mixed $json
     * @param Closure $update a function to match and update a node (return falsy if not updating)
     * @return array<mixed>
     */
    public function updateBlocks(mixed $json, Closure $update): array
    {
        $json = $this->getArrayJson($json);

        if (!isset($json['content']) || !is_array($json['content'])) {
            return $json;
        }

        foreach ($json['content'] as &$child) {
            $child = $update($child);

            if (is_array($child) && isset($child['content']) && is_array($child['content'])) {
                $child = $this->updateBlocks($child, $update);
            }
        }

        return $json;
    }

    /**
     * @return array<mixed>
     */
    public function updateUrls(mixed $json, string $oldUrl, string $newUrl): array
    {
        return $this->updateBlocks($json, function (array $node) use ($oldUrl, $newUrl) {
            $oldPrefix = $oldUrl . '/media/';

            if ($node['type'] === 'image' && isset($node['attrs']) && is_array($node['attrs'])) {
                $attrs = $node['attrs'];
                if (isset($attrs['src']) && is_string($attrs['src'])) {
                    $src = $attrs['src'];
                    if (str_starts_with($src, $oldPrefix)) {
                        $path = str_replace($oldUrl, '', $src);
                        $node['attrs']['src'] = $newUrl . $path;
                    }
                }
            }

            if ($node['type'] === 'text' && isset($node['marks']) && is_array($node['marks'])) {
                foreach ($node['marks'] as &$mark) {
                    if (!is_array($mark)) {
                        continue;
                    }
                    if (
                        $mark['type'] === 'link' &&
                        isset($mark['attrs']) && is_array($mark['attrs']) &&
                        isset($mark['attrs']['href']) && is_string($mark['attrs']['href'])
                    ) {
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
