<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\CountEnum;
use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Data\Objects\ConsoleAPI\LanguageObject;
use App\Data\Objects\ConsoleAPI\Tag\TagObject;
use App\Data\Objects\ConsoleAPI\User\UserObject;
use App\Domains\Blog\BlogCountsRepository;
use App\Domains\Blog\BlogRepository;
use App\Domains\Count\CountRepository;
use App\Domains\Language\LanguageRepository;
use App\Domains\Tag\TagRepository;
use App\Domains\User\UserRepository;
use App\Http\Controllers\Controller;

use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'users' => UserRepository::getUsers($blog, limit: 15)->map(fn($user) => new UserObject($user, $blog)),
            'tags' => TagRepository::getTags($blog, limit: 15)->map(fn($tag) => new TagObject($tag, $blog)),
            'languages' => LanguageRepository::getAllLanguages($blog)->map(fn($language) => new LanguageObject($language))
        ]);
    }

    public function getPostsCounts(Blog $blog)
    {
        return response()->json(BlogCountsRepository::getPostsCounts($blog));
    }

    public static function updateBlog(Request $request, Blog $blog)
    {
        $updates = $request->all();
        $blog = BlogRepository::updateBlog($blog, $updates);

        return response()->json(new BlogObject($blog));
    }

    public static function createBlogVariant(Request $request, Blog $blog)
    {
        $languageId = $request->input('languageId');
        $createVariant = BlogRepository::createBlogVariant($blog, $languageId);

        return response()->json($createVariant);
    }

}
