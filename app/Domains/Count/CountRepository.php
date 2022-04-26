<?php
namespace App\Domains\Count;

use App\Data\Enums\CountEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * For Count model
 */
class CountRepository {

    public static function setCount(Model $model, CountEnum $name, string $count) : void
    {

        $model->counts()->updateOrCreate(
            ['name' => $name],
            ['value' => $count]
        );

    }

    /**
     * @param $names - Array<CountEnum>
     */
    public static function getCounts(Model $model, array $names) : array
    {

        $names = array_map(function($name) {
            return $name->value;
        }, $names);

        $counts = $model->counts()->whereIn('name', $names)->get()->keyBy('name');

        $ret = [];
        foreach ($names as $name) {
            $ret[ $name ] = $counts->get($name)?->value ?? 0;
        }

        return $ret;

    }

    public static function getCount(Model $model, string $name) : int
    {

        $count = $model->counts()->where('name', $name)->first()?->value ?? 0;

        return (int) $count;

    }

    /**
     * IMPORTANT!
     * $model and $name considered to be safe. Never call this with user input
     */
    public static function getSubQueryForCount(Model $model, CountEnum $name, string $as = null)
    {
        

        $className = get_class($model);
        $tableName = $model->getTable();
        $countName = $name->value;
        $as = $as ?? '_count';

        return DB::raw(
            "COALESCE(
                (
                    SELECT value 
                    FROM counts
                    WHERE 
                        countable_type = '$className' AND
                        countable_id = $tableName.id AND
                        name = '$countName'
                    LIMIT 1
                )
            , 0) as $as
            "
        ,);
    }

}