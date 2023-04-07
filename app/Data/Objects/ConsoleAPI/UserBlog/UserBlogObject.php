<?php

namespace App\Data\Objects\ConsoleAPI\UserBlog;

use App\Exceptions\SafetyException;
use App\Models\User;

/**
 * This is used in the console to represent a user's blog.
 * It contains user's and blog's data that is essential to render the console UI
 * User can be of any role.
 */
class UserBlogObject
{
    public UserBlogBlogObject $blog;

    public UserBlogUserObject $user;

    /**
     * User should be fetched with the following relations
     * 'blog', 'blog.subscriptions'
     * If not fetched with those relations, this function will create new queries to fetch them
     */
    public function __construct(User $user)
    {

        $blog = $user->blog;

        if (!$blog) {
            throw new SafetyException('UserBlogObject: User does not have a blog');
        }

        $this->blog = new UserBlogBlogObject($blog);
        $this->user = new UserBlogUserObject($user);
    }
}
