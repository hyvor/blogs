import userBlogsLogic from "../logic/userBlogsLogic";
import {UserBlog} from "../objects/userblog"
import {appConfig} from "../helpers";
import {UserRole} from "../enums";
import {Post} from "../types";
import getSubdomain from "../logic-helpers/subdomain";

export default class UserPermissions {

    static getRole() : UserRole {
        const blog : UserBlog = userBlogsLogic.values.findBlogBySubdomain(getSubdomain())
        return blog.user.role;
    }

    static canCreatePost() : boolean {
        const role = UserPermissions.getRole();
        return role !== UserRole.FINANCE;
    }

    static canEditPost(post: Post) : boolean {
        const role = UserPermissions.getRole();

        // owner, admin, editor
        if ([UserRole.OWNER, UserRole.ADMIN, UserRole.EDITOR].indexOf(role) >= 0)
            return true;

        console.log(role, post.authors)
        if (
            // writer
            (role === UserRole.WRITER || role === UserRole.CONTRIBUTOR) &&
            // should be an author
            post.authors.find(author => author.hyvor_user_id === appConfig().hyvorUser.id)
        ) {
            return true;
        }

        return false;
    }

    static canPublishPost(post: Post) : boolean {
        const role = UserPermissions.getRole()

        if (role === UserRole.OWNER || role === UserRole.ADMIN || role === UserRole.EDITOR) {
            return true;
        }

        if (role === UserRole.WRITER) {
            return !!post.authors.find(author => author.hyvor_user_id === appConfig().hyvorUser.id);
        }

        return false;
    }


    // nav
    static canAccessPosts() : boolean {
        return UserPermissions.canCreatePost();
    }
    static canAccessPages() : boolean {
        return UserPermissions.canAccessPosts();
    }
    static canAccessComments() : boolean {
        const role = UserPermissions.getRole();
        return role === UserRole.OWNER ||
            role === UserRole.ADMIN ||
            role === UserRole.EDITOR;
    }
    static canAccessSettings() : boolean {
        const role = UserPermissions.getRole();
        return role === UserRole.OWNER ||
            role === UserRole.ADMIN ||
            role === UserRole.EDITOR // for TAGS
    }
    static canAccessTheme() : boolean {
        const role = UserPermissions.getRole();
        return role === UserRole.OWNER || role === UserRole.ADMIN;
    }
    static canAccessBilling() : boolean {
        const role = UserPermissions.getRole();
        return role === UserRole.OWNER ||
            role === UserRole.ADMIN ||
            role === UserRole.FINANCE;
    }

}