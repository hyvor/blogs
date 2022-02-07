<?php

namespace App\Http\Controllers\ConsoleAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Domains\Blog\BlogCountsRepository;
use App\Domains\Blog\BlogRepository;

use App\Models\Blog;

class ConsoleBlogController extends Controller
{
    public function getPostsCounts(Blog $blog)
    {
        return response()->json(BlogCountsRepository::getPostsCounts($blog->id));
    }

    public function getBlog(Blog $blog) {
        $getBlog = BlogRepository::getBlog($blog->id);
        return response()->json($getBlog);
    }

    public function updateBlog(Request $request, Blog $blog) {

        $updates = [];

        // $codeHead = $request->input('codeHead');
        // $codeFooter = $request->input('codeFooter');

        // $codeHead = 'eloquent testing head';
        // $codeFooter = 'footer eloquent';

        // $updateBlog = BlogRepository::updateBlog($blog->id, $codeHead, $codeFooter);
        // return response()->json($updateBlog);

        /*
        *
        * Some strings become null when empty
        * So, always use ->has() to check if the variable is set
        *
        */
        if ($request->has('subdomain')) {
            $updates['subdomain'] = $request->input('subdomain');
        }

        if ($request->has('name')) {
            $updates['name'] = $request->input('name');
        }

        if ($request->has('description')) {
            $updates['description'] = $request->input('description');
        }

        if ($request->has('icon')) {
            $updates['icon'] = $request->input('icon');
        }

        if ($request->has('featured_image')) {
            $updates['featured_image'] = $request->input('featured_image');
        }

        if ($request->has('hosted_at')) {
            $updates['hosted_at'] = $request->input('hosted_at');
        }

        if ($request->has('custom_domain')) {
            $updates['custom_domain'] = $request->input('custom_domain');
        }

        if ($request->has('subdirectory')) {
            $updates['subdirectory'] = $request->input('subdirectory');
        }

        if ($request->has('social_facebook')) {
            $updates['social_facebook'] = $request->input('social_facebook');
        }

        if ($request->has('social_twitter')) {
            $updates['social_twitter'] = $request->input('social_twitter');
        }

        if ($request->has('social_linkedin')) {
            $updates['social_linkedin'] = $request->input('social_linkedin');
        }

        if ($request->has('social_youtube')) {
            $updates['social_youtube'] = $request->input('social_youtube');
        }

        if ($request->has('social_instagram')) {
            $updates['social_instagram'] = $request->input('social_instagram');
        }

        if ($request->has('social_github')) {
            $updates['social_github'] = $request->input('social_github');
        }

        if ($request->has('custom_head')) {
            $updates['custom_head'] = $request->input('custom_head');
        }

        if ($request->has('custom_footer')) {
            $updates['custom_footer'] = $request->input('custom_footer');
        }

        if ($request->has('edited_at')) {
            $updates['edited_at'] = $request->input('edited_at');
        }

        if ($request->has('posts_count')) {
            $updates['posts_count'] = $request->input('posts_count');
        }

        if ($request->has('users_count')) {
            $updates['users_count'] = $request->input('users_count');
        }

        $updateBlog = BlogRepository::updateBlog($blog->id, $updates);
        return response()->json($updateBlog);
    }
}
