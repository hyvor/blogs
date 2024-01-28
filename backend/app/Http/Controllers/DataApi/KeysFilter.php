<?php declare(strict_types=1);

namespace App\Http\Controllers\DataApi;

/**
 * Main task of this class is to use the ?key= param to
 * filter objects based on keys
 */
class KeysFilter
{
    // default is exclude nothing
    // which means include everything
    private string $type = 'exclude';

    /**
     * @var array<string>
     */
    private array $keys = [];

    private mixed $data;

    public function __construct(mixed $input, ?string $keys)
    {
        $this->decodeKeys($keys);

        $this->data = $this->filterObject($input);
    }

    private function filterObject(mixed $object, string $start = '') : mixed
    {
        $ret = [];

        if (is_iterable($object)) {
            foreach ($object as $i => $element) {
                $ret[$i] = $this->filterObject($element, $start);
            }
        } else {
            if (
                is_string($object) ||
                is_int($object) ||
                is_null($object) ||
                is_bool($object) ||
                (is_object($object) && enum_exists($object::class))
            ) {
                return $object;
            }

            foreach ((array) $object as $key => $value) {
                $fullKey = strval($start ? $start.'.'.$key : $key);
                if ($this->type === 'exclude') {
                    if (
                        ! in_array($fullKey, $this->keys)
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

    private function isKeyIncluded(string $key) : bool
    {
        foreach ($this->keys as $checkKey) {
            // if the keys matches
            if ($checkKey === $key) {
                return true;
            }

            /**
             * If the current key's children are defined, the current key is considered included
             * For example, when keys=tags.id,
             * tags key should be included
             */
            if (preg_match("/$key\..+/", $checkKey)) {
                return true;
            }

            /**
             * The opposite of the above
             * If when keys=tags, tags.* should also be included
             */
            if (preg_match("/$checkKey\..+/", $key)) {
                return true;
            }
        }

        return false;
    }

    private function decodeKeys(?string $keys) : void
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

    public function getData(): mixed
    {
        return $this->data;
    }

    public static function filter(object $input, ?string $keys): mixed
    {
        return (new self($input, $keys))->getData();
    }
}
