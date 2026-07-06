<?php

namespace App\Service\Import\Importer;

abstract class ParserAbstract
{
    /** @var ImportingPost[] */
    public array $posts = [];

    public function addPost(ImportingPost $post): void
    {
        $this->posts[] = $post;
    }

    abstract public function parse(): void;
}
