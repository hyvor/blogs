<?php declare(strict_types=1);

namespace App\Domains\Import;

use App\Data\Enums\ImportTypeEnum;
use App\Data\Enums\JobStatusEnum;
use App\Models\Blog;
use App\Models\Import;
use Illuminate\Database\Eloquent\Collection;

class ImportService
{

    /**
     * @return Collection<int, Import>
     */
    public static function getImports(Blog $blog) : Collection
    {
        return Import::where('blog_id', $blog->id)->get();
    }


    /**
     * @param array<mixed> $options
     */
    public static function createImport(
        Blog $blog,
        ImportTypeEnum $type,
        string $name,
        array $options,
    ) : Import
    {

        $import = Import::create([
            'blog_id' => $blog->id,
            'status' => JobStatusEnum::PENDING,
            'name' => $name,
            'type' => $type,
            'options' => $options,
        ]);
        
        return $import->refresh();

    }

}