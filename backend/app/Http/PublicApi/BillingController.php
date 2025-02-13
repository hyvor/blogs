<?php

namespace App\Http\PublicApi;

use App\Domains\Blog\BlogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BillingController
{

    public function success(Request $request) : mixed
    {
        $resourceId = $request->integer('resource_id');

        $blog = BlogService::getBlogById($resourceId);
        if ($blog) {
            return redirect('/console/' . $blog->subdomain . '/billing');
        }

        return redirect('/console');
    }

}