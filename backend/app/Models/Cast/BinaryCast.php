<?php declare(strict_types=1);

namespace App\Models\Cast;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * from https://github.com/jBernavaPrah/eloquent-binary-cast/blob/master/src/Casts/BinaryCast.php
 */
class BinaryCast implements CastsAttributes
{

    public function get($model, $key, $value, array $attributes)
    {
        
        if ($value === null) {
            return null;
        }

        if (is_resource($value)) {
            rewind($value);
            $value = stream_get_contents($value);
        }

        return hex2bin($value);
    }

    public function set($model, $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        return bin2hex($value);
    }

}