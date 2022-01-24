<?php

namespace App\Http\Controllers\DataAPI;

use App\Data\Objects\DataAPI\AuthorObject;
use App\Data\Objects\DataAPI\PostObject;
use App\Data\Objects\DataAPI\TagObject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\TrustedException;
use App\Models\Blog;
use App\Domains\Post\PostRepository;
use App\Domains\Tag\TagRepository;
use App\Domains\User\UserRepository;
use App\Models\Post;

class DataAPIController extends Controller
{

    public function post(Request $request, Blog $blog) {

        $id = $request->input('id');
        $slug = $request->input('slug');
        $keys = $request->input('keys');

        $request->validate([
            'id' => 'required_without:slug',
            'slug' => 'required_without:id',
        ]);

        $post = PostRepository::getPostByBlogIdAndIdentifier($blog->id, $id, $slug);

        if (!$post) {
            throw new TrustedException('Post not found', TrustedException::ERROR_NOT_FOUND);
        }
        
        // Data API only return published posts
        if ($post->status !== 'published') {
            throw new TrustedException('This post is not yet published', TrustedException::ERROR_BAD_REQUEST);
        }

        return response()->json(DataAPIKeysFilter::filter(new PostObject($post, $blog), $keys));
    }

    public function tag(Request $request, Blog $blog) {
        $id = $request->input('id');
        $slug = $request->input('slug');
        $keys = $request->input('keys');

        $request->validate([
            'id' => 'required_without:slug',
            'slug' => 'required_without:id',
        ]);

        $tag = TagRepository::getTagByBlogIdAndIdentifier($blog->id, $id, $slug);
        if (!$tag) {
            throw new TrustedException('Tag not found', TrustedException::ERROR_NOT_FOUND);
        }

        return response()->json(DataAPIKeysFilter::filter(new TagObject($tag, $blog), $keys));
    }

    public function author(Request $request, Blog $blog) {
        $id = $request->input('id');
        $slug = $request->input('slug');
        $keys = $request->input('keys');

        $request->validate([
            'id' => 'required_without:slug',
            'slug' => 'required_without:id',
        ]);

        $user = UserRepository::getTagByBlogIdAndIdentifier($blog->id, $id, $slug);
        if (!$user) {
            throw new TrustedException('Tag not found', TrustedException::ERROR_NOT_FOUND);
        }
        if (!$user->posts_count > 0) {
            throw new TrustedException('This user is not an author', TrustedException::ERROR_BAD_REQUEST);
        }

        return response()->json(DataAPIKeysFilter::filter(new AuthorObject($user, $blog), $keys));
    }

    public function posts(Request $request, Blog $blog) {

        $limit = $request->input('limit');
        $page = $request->input('page');
        $filter = $request->input('filter');
        $sort = $request->input('sort');
        $keys = $request->input('keys');

        $posts = Post::get()
            ->map(function($post) use ($blog) {
                return new PostObject($post, $blog);
            });

        return response()->json(DataAPIKeysFilter::filter($posts, $keys));
        

    }
}
