<?php

namespace App\Http\Middleware\App\ConsoleApi;

use App\Data\Enums\BlogBillingTypeEnum;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use Closure;
use Illuminate\Http\Request;

class BillingTypeMiddleware
{
    public function __construct(private Blog $blog)
    {
    }

    public function handle(Request $request, Closure $next, $type)
    {
        $type = BlogBillingTypeEnum::from($type);

        if ($this->blog->billing_type !== $type) {
            throw new TrustedException("Invalid billing type ($type->value)");
        }

        return $next($request);
    }
}
