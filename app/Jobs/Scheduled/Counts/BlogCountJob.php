<?php
namespace App\Jobs\Scheduled\Counts;

use App\Domains\Count\CountRepository;
use App\Models\Blog;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use App\Data\Enums\CountEnum;
use App\Models\Count;

/**
 * Updates counts of all blogs
 * Called every 12 hours
 */
class BlogCountJob implements ShouldQueue, ShouldBeUnique {


    public function handle() {

        $rows = DB::table('blogs')
            ->select( 
                DB::raw('
                    blogs.id,
                    (
                        SELECT COUNT(users.id) 
                        FROM users 
                        WHERE
                            users.blog_id = blogs.id AND
                            users.user_id IS NOT NULL AND 
                            users.status = "active"
                    ) as users,
                    (
                        SELECT COUNT(posts.id) 
                        FROM posts 
                        WHERE
                            posts.blog_id = blogs.id AND
                            posts.status = "published" AND
                            posts.is_page = 0
                    ) as posts,

                    (
                        SELECT SUM(media.size) 
                        FROM media 
                        WHERE
                            media.blog_id = blogs.id
                    ) as media
                    '
                )
            )
            ->groupBy('blogs.id')
            ->get();

        foreach ($rows as $row) {
            $blog = Blog::find($row->id);

            CountRepository::setCount($blog, CountEnum::BLOG_USERS, $row->users);
            CountRepository::setCount($blog, CountEnum::BLOG_POSTS, $row->posts);
            CountRepository::setCount($blog, CountEnum::BLOG_MEDIA, $row->media);

        }

    }

}