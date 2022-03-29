<?php
namespace App\Domains\Count;

use App\Data\Enums\CountEnum;
use Illuminate\Database\Eloquent\Model;

/**
 * For Count model
 */
class CountRepository {

    public static function setCount(Model $model, CountEnum $name, string $count) {

        $model->counts()->updateOrCreate(
            ['name' => $name],
            ['value' => $count]
        );

    }

    public static function getCounts(Model $model, array $names) {

        $counts = $model->counts()->whereIn('name', $names)->get()->keyBy('name');

        $ret = [];
        foreach ($names as $name) {
            $ret[$name] = $counts->get($name)?->value ?? 0;
        } 

        return $ret;

    }

}