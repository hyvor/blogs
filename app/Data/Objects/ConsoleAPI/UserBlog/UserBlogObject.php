<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Models\User;

/**
 *
 * This is used in the console to represent a user's blog.
 * It contains user's and blog's data that is essential to render the console UI
 * User can be of any role.
 *
 */

class UserBlogObject
{
    public UserBlogBlogObject $blog;
    public UserBlogUserObject $user;

    /**
     * @var User should be fetched with the following relations
     * 'blog', 'blog.subscriptions'
     * If not fetched with those relations, this function will create new queries to fetch them
     */
    public function __construct(User $user)
    {
        $this->blog = new UserBlogBlogObject($user->blog);
        $this->user = new UserBlogUserObject($user);
    }
}
