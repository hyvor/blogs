<?php

namespace Tests\Feature\ConsoleAPI\UserApi;

use App\Models\Blog;
use App\Models\User;

it('changes blog sorts', function () {
    $hyvorUserId = config('test.hyvor_user_id');
    $blogs = Blog::where('hyvor_user_id', $hyvorUserId)->orderBy('id', 'DESC')->get();


    // now the lowest blogID has the highest sort
    $changes = [];
    foreach ($blogs as $blog) {
        $changes[] = $blog->id;
    }

    $this->callConsoleUserApi('PATCH', '/blogs/sort', [
        'blog_ids' => $changes,
    ])->assertOk();

    $users = User::where('hyvor_user_id', $hyvorUserId)
        ->orderBy('blog_id', 'ASC')
        ->get();

    $lastSort = INF;

    foreach ($users as $user) {
        expect($user->sort)->toBeLessThan($lastSort);
        $lastSort = $user->sort;
    }
});
