<?php
namespace App\Repositories\DataAPI;

use App\Models\Post;
use App\Exceptions\DataAPIException;
use App\Models\Tag;

class DataAPIRepository implements DataAPIRepositoryInterface {

    public function post(DataAPISingleRequest $request) : array {
        $blog = $request->getBlog();
        $id = $request->getId();
        $slug = $request->getSlug();

        $keys = $request->getKeys();
        if (is_null($id) && is_null($slug)) {
            throw new DataAPIException('Either the id or slug should be provided', 400);
        }

        $query = Post::where('blog_id', $blog->id);
        if ($id) {
            $query->where('id', $id);
        } else {
            $query->where('slug', $slug);
        }
        $post = $query->first();

        if (!$post) {
            throw new DataAPIException('Post not found', 404);
        }

        return DataAPIKeysFilter::filter(new DataAPIPost($post, $blog), $keys);
    }

    public function tag(DataAPISingleRequest $request) : array {
        $blog = $request->getBlog();
        $id = $request->getId();
        $slug = $request->getSlug();

        $keys = $request->getKeys();
        if (is_null($id) && is_null($slug)) {
            throw new DataAPIException('Either the id or slug should be provided', 400);
        }

        $query = Tag::where('blog_id', $blog->id);
        if ($id) {
            $query->where('id', $id);
        } else {
            $query->where('slug', $slug);
        }
        $tag = $query->first();

        if (!$tag) {
            throw new DataAPIException('Tag not found', 404);
        }

        return DataAPIKeysFilter::filter(new DataAPITag($tag, $blog), $keys);
    }

    public function author(DataAPISingleRequest $request) : array {
        return [];
    }

    public function posts(DataAPIMultiRequest $request) : array {
        return [];
    }
    public function tags(DataAPIMultiRequest $request) : array {
        return [];
    }
    public function authors(DataAPIMultiRequest $request) : array {
        return [];
    }

}
