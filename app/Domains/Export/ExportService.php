<?php declare(strict_types=1);

namespace App\Domains\Export;

use App\Models\Blog;
use App\Models\Export;
use Illuminate\Support\Collection;

class ExportService
{

    /**
     * @return Collection<int, Export>
     */
    public static function getExports(Blog $blog) : Collection
    {
        return $blog
            ->exports()
            ->orderBy('id', 'desc')
            ->limit(25)
            ->get();
    }

}