<?php
namespace App\Models\Concerns;

use App\Data\Enums\CountEnum;
use App\Models\Count;
use Closure;

trait Countable
{

    public function counts()
    {
        return $this->morphMany(Count::class, 'countable');
    }

    public function withCount(CountEnum $name = null)
    {
        return $this->with([
            'counts' => function($query) use ($name) {
                if ($name !== null) {
                    $query->where('name', $name->value);
                }
            }
        ]);
    }

    public function joinCount(string $mainTable, CountEnum $name)
    {
        return $this->join('counts', function($query) use ($mainTable, $name) {
            $query
                ->where('counts.countable_id', "$mainTable.id")
                ->where('countable_type', static::class)
                ->where('name', $name->value);
        });
    }

}