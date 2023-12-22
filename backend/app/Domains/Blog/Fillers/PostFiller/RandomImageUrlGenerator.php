<?php declare(strict_types=1);

namespace App\Domains\Blog\Fillers\PostFiller;

class RandomImageUrlGenerator
{

    private const URL = 'https://res.cloudinary.com/dqabfne6s/image/upload/v1689824633/blogs.hyvor.com/filler-images';

    public static function getFeaturedImageUrl() : string
    {
        $min = 1;
        $max = 20;
        $random = rand($min, $max);
        return self::URL . '/post-featured-images/' . $random . '.webp';
    }

    public static function getUserImageUrl() : string
    {
        $min = 1;
        $max = 5;
        $random = rand($min, $max);
        return self::URL . '/author-images/' . $random . '.webp';
    }

}