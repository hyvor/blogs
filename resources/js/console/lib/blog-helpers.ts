/**
 * Some helpers for blog plan
 * 
 * blog = array item in blogsLogic (which has both blog and user)
 */

import blogsLogic from "../logic/blogsLogic";
import languagesLogic from "../logic/languagesLogic";
import {Language} from "../types";
import {BlogType} from "../enums";

export function getBlogFromSubdomain(subdomain: string) {
    return blogsLogic.values.findBlogBySubdomain(subdomain).blog;
}

export function getPrimaryLanguage(subdomain: string) : Language {
    return languagesLogic({subdomain}).values.primaryLanguage
}

export function getBlogUrl(subdomain: string, path: string) {
    const blog = getBlogFromSubdomain(subdomain)
    if (path[0] !== '/') {
        path = '/' + path;
    }
    return blog.base_url + path;
}

export function isBlogInTeamPlan(subdomain: string) {
    const blog = getBlogFromSubdomain(subdomain)
    return blog.is_on_trial ||
        (
            blog.subscribed && 
            ['team', 'enterprise'].indexOf(blog.subscription.plan) >= 0
        )
}
export function isBlogInProPlan(subdomain: string) {
    const blog = getBlogFromSubdomain(subdomain)
    return blog.is_on_trial ||
        (
            blog.subscribed && 
            ['pro', 'team', 'enterprise'].indexOf(blog.subscription.plan) >= 0
        )
}

export function isDevBlog(subdomain: string) {
    const blog = getBlogFromSubdomain(subdomain);
    return blog.type === BlogType.DEV
}