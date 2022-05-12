<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\Blog\BlogRepository;
use App\Domains\User\UserRepository;
use App\Http\Controllers\Controller;
use Hyvor\HyvorConnecter\HyvorUser;
use Illuminate\Http\Request;

class ConsoleUserBlogController extends Controller
{
    public function createBlog(Request $request, HyvorUser $hyvorUser)
    {
        $request->validate([
            'name' => 'required|string',
            'subdomain' => 'required|string',
            'is_dev' => 'boolean'
        ]);

        $name = $request->input('name');
        $subdomain = $request->input('subdomain');
        $isDev = $request->input('is_dev');

        $blog = BlogRepository::createBlog(
            $hyvorUser->id,
            $name,
            $subdomain,
            $isDev ? BlogTypeEnum::DEV : BlogTypeEnum::DEFAULT
        );

        $user = UserRepository::getUserByBlogOwnership($blog->id);

        return response()->json(new UserBlogObject($user));
    }

    public function changeSort(Request $request, User $hyvorUser)
    {
        $request->validate([
            'blog_ids' => 'required|array',
            'blog_ids.*' => 'integer',
        ]);

        $blogIds = $request->input('blog_ids');

        UserRepository::changeBlogSorts($hyvorUser->id, $blogIds);

        return response()->json();
    }

    public function checkSubdomain(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string',
        ]);

        $subdomain = $request->input('subdomain');

        $blog = BlogRepository::getBlogBySubdomain($subdomain);

        return response()->json($blog ? false : true);
    }

}
