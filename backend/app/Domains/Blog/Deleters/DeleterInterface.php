<?php

namespace App\Domains\Blog\Deleters;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Model;

interface DeleterInterface
{
    public function __construct(Blog $blog);

    public function delete() : void;
}
