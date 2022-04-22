<?php
namespace App\Domains\Post\Observers;

use App\Domains\Post\Content\PostContentMetaRepository;
use App\Models\PostVariant;

class PostVariantObserver
{

    public function created(PostVariant $variant)
    {

        

    }

    public function updated(PostVariant $variant)
    {

        PostContentMetaRepository::updateWordCount($variant);

    }

}