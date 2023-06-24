<?php declare(strict_types=1);

namespace App\Domains\Blog\Fillers;

use App\Models\Blog;

interface FillerInterface
{
    public function __construct(Blog $blog);

    public function fill() : void;
}
