<?php

namespace App\Domains\App\AppContext;

class AppContext
{

    /**
     * @var AppContextType[]
     */
    static array $contexts = [];

    public static function start(AppContextType $type): void
    {
        //
    }

    public static function end(AppContextType $type): void
    {
        //
    }

    public static function in(): bool
    {
        //
    }

    public static function flush() : void
    {
        //
    }

}