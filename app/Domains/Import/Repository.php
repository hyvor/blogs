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
    

    public static function blogData(?string $blogTitle, ?string $blogDescription, ?string $blogLanguage){
        // dd($this->blog);
        // return Importer::blogData($blogTitle, $blogDescription, $blogLanguage);
    }

    public function authorData(?array $name, ?array $email){
        // language id also should be added when adding the tags to the database.
        // user role should be guest by default to all users.
        // slug should be added according to user name if required with the (-)
        // if the username already exist we have to either add a special character to the slug
        // dd($name, $email);

        $slug = 'test-test';
        $role = 'admin';
        $status = 'active';
        $authorCount = 10;
        // return Importer::authorData($name, $email, $slug, $role, $status, $authorCount );
    }


    public static function tagData(?array $name){
        // it just has the tag name so I should generate the slug for it.
        // language id also should be added when adding the tags to the database.
        // if the tag already exist we have to either add a special character to the slug

        $slug = 'test-test';
        $tagCount = 10;

        // if ($slug == null) {
        //     $slug = Str::slug($name);
        // }
        
        // $currentTag = TagRepository::getTagByBlogIdAndSlug($blog->id, $slug);

        // return Importer::tagData($name, $slug, $tagCount);
    }

    public static function postData(Blog $blog){
        // if there is asssing tags or authors for a specific post it should be added. (post_tag)
        // return Importer::postData($blog);
    }
} 