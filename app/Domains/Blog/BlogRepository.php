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
use Hyvor\JsonMeta\Definer;

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
                TrustedException::ERROR_INVALID_INPUT
            );
        }

        // create the blog
        $blog = Blog::create([
            'hyvor_user_id' => $userId, // I changed here from user_id to hyvor_user_id
            'name' => $name,
            'subdomain' => $subdomain,
            'type' => $type->value
        ]);

        // ['user' => $user] = FillNewBlog::fill($blog);

        // return $user;

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
    
    /**
     * @param Blog $blog
     * @param array<string, mixed> $update
     * @return Blog
     */
    public static function updateBlog(Blog $blog, array $updates) : Blog
    {
        
        $metaKeys = $blog->getMetaKeys();
        
        $metaUpdates = []; // metadata
        $realUpdates = []; // real columns
        $updatables = [
            'icon_url', 'featured_image_url',
            'subdomain', 'hosting_at', 'hosting_domain',
            'hosting_url'
        ];
        
        foreach ($updates as $key => $value) {
            if (in_array($key, $metaKeys)) {
                $metaUpdates[$key] = $value;
            } else if (in_array($key, $updatables)) {
                $realUpdates[$key] = $value;
            }
        }
        
        if ($metaUpdates !== []) {
            $blog->setMeta($metaUpdates);
        }
        
        if ($realUpdates !== []) {
            $blog->update($realUpdates);
        }
        
        return $blog;
    }

    public static function createBlogVariant($blog, int $languageId) : void 
    {
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


    public static function updateBlogFeatureImage($blog, $file) : bool {

        $media = MediaRepository::upload($blog->id, $file);
        $featureImageUrl = PermalinkRepository::getMediaPermalink($media, $blog);
        // dd($featureImageUrl);
        // $featureImageId = $media->id;

        return Blog::find($blog->id)
            ->update([
                'featured_image_url' => $featureImageUrl,
            ]);
    }

    public static function updateBlogIcon($blog, $file) : bool {

        $media = MediaRepository::upload($blog->id, $file);
        $iconUrl = PermalinkRepository::getMediaPermalink($media, $blog);
        // dd($iconUrl);
        // $icon = $media->id;

        return Blog::find($blog->id)
            ->update([
                'icon_url' => $iconUrl,
            ]);
    }

}
