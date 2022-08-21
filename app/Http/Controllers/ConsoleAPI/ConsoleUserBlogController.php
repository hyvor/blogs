<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\Blog\BlogService;
use App\Domains\User\UserBlogRepository;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Http\Controllers\Controller;
use App\Rules\Subdomain;
use Hyvor\HyvorConnecter\HyvorUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConsoleUserBlogController extends Controller
{
    public function createBlog(Request $request, HyvorUser $hyvorUser)
    {
        $request->validate([
            'name' => 'required|string',
            'subdomain' => ['required_unless:is_dev,true', new Subdomain(checkUnique: true)],
            'is_dev' => 'boolean',
        ]);

        $name = $request->input('name');
        $subdomain = $request->input('subdomain');
        $isDev = $request->input('is_dev', false);

        if ($isDev) {
            $subdomain = 'dev-'.((string) Str::uuid());
        }

        $blog = app(BlogService::class)->createBlog(
            $hyvorUser->id,
            $name,
            $subdomain,
            $isDev ? BlogTypeEnum::DEV : BlogTypeEnum::DEFAULT
        );

        $user = UserRepository::getOwnerOfBlog($blog);

        return response()->json(new UserBlogObject($user));
    }

    public function changeSort(Request $request, HyvorUser $hyvorUser)
    {
        $request->validate([
            'blog_ids' => 'required|array',
            'blog_ids.*' => 'integer',
        ]);

        $blogIds = $request->input('blog_ids');

        UserBlogRepository::changeBlogSorts($hyvorUser, $blogIds);

        return response()->json();
    }

    public function checkSubdomain(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string',
        ]);

        $subdomain = $request->input('subdomain');

        $blog = BlogService::getBlogBySubdomain($subdomain);

        if ($blog) {
            throw new TrustedException('Subdomain already taken');
        }

        return response()->json();
    }
}
