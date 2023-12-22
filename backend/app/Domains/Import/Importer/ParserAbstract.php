<?php declare(strict_types=1);

namespace App\Domains\Import\Importer;

abstract class ParserAbstract
{

    /** @var ImportingPost[] */
    public array $posts = [];

    public function addPost(ImportingPost $post) : void
    {
        $this->posts[] = $post;
    }

    abstract public function parse() : void;

}