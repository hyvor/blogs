<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\BlogHostingAtEnum;
use App\Data\Enums\ColorModeDefaultEnum;
use App\Data\Enums\ColorModesEnum;
use App\Data\Enums\CommentsTypeEnum;
use App\Data\Enums\CountEnum;
use App\Data\Enums\SeoExternalLinksFollowEnum;
use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Data\Objects\ConsoleAPI\BlogVariantObject;
use App\Data\Objects\ConsoleAPI\LanguageObject;
use App\Data\Objects\ConsoleAPI\Tag\TagObject;
use App\Data\Objects\ConsoleAPI\User\UserObject;
use App\Domains\Blog\BlogService;
use App\Domains\Count\CountRepository;
use App\Domains\Language\LanguageRepository;
use App\Domains\Tag\TagRepository;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;

use App\Models\Blog;
use App\Models\Language;
use App\Rules\BlogDescription;
use App\Rules\BlogHostingDomain;
use App\Rules\BlogName;
use App\Rules\Subdomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class ConsoleBlogController extends Controller
{
    /**
     * Get initial data of a blog that is required to load it in the Console.
     *
     * @param Blog $blog
     * @return JsonResponse
     */
    public function getBlogData(Blog $blog)
    {
        $counts = CountRepository::getCounts(new Blog(), [
            CountEnum::BLOG_POSTS,
            CountEnum::BLOG_POSTS_DRAFT,
            CountEnum::BLOG_POSTS_SCHEDULED,
            CountEnum::BLOG_POSTS_FEATURED,
        ]);

        return response()->json([
            'blog' => new BlogObject($blog),
            'counts' => [
                'published' => $counts[CountEnum::BLOG_POSTS->value],
                'draft' => $counts[CountEnum::BLOG_POSTS_DRAFT->value],
                'scheduled' => $counts[CountEnum::BLOG_POSTS_SCHEDULED->value],
                'featured' => $counts[CountEnum::BLOG_POSTS->value],
            ],
            'users' => UserRepository::getUsers($blog, limit: 15)->map(fn ($user) => new UserObject($user, $blog)),
            'tags' => TagRepository::getTags($blog, limit: 15)->map(fn ($tag) => new TagObject($tag, $blog)),
            'languages' => LanguageRepository::getAllLanguages($blog)->map(fn ($language) => new LanguageObject($language)),
        ]);
    }

    /**
     * Updates a blog.
     *
     * @param Request $request
     * @param Blog $blog
     * @return JsonResponse
     */
    public static function updateBlog(Request $request, Blog $blog)
    {
        $validate = [

            'subdomain' => new Subdomain(checkUnique: true),
            'hosting_at' => new Enum(BlogHostingAtEnum::class),
            'hosting_domain' => new BlogHostingDomain(),
            'hosting_url' => 'string|url|nullable',

            // meta
            'logo_url' => 'url|nullable',
            'cover_url' => 'url|nullable',

            'social_facebook' => 'url|nullable',
            'social_twitter' => 'url|nullable',
            'social_linkedin' => 'url|nullable',
            'social_youtube' => 'url|nullable',
            'social_tiktok' => 'url|nullable',
            'social_instagram' => 'url|nullable',
            'social_github' => 'url|nullable',

            'code_head' => 'string|nullable',
            'code_foot' => 'string|nullable',

            'seo_indexing' => 'boolean',
            'seo_robots_txt' => 'string|nullable',
            'seo_external_links_follow' => new Enum(SeoExternalLinksFollowEnum::class),

            'comments_type' => new Enum(CommentsTypeEnum::class),
            'comments_ht_website_id' => 'integer|nullable',
            'comments_ht_api_key' => 'string|nullable',
            'comments_code' => 'string|nullable',
            'newsletter_code' => 'string|nullable',

            'color_modes' => new Enum(ColorModesEnum::class),
            'color_mode_default' => new Enum(ColorModeDefaultEnum::class),

            'syntax_on' => 'boolean',
            'syntax_line_numbers' => 'boolean',
            'syntax_theme' => 'string|nullable',
        ];
        $request->validate($validate);

        $updates = $request->all();

        $blog = BlogService::updateBlog($blog, $updates);

        return response()->json(new BlogObject($blog));
    }


    /**
     * Creates a blog variant.
     *
     * @param Request $request
     * @param Blog $blog
     * @return JsonResponse
     * @throws TrustedException
     */
    public static function createBlogVariant(Request $request, Blog $blog, Language $language)
    {
        $request->validate([
            'language_id' => 'required|integer',
        ]);

        $variant = BlogService::createBlogVariant($blog, $language);

        return response()->json(new BlogVariantObject($variant));
    }

    /**
     * Updates a blog variant.
     *
     * @param Request $request
     * @param Blog $blog
     * @return JsonResponse
     * @throws TrustedException
     */
    public static function updateBlogVariant(Request $request, Blog $blog, Language $language)
    {
        $request->validate([
            'language_id' => 'required|integer',
            'name' => new BlogName(),
            'description' => new BlogDescription(),
        ]);

        $updates = [];
        if ($request->has('name')) {
            $updates['name'] = $request->input('name');
        }
        if ($request->has('description')) {
            $updates['description'] = $request->input('description');
        }

        $variant = BlogService::updateBlogVariant($blog, $language, $updates);

        return response()->json(new BlogVariantObject($variant));
    }
}
