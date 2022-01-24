<?php

namespace App\Http\Controllers\DataAPI;

use Illuminate\Support\Collection;

/**
 * Main task of this class is to use the ?key= param to
 * filter objects based on keys
 */
class DataAPIKeysFilter
{
    // default is exclude nothing
    // which means include everything
    private $type = 'exclude';
    private $keys = [];

    private $data;

    public function __construct(array|object $input, ?string $keys)
    {
        $this->decodeKeys($keys);

        $this-> data = $this->filterObject($input);
    }

    private function filterObject($object, $start = '')
    {

        $ret = [];

        if (is_array($object)) {
            foreach ($object as $i => $element) {
                $ret[$i] = $this->filterObject($element, $start);
            }
        } else {
            if (
                is_string($object) ||
                is_int($object) ||
                is_null($object) ||
                is_bool($object)
            ) {
                return $object;
            }

            foreach ($object as $key => $value) {
                $fullKey = $start ? $start . '.' . $key : $key;
                if ($this->type === 'exclude') {
                    if (
                        !in_array($fullKey, $this->keys)
                    ) {
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

    private function isKeyIncluded($key)
    {
        foreach ($this->keys as $checkKey) {
            // if the keys matches
            if ($checkKey === $key) {
                return true;
            }

            /**
             * If the current key's children are defined, the current key is considered included
             * For example, when keys=tags.id,
             * tags key is included
             */
            if (preg_match("/$key\..+/", $checkKey)) {
                return true;
            }
        }
    }

    private function decodeKeys(?string $keys)
    {
        if (is_null($keys)) {
            return;
        }

        /**
         * First, we trim $keys
         *  For HTTP requests, it is already trimmed because Laravel TrimStrings middleware
         *  For Twig requests, it's easier to do here than when calling
         */
        $keys = trim($keys);

        if (empty($keys)) {
            return;
        }

        /**
         * If the keys string starts with !, it means exclude
         * Otherwise, it means include
         */
        if ($keys[0] !== '!') {
            $this->type = 'include';
        } else {
            $keys = str_replace('!', '', $keys);
        }

        $this->keys = array_map('trim', explode(',', $keys));
    }

    public function getData()
    {
        return $this->data;
    }

    static function filter($input, $keys)
    {
        return (new self($input, $keys))->getData();
    }
}
