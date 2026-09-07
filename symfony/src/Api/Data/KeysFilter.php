<?php

namespace App\Api\Data;

/**
 * Filters object properties based on a keys parameter string.
 *
 * - null keys: return everything
 * - "id,slug": include only these keys (include mode)
 * - "!id,!slug": exclude these keys (exclude mode)
 * - "tags.id": filter nested object keys
 */
class KeysFilter
{
    // default is exclude nothing = include everything
    private string $type = 'exclude';

    /** @var string[] */
    private array $keys = [];

    private mixed $data;

    public function __construct(mixed $input, ?string $keys)
    {
        $this->decodeKeys($keys);
        $this->data = $this->filterObject($input);
    }

    private function filterObject(mixed $object, string $start = ''): mixed
    {
        $ret = [];

        if (is_iterable($object)) {
            foreach ($object as $i => $element) {
                /** @var int|string $i */
                $ret[$i] = $this->filterObject($element, $start);
            }
        } else {
            if (
                is_string($object) ||
                is_int($object) ||
                is_null($object) ||
                is_bool($object) ||
                is_float($object) ||
                (is_object($object) && enum_exists($object::class))
            ) {
                return $object;
            }

            foreach ((array)$object as $key => $value) {
                $fullKey = $start ? $start . '.' . $key : $key;
                if ($this->type === 'exclude') {
                    if (!in_array($fullKey, $this->keys, true)) {
                        $ret[$key] = $this->filterObject($value, $fullKey);
                    }
                } else {
                    if ($this->isKeyIncluded($fullKey)) {
                        $ret[$key] = $this->filterObject($value, $fullKey);
                    }
                }
            }
        }

        return $ret;
    }

    private function isKeyIncluded(string $key): bool
    {
        foreach ($this->keys as $checkKey) {
            // exact match
            if ($checkKey === $key) {
                return true;
            }

            // if the key's children are defined, the key itself is included
            // e.g. keys=tags.id → tags should be included
            if (preg_match('/' . preg_quote($key, '/') . '\..+/', $checkKey)) {
                return true;
            }

            // if keys=tags, tags.* should also be included
            if (preg_match('/' . preg_quote($checkKey, '/') . '\..+/', $key)) {
                return true;
            }
        }

        return false;
    }

    private function decodeKeys(?string $keys): void
    {
        if ($keys === null) {
            return;
        }

        $keys = trim($keys);

        if ($keys === '') {
            return;
        }

        if ($keys[0] !== '!') {
            $this->type = 'include';
        } else {
            $keys = str_replace('!', '', $keys);
        }

        $this->keys = array_map('trim', explode(',', $keys));
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public static function filter(mixed $input, ?string $keys): mixed
    {
        return (new self($input, $keys))->getData();
    }
}
