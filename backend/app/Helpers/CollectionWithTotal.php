<?php declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template T of Model
 */
class CollectionWithTotal
{

    /**
     * @param Collection<int, T> $collection
     */
    public function __construct(
        public Collection $collection,
        public int $total
    ) {
    }
}
