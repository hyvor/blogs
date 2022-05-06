<?php

namespace App\Domains\Import;
use App\Data\Enums\ImportFormatEnum;
use  App\Domains\Import\Jobs\ImportJob;
use App\Models\Blog;
use App\Domains\Import\Importer;
use App\Models\Import;
use Illuminate\Support\Facades\Storage;
use App\Domains\Tag\TagRepository;
use Illuminate\Support\Str;


use App\Models\User;
use App\Models\Tag;
use App\Models\UserVariant;
use App\Models\TagVariant;


class Repository
{
    static function import(int $blogId, ImportFormatEnum $platform) {
        $fileName = Import::select('name')
            ->where('blog_id','=', $blogId)
            ->value('name');
        
        // $wordpressPath = Storage::get('import\'.$fileName);
        $file = Storage::get('public\wordpress.xml');

        dispatch(new ImportJob($platform, $file));
    }

    /**
    * @var array<array<string,mixed>>
    */
    public array $authors = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $tags = [];

    /**
    * @var array<array<string,mixed>>
    */
    public array $posts = [];
    
    /**
    * @var array<array<string,mixed>>
    */
    public array $pages = [];

    public static function language(?string $language)
    {
        // dd($language);
        // return Importer::blogData($blogTitle, $blogDescription, $blogLanguage);
    }

    public function author(
        $id, 
        $name,
        $email
    )
    {
        // dd($name);
    }

    public function tag(
        $id, 
        $name
    )
    {
        // dd($name);
    }


    public static function post(
        $id, 
        $title, 
        $createdAt, 
        $postType, 
        $description, 
        $category, 
        $status, 
        $content
    )
    {
        // dd($category);
    }

    public static function page(
        $id, 
        $title, 
        $createdAt, 
        $postType, 
        $description, 
        $category, 
        $status, 
        $content
    )
    {}
} 