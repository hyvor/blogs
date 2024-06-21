<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Objects\ConsoleAPI\UserBlog\UserBlogObject;
use App\Domains\User\UserBlogRepository;
use App\Domains\User\UserRepository;
use App\Http\Controllers\Controller;
use Hyvor\HyvorConnecter\Login;
use Hyvor\HyvorConnecter\Redirect;
use Hyvor\SyntaxHighlighter\Highlighter;
use Illuminate\Http\Request;

class ConsoleViewController extends Controller
{
    public function __invoke(Request $request) : mixed
    {
        $hyvorUser = Login::check();


        if ($hyvorUser === null) {
            $isSignup = $request->boolean('signup');
            return $isSignup ? Redirect::toSignup() : Redirect::toLogin();
        }

        $blogs = UserBlogRepository::getBlogsOfUser($hyvorUser->id)->mapInto(UserBlogObject::class);

        $config = [
            // state

            'hyvorUser' => $hyvorUser,
            'blogs' => $blogs,

            'is_blocked' => UserRepository::isBlocked($hyvorUser->id),

            // static

            'domains' => [
                'app' => config('blogs.domain_app'),
                'delivery' => config('blogs.domain_delivery'),
                'hyvor' => config('blogs.domain_hyvor'),
            ],

            'syntax_themes' => Highlighter::getAllThemes(),

            'limits' => [
                'max_theme_zip_size_kb' => config('limits.max_theme_zip_size_kb'),
                'max_asset_file_size' => config('limits.max_asset_file_size'),
            ],
        ];

        return view('console', ['config' => $config]);
    }
}
