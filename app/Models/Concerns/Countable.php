<?php

namespace App\Models\Concerns;

use App\Data\Enums\CountEnum;
use App\Models\Count;
use Exception;
use stdClass;

trait Countable
{


    /*public function counts()
    {
        return $this->morphMany(Count::class, 'countable');
    }

    public function withCount(CountEnum $name = null)
    {
        return $this->with([
            'counts' => function ($query) use ($name) {
                if ($name !== null) {
                    $query->where('name', $name->value);
                }
            },
        ]);
    }

    public function joinCount(string $mainTable, CountEnum $name)
    {
        return $this->join('counts', function ($query) use ($mainTable, $name) {
            $query
                ->where('counts.countable_id', "$mainTable.id")
                ->where('countable_type', static::class)
                ->where('name', $name->value);
        });
    }*/

    public function getCount(string $name) : int
    {
        $this->validateCountName($name);

        $counts = $this->getCountsAsObject();
        return $counts->$name ?? 0;
    }

    public function setCount(string $name, int $value)
    {
        $this->setCounts([$name => $value]);
    }

    public function setCounts(array $counts)
    {

        $fill = [];
        foreach ($counts as $key => $value) {

            $this->validateCountName($key);

            if (!is_int($value)) {
                throw new Exception('Value must be an integer');
            }

            $fill["counts->$key"] = $value;
        }

        $this->forceFill($fill)->save();
    }

    abstract function countsDefinition();


    /**
     * This is to make sure wrong (undefined)
     * count names are not used in the code
     */
    private function validateCountName(string $name)
    {
        if (!in_array($name, $this->countsDefinition())) {
            $class = self::class;
            throw new Exception("Count name $name is not defined in $class");
        }
    }

    private function getCountsAsObject() : object
    {
        $meta = $this->counts;

        if (is_string($meta)) {
            return json_decode($meta) ?? new stdClass;
        } else if (is_array($meta)) {
            return (object) $meta;
        } else if (is_object($meta)) {
            return $meta;
        }

        return new stdClass;
    }
}
