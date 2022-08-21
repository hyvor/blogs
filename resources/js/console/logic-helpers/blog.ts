import {useActions, useValues} from "kea";
import getSubdomain from "./subdomain";
import blogLogic from "../logic/blogLogic";
import userBlogsLogic from "../logic/userBlogsLogic";

export function getBlogLogic() {
    return blogLogic({subdomain: getSubdomain()})
}

export function useBlogValues() {
    return useValues(getBlogLogic())
}
export function useBlogActions() {
    return useActions(getBlogLogic());
}

export function getUserBlog() {
    return userBlogsLogic.values.findBlogBySubdomain(getSubdomain())
}
export function getUserBlogBlog() {
    return getUserBlog().blog;
}