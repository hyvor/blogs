<?php

namespace App\Service\Delivery\RouteMatcher;

class MatchedRoute
{
    public readonly string $name;

    /** @var array<string, mixed> */
    public readonly array $params;

    /** @param array<string, mixed> $props */
    public function __construct(array $props)
    {
        $params = [];
        $name = '';
        foreach ($props as $key => $value) {
            if ($key === '_route') {
                $name = is_string($value) ? $value : '';
            } else {
                $params[$key] = $value;
            }
        }
        $this->name = $name;
        $this->params = $params;
    }

    public function param(string $key): ?string
    {
        $value = $this->params[$key] ?? null;
        return is_string($value) ? $value : null;
    }
}
