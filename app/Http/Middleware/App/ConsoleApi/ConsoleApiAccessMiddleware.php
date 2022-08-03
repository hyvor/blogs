<?php

namespace App\Http\Middleware\App\ConsoleApi;

use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Closure;
use Hyvor\HyvorConnecter\Login;
use Illuminate\Http\Request;

class ConsoleApiAccessMiddleware
{
    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
    }

    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY');

        if ($apiKey) {
            if ($this->blog->api_key_console === null) {
                throw new TrustedException('Console API is not enabled');
            }

            if ($apiKey !== $this->blog->api_key_console) {
                throw new TrustedException('Invalid API key');
            }

            $owner = UserRepository::getOwnerOfBlog($this->blog);

            app()->instance(
                ConsoleApiAccessingUser::class,
                new ConsoleApiAccessingUser($owner)
            );
        } else {
            $hyvorUser = Login::check();
            if (! $hyvorUser) {
                throw new TrustedException('You are not logged in');
            }

            $user = UserRepository::getUserByBlogIdAndHyvorUserId($this->blog->id, $hyvorUser->id);

            if (! $user) {
                throw new TrustedException(
                    'You do not have access to this blog',
                    TrustedException::ERROR_UNAUTHORIZED
                );
            }

            app()->instance(
                ConsoleApiAccessingUser::class,
                new ConsoleApiAccessingUser($user)
            );
        }

        return $next($request);
    }
}
