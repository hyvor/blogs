<?php

namespace App\Domains\App\AppContext;

use App\Exceptions\SafetyException;

class AppContext
{

    /**
     * @var AppContextType[]
     */
    static array $contexts = [];

    public static function start(AppContextType $type): void
    {
        self::$contexts[] = $type->value;
    }

    public static function end(AppContextType $type): void
    {
        $key = array_search($type->value, self::$contexts);

        if ($key !== false) {
            unset(self::$contexts[$key]);
        } else {
            throw new SafetyException('App Context not started');
        }
    }

    public static function in(AppContextType $type): bool
    {
        if (in_array($type->value, self::$contexts))
            return true;
        return false;
    }

    public static function flush() : void
    {
        self::$contexts = [];
    }

}