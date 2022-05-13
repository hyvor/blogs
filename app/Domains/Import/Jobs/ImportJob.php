<?php
namespace App\Domains\Import\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Data\Enums\ImportFormatEnum;
use App\Domains\Import\Importer;
use App\Models\Blog;
use App\Models\import;
use App\Domains\Import\Parsers\WordpressParser;
use App\Domains\Import\Parsers\HyvorParser;
use App\Domains\Import\Parsers\GhostParser;
use App\Domains\Import\Parsers\BloggerParser;
use App\Domains\Import\Parsers\TumblrParser;
use App\Domains\Import\Parsers\SubstackParser;

class ImportJob implements ShouldQueue {

    // public function parse(ImportFormatEnum $platform, $file)
    public function __construct(ImportFormatEnum $platform, $file, Blog $blog, Import $import)
    {
        $parserClass = match($platform->value){
            'wordpress' => WordpressParser::class, 
            'ghost' => GhostParser::class,
            'hyvor' => HyvorParser::class,
            'blogger' => BloggerParser::class,
            'tumblr' => TumblrParser::class,
            'substack' => SubstackParser::class,
        };

        $parser = new $parserClass($file);
        $repository = $parser->parse();

        $importer = new Importer($repository, $blog, $import);
        $importer->import();
    }
}