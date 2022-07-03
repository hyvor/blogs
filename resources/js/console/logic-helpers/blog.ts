import {useActions, useValues} from "kea";
import getSubdomain from "./subdomain";
import blogLogic from "../logic/blogLogic";

export function getBlogLogic() {
    return blogLogic({subdomain: getSubdomain()})
}

export function useBlogValues() {
    return useValues(getBlogLogic())
}
export function useBlogActions() {
    return useActions(getBlogLogic());
}