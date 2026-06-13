<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Data\Enums\BlogTypeEnum;
use App\Domains\Blog\BlogService;
use App\Domains\User\UserRepository;
use App\Exceptions\TrustedException;
use App\Http\ConsoleApi\Middleware\ConsoleApiAuthMiddleware;
use App\Http\ConsoleApi\Objects\Blog\BlogListObject;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Rules\Subdomain;
use Hyvor\Internal\Billing\Billing;
use Hyvor\Internal\Billing\License\BlogsLicense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConsoleUserBlogController extends Controller
{

    public function __construct(
        private ConsoleApiAuthMiddleware $consoleApiAuthMiddleware,
    ) {}

    public function createBlog(Request $request): JsonResponse
    {
        $user = $this->consoleApiAuthMiddleware->getUser($request);
        $organization = $this->consoleApiAuthMiddleware->getOrganization($request);

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
            $subdomain = 'dev-' . ((string)Str::uuid());
        }

        if (BlogService::isSubdomainReserved($subdomain)) {
            throw new TrustedException('Subdomain is reserved');
        }

        //        if (!BlogService::canUserCreateBlog($hyvorUser->id)) {
        //            throw new TrustedException('Please upgrade at least one of your blogs to create more.');
        //        }

        if ($organization === null) {
            throw new TrustedException('No current organization found');
        }

        if (!$isDev) {
            /** @var ?BlogsLicense $license */
            $license = app(Billing::class)->license($organization->id)->license;
            if ($license && $license->blogs !== 0) {
                $count = Blog::where('organization_id', $organization->id)
                    ->where('type', BlogTypeEnum::DEFAULT->value)
                    ->count();
                if ($count >= $license->blogs) {
                    throw new TrustedException('You have reached the maximum number of blogs allowed in your plan. Please upgrade your plan to create more blogs.');
                }
            }
        }

        $blog = app(BlogService::class)->createBlog(
            $user->id,
            $organization->id,
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
}
