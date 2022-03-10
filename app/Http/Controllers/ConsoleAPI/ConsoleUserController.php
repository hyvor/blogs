<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\ConsoleAPI\BlogObject;
use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\Blog\BlogRepository;
use App\Domains\User\UserRepository;
use App\Http\Controllers\Controller;
use App\Domains\User\UserRepositoryInterface;
use Hyvor\HyvorConnecter\User;
use Illuminate\Http\Request;

class ConsoleUserController extends Controller
{

    public function createBlog(Request $request, User $hyvorUser)
    {

        $request->validate([
            'name' => 'required|string',
            'subdomain' => 'required|string',
            'type' => 'string|in:normal,temp,dev'
        ]);

        $name = $request->input('name');
        $subdomain = $request->input('subdomain');
        $type = BlogTypeEnum::from($request->input('type') ?? 'normal');

        $user = BlogRepository::createBlog(
            $hyvorUser->id,     
            $name,
            $subdomain,
            $type
        );

        return response()->json(new UserBlogObject($user));

    }

    public function changeSort(Request $request, User $hyvorUser)
    {

        $request->validate([
            'blog_ids' => 'required|array',
            'blog_ids.*' => 'integer'
        ]);

        $blogIds = $request->input('blog_ids');

        UserRepository::changeBlogSorts($hyvorUser->id, $blogIds);
        
    }

    public function checkSubdomain(Request $request)
    {
        $request->validate([
            'subdomain' => 'required|string'
        ]);

        $subdomain = $request->input('subdomain');

        $blog = BlogRepository::getBlogBySubdomain($subdomain);

        return response()->json($blog ? false : true);

    }

}
