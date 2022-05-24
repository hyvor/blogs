<?php
namespace App\Domains\Import\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Data\Enums\ImportFormatEnum;
use App\Domains\Import\Importer;
use App\Models\Blog;
use App\Models\import;
use App\Domains\Import\Parsers\WordpressParser;
use App\Domains\Import\Parsers\MediumParser;
use App\Domains\Import\Parsers\HyvorParser;
use App\Domains\Import\Parsers\GhostParser;
use App\Domains\Import\Parsers\BloggerParser;
use App\Domains\Import\Parsers\TumblrParser;
use App\Domains\Import\Parsers\SubstackParser;
use Illuminate\Support\Facades\Storage;

class ImportJob implements ShouldQueue {

    // public function parse(ImportFormatEnum $platform, $file)
    public function __construct(ImportFormatEnum $platform, Blog $blog, Import $import)
    {
        $parserClass = match($platform->value){
            'wordpress' => WordpressParser::class,
            'medium' => MediumParser::class,
            'ghost' => GhostParser::class,
            'hyvor' => HyvorParser::class,
            'blogger' => BloggerParser::class,
            'tumblr' => TumblrParser::class,
            'substack' => SubstackParser::class,
        };

        $fileName = Import::select('name')
            ->where('blog_id','=', $blog->id)
            ->value('name');

        // $wordpressPath = Storage::get('import\'.$fileName);
        $file = Storage::get('public\wordpress.xml');

        $parser = new $parserClass($file);
        $repository = $parser->parse();

        $importer = new Importer($repository, $blog, $import);
        $importer->import();
    }
}