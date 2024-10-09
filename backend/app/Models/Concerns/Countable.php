<?php declare(strict_types=1);

namespace App\Models\Concerns;

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

    public function getCount(string $name): int
    {
        $this->validateCountName($name);

        $counts = $this->getCountsAsObject();

        return $counts->$name ?? 0;
    }

    public function setCount(string $name, int $value) : void
    {
        $this->setCounts([$name => $value]);
    }

    /**
     * @param array<string, mixed> $counts
     */
    public function setCounts(array $counts) : void
    {
        $fill = [];
        foreach ($counts as $key => $value) {
            $this->validateCountName($key);

            if (! is_int($value)) {
                throw new Exception('Value must be an integer');
            }

            $fill["counts->$key"] = $value;
        }

        $this->forceFill($fill)->save();
    }

    abstract public function countsDefinition();

    /**
     * This is to make sure wrong (undefined)
     * count names are not used in the code
     */
    private function validateCountName(string $name) : void
    {
        if (! in_array($name, $this->countsDefinition())) {
            $class = self::class;
            throw new Exception("Count name $name is not defined in $class");
        }
    }

    private function getCountsAsObject(): object
    {
        /** @var mixed $meta */
        $meta = $this->counts;

        if ($meta === null) {
            return new stdClass();
        }

        if (is_string($meta)) {
            return json_decode($meta) ?? new stdClass();
        } elseif (is_array($meta)) {
            return (object) $meta;
        } elseif (is_object($meta)) {
            return $meta;
        }

        return new stdClass();
    }
}
