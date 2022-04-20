<?php

namespace App\Domains\Blog;

use App\Data\Enums\BlogTypeEnum;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Models\BlogVariant;
use App\Domains\Language\LanguageRepository;
use App\Models\Language;
use App\Domains\Media\MediaRepository;
use App\Domains\Route\PermalinkRepository;

class BlogRepository
{

    public static function createBlog(
        int $userId, 
        string $name, string $subdomain, 
        BlogTypeEnum $type = BlogTypeEnum::NORMAL    
    // ) : ? User {
    ) {


        $blog = self::getBlogBySubdomain($subdomain);

        if ($blog) {
            throw new TrustedException('This subdomain is already taken, please choose a different subdomain', 
                TrustedException::ERROR_BAD_REQUEST
            );
        }

        // create the blog
        $blog = Blog::create([
            'hyvor_user_id' => $userId, // I changed here from user_id to hyvor_user_id
            'name' => $name,
            'subdomain' => $subdomain,
            'type' => $type->value
        ]);

        ['user' => $user] = FillNewBlog::fill($blog);

        return $user;

    }

    public static function getBlogById(int $id) : ?Blog {
        return Blog::find($id);
    }

    public static function getBlogBySubdomain(string $subdomain) : ?Blog {
        return Blog::where('subdomain', $subdomain)->first();
    }

    public static function getBlogByCustomDomain(string $customDomain) : ?Blog {
        return Blog::where('hosting_domain', $customDomain)->first();
    }
    
    /*
    *
    * ConsoleAPI Settings->General
    *
    */
    // public static function getBlog($blog) {
    //     $language = LanguageRepository::getPrimaryLanguage($blog);

    //     $blogData = Blog::where('blogs.id', '=', $blog->id)
    //     ->join('blog_variants', function($join) use ($language) {
    //         $join->on('blog_variants.blog_id', '=', 'blogs.id');
    //         $join->where('blog_variants.language_id', '=',  $language->id);
    //     })
    //     ->select('blogs.*')
    //     ->get();

    //     // dd($blogData);
    //     return $blogData;
    // } 

    public static function updateBlog($blog, $languageId, array $blogData = []) {

        Blog::find($blog->id)
            ->update([
                'subdomain' => $blogData['subdomain'],
                'social_facebook' => $blogData['social_facebook'] ?? null,
                'social_twitter' => $blogData['social_twitter'] ?? null,
                'social_linkedin' => $blogData['social_linkedin'] ?? null,
                'social_youtube' => $blogData['social_youtube'] ?? null,
                'social_instagram' => $blogData['social_instagram'] ?? null,
                'social_github' => $blogData['social_github'] ?? null,
            ]);

        BlogVariant::where([
                'blog_id' => $blog->id ,
                'language_id'=> $languageId,
            ]) ->update([
                'name' => $blogData['name'] ?? null,
                'description' => $blogData['description']  ?? null,
            ]);
    }

    public static function createBlogVariant($blog, $languageId) {
        $language = Language::where('id','=', $languageId)
        ->value('is_primary');

        if($language == 0){
            $userVariantCheck = BlogVariant::where('blog_id','=', $blog->id)
            ->where('language_id','=', $languageId)
            ->first();

            if($userVariantCheck == null){
                BlogVariant::create([
                    'blog_id' => $blog->id,
                    'language_id' => $languageId,
                ]);
            }
        }
    }


    public static function updateBlogFeatureImage($blog, $file) {

        $media = MediaRepository::upload($blog->id, $file);
        $featureImageUrl = PermalinkRepository::getMediaPermalink($media, $blog);

        // dd($featureImageUrl);
        // $featureImageId = $media->id;

        Blog::find($blog->id)
            ->update([
                'featured_image_url' => $featureImageUrl,
            ]);
    }

    public static function updateBlogIcon($blog, $file) {

        $media = MediaRepository::upload($blog->id, $file);
        $iconUrl = PermalinkRepository::getMediaPermalink($media, $blog);

        // dd($iconUrl);
        // $icon = $media->id;

        Blog::find($blog->id)
            ->update([
                'icon_url' => $iconUrl,
            ]);
    }

}
