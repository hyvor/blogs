<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\Blog\BlogService;
use App\Domains\User\UserBlogRepository;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Http\ConsoleApi\Objects\Blog\BlogListObject;
use App\Http\Controllers\Controller;
use App\Rules\Subdomain;
use Hyvor\Helper\Http\Middleware\AccessAuthUser;
use Hyvor\HyvorConnecter\HyvorUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConsoleUserBlogController extends Controller
{
    public function createBlog(Request $request, AccessAuthUser $hyvorUser) : JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'subdomain' => ['required_unless:is_dev,true', new Subdomain(checkUnique: true)],
            'is_dev' => 'boolean',
        ]);

        $name = $request->input('name');
        $subdomain = $request->input('subdomain');
        $isDev = $request->input('is_dev', false);
        $ip = $request->ip();

        if ($isDev) {
            $subdomain = 'dev-'.((string) Str::uuid());
        }

        if (!BlogService::canUserCreateBlog($hyvorUser->id)) {
            throw new TrustedException('Please upgrade at least one of your blogs to create more.');
        }

        $blog = app(BlogService::class)->createBlog(
            $hyvorUser->id,
            $name,
            $subdomain,
            $isDev ? BlogTypeEnum::DEV : BlogTypeEnum::DEFAULT,
            ip: $ip
        );

        $user = UserRepository::getOwnerOfBlog($blog);

        if (!$user) {
            throw new TrustedException('User not found');
        }

        return response()->json(new BlogListObject($user));
    }


    public function checkSubdomain(Request $request) : JsonResponse
    {
        $request->validate([
            'subdomain' => 'required|string',
        ]);

        $subdomain = $request->input('subdomain');

        $blog = BlogService::getBlogBySubdomain($subdomain);

        return response()->json([
            'available' => $blog === null,
        ]);
    }
}
