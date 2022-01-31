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

}