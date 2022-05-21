import {Post} from "../objects/post";
import subdomainLogic from "../logic/subdomainLogic";
import blogsLogic from "../logic/blogsLogic";
import {UserBlog} from "../objects/userblog"
import {appConfig} from "../helpers";
import {UserRole} from "../enums";

function getRole() : UserRole {
    const subdomain = subdomainLogic.values.subdomain;
    const blog : UserBlog = blogsLogic.values.findBlogBySubdomain(subdomain)
    return blog.user.role;
}

export function canCreatePost() : boolean {
    const role = getRole();
    return role !== UserRole.FINANCE;
}

export function canEditPost(post: Post) : boolean {
    const role = getRole();

    // owner, admin, editor
    if ([UserRole.OWNER, UserRole.ADMIN, UserRole.EDITOR].indexOf(role) >= 0)
        return true;

    if (
        // writer
        (role === UserRole.WRITER || role === UserRole.CONTRIBUTOR) &&
        // should be an author
        post.authors.find(author => author.hyvor_user_id === appConfig().hyvorUser.id)
    ) {
        return true;
    }

}

export function canPublishPost(post: Post) : boolean {
    const role = getRole()

    if (role === UserRole.OWNER || role === UserRole.ADMIN || role === UserRole.EDITOR) {
        return true;
    }

    if (role === UserRole.WRITER) {
        return !!post.authors.find(author => author.hyvor_user_id === appConfig().hyvorUser.id);
    }

    return false;

}

/**
 * Navigation Access
 */
export function canAccessPosts() : boolean {
    return canCreatePost();
}
export function canAccessPages() : boolean {
    return canAccessPosts();
}
export function canAccessComments() : boolean {
    const role = getRole();
    return role === UserRole.OWNER ||
        role === UserRole.ADMIN ||
        role === UserRole.EDITOR;
}
export function canAccessSettings() : boolean {
    const role = getRole();
    return role === UserRole.OWNER ||
        role === UserRole.ADMIN ||
        role === UserRole.EDITOR
}
export function canAccessTheme() : boolean {
    return canAccessSettings();
}
export function canAccessBilling() : boolean {
    const role = getRole();
    return role === UserRole.OWNER ||
        role === UserRole.ADMIN ||
        role === UserRole.FINANCE;
}