<?php

namespace App\Http\PublicApi;

use App\Domains\Blog\BlogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BillingController
{

    public function success(Request $request) : mixed
    {
        $resourceType = (string) $request->string('resource_type');
        $resourceId = $request->integer('resource_id');

        if ($resourceType === 'blog') {
            $blog = BlogService::getBlogById($resourceId);
            if ($blog) {
                return redirect('/console/' . $blog->subdomain . '/billing');
            }
        }

        return redirect('/console');
    }

}