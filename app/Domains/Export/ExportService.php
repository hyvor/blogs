<?php declare(strict_types=1);

namespace App\Domains\Export;

use App\Data\Enums\ExportFormatEnum;
use App\Models\Blog;
use App\Models\Export;
use Illuminate\Support\Collection;

class ExportService
{

    public static function hasPendingExports(Blog $blog) : bool
    {
        return $blog
            ->exports()
            ->where('status', 'pending')
            ->exists();
    }

    public static function createExport(Blog $blog, ExportFormatEnum $format) : Export
    {
        $export = Export::create([
            'format' => $format,
            'blog_id' => $blog->id,
        ]);

        return $export->refresh();
    }

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