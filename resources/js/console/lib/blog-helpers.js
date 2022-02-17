/**
 * Some helpers for blog plan
 * 
 * blog = array item in blogsLogic (which has both blog and user)
 */

import blogsLogic from "../logic/blogsLogic";

export function getBlogFromSubdomain(subdomain) {
    return blogsLogic.values.findBlogBySubdomain(subdomain).blog;
}

export function getBlogUrl(subdomain, path) {
    const blog = getBlogFromSubdomain(subdomain)
    if (path[0] !== '/') {
        path = '/' + path;
    }
    return blog.base_url + path;
}

export function isBlogInTeamPlan(subdomain) {
    const blog = getBlogFromSubdomain(subdomain)
    return blog.is_on_trial ||
        (
            blog.subscribed && 
            ['team', 'enterprise'].indexOf(blog.subscription.plan) >= 0
        )
}
export function isBlogInProPlan(subdomain) {
    const blog = getBlogFromSubdomain(subdomain)
    return blog.is_on_trial ||
        (
            blog.subscribed && 
            ['pro', 'team', 'enterprise'].indexOf(blog.subscription.plan) >= 0
        )
}