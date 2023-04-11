<?php declare(strict_types=1);

namespace App\Domains\Export;

use App\Data\Enums\ExportFormatEnum;
use App\Models\Blog;
use App\Models\Export;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class Exporter
{

    private Blog $blog;
    private ExportFormatEnum $format;
    private Export $export;

    public function __construct(
        Blog $blog,
        ExportFormatEnum $format,
    )
    {

        $this->blog = $blog;
        $this->format = $format;

        $this->export = Export::create([
            'format' => $format,
            'blog_id' => $blog->id,
        ]);

    }

    public function export() : void
    {

        $exporterClass = match ($this->format) {
            ExportFormatEnum::HYVOR_BLOGS => HyvorBlogsExporter::class,
            default => throw new \Exception('Invalid format'),
        };

        $exporter = new $exporterClass($this->blog);
        $filePath = $exporter->createFile();

        $path = Storage::putFileAs(
            '/exports/' . $this->blog->id,
            new File($filePath),
            date('Y-m-d') . '-' . $this->export->id . '.' . pathinfo($filePath, PATHINFO_EXTENSION)
        );

        if (!$path) {
            $this->fail('Failed to upload file to storage');
            return;
        }

        Storage::setVisibility($path, 'public');

        $url = config('filesystems.disks.s3.url') . '/' . $path;

        $this->export->update([
            'status' => 'completed',
            'url' => $url,
        ]);

    }

    private function fail(string $error) : void
    {
        $this->export->update([
            'status' => 'failed',
            'error' => $error,
        ]);
    }

}