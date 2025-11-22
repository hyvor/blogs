<?php

declare(strict_types=1);

namespace App\Http\Middleware\App\ConsoleApi;

use App\Data\Enums\ApiKeysTypeEnum;
use App\Data\Enums\BlogTypeEnum;
use App\Domains\Api\ApiKeysRepository;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Closure;
use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthInterface;
use Illuminate\Http\Request;

class ConsoleApiAccessMiddleware
{

    private Blog $blog;

    public function __construct(
        Blog $blog,
        private AuthInterface $auth,
    ) {
        $this->blog = $blog;
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $apiKey = $request->header('X-API-KEY');

        if ($this->blog->type === BlogTypeEnum::TEMP) {
            $owner = UserRepository::getOwnerOfBlog($this->blog);
            if (!$owner) {
                throw new TrustedException('Blog owner not found');
            }

            app()->instance(
                ConsoleApiAccessingUser::class,
                new ConsoleApiAccessingUser($owner)
            );
        } else {
            if ($apiKey) {
                if (!ApiKeysRepository::hasKey($this->blog, ApiKeysTypeEnum::CONSOLE, $apiKey)) {
                    throw new TrustedException('Invalid API key');
                }

                $owner = UserRepository::getOwnerOfBlog($this->blog);
                if (!$owner) {
                    throw new TrustedException('Blog owner not found');
                }

                app()->instance(
                    ConsoleApiAccessingUser::class,
                    new ConsoleApiAccessingUser($owner)
                );
            } else {
                $hyvorUser = $this->auth->check($request);
                if (!$hyvorUser) {
                    throw new TrustedException('You are not logged in');
                }

                $user = UserRepository::getUserByBlogIdAndHyvorUserId($this->blog->id, $hyvorUser->id);

                if (!$user) {
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
        }

        return $next($request);
    }
}
