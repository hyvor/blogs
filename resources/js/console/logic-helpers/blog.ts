import {useActions, useValues} from "kea";
import getSubdomain, {useSubdomain} from "./subdomain";
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

export function useUserBlog() {
    const subdomain = useSubdomain();
    return userBlogsLogic().values.findBlogBySubdomain(subdomain);
}

export function getUserBlog() {
    return userBlogsLogic().values.findBlogBySubdomain(getSubdomain())
}
export function getUserBlogBlog() {
    return getUserBlog().blog;
}