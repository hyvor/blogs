<?php

namespace App\Tests\Helper;

use BackedEnum;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;

/**
 * Builds JSON-shaped arrays for Hyvor SDK response DTOs (e.g. for
 * JsonMockResponse), filling every constructor parameter not explicitly
 * overridden with a type-appropriate default: null for nullable types,
 * '' / 0 / false / [] for scalars, the first case's value for backed enums.
 *
 * This lets tests mock only the fields they actually care about instead of
 * hand-writing every property of a DTO - and re-writing it again whenever
 * the SDK adds a field.
 *
 * TODO: move this into hyvor/sdk-php as Hyvor\Sdk\Testing\Fixtures.
 */
class Fixtures
{
    /**
     * @param class-string $class
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public static function make(string $class, array $overrides = []): array
    {
        $constructor = (new ReflectionClass($class))->getConstructor();

        if ($constructor === null) {
            return $overrides;
        }

        $data = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            $data[$name] = array_key_exists($name, $overrides)
                ? $overrides[$name]
                : self::defaultFor($parameter->getType());
        }

        return $data;
    }

    private static function defaultFor(?ReflectionType $type): mixed
    {
        if (!$type instanceof ReflectionNamedType) {
            // union/intersection types: null covers the common case of a
            // nullable union; widen this if a non-nullable union comes up.
            return null;
        }

        if ($type->allowsNull()) {
            return null;
        }

        $name = $type->getName();

        if (enum_exists($name)) {
            $first = $name::cases()[0];

            return $first instanceof BackedEnum ? $first->value : $first->name;
        }

        return match ($name) {
            'int' => 0,
            'float' => 0.0,
            'string' => '',
            'bool' => false,
            'array' => [],
            default => null,
        };
    }
}
