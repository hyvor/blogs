<?php
namespace App\Repositories\Post;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PostRepository implements PostRepositoryInterface {

    public function getPostStats(int $blogId) : array
    {

        $status = Post::select('status as _id', DB::raw('COUNT(id) as count'))
            ->groupBy('status')
            ->where('blog_id', $blogId)
            ->get();

         $author = User::select('users.id as _id', DB::raw('COUNT(post_author.id) as count'))
            ->leftJoin('post_author', 'post_author.user_id', '=', 'users.id')
            ->leftJoin('posts', 'posts.id', '=', 'post_author.post_id')
            ->where('posts.blog_id', $blogId)
            ->groupBy('users.id')
            ->get();

        $tag = Tag::select('tags.id as _id', DB::raw('COUNT(post_tag.id) as count'))
            ->leftJoin('post_tag', 'post_tag.tag_id', '=', 'tags.id')
            ->leftJoin('posts', 'posts.id', '=', 'post_tag.post_id')
            ->where('posts.blog_id', '=', $blogId)
            ->groupBy('tags.id')
            ->get();
        

        $convertToKeyValue = function($val) {
            $ret = [];
            foreach ($val as $x) {
                $ret[$x['_id']] = $x['count'];
            }
            return $ret;
        };

        return [
            'status' => $convertToKeyValue($status),
            'author' => $convertToKeyValue($author),
            'tag' => $convertToKeyValue($tag),
        ];
    }

}