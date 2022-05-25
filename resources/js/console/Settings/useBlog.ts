import { useActions, useValues } from "kea";
import blogLogic from "../logic/blogLogic";
import subdomainLogic from "../logic/subdomainLogic";

export function useBlogValues() {

    const {subdomain} = useValues(subdomainLogic)
    const blogLogicInst = blogLogic({subdomain})
    return useValues(blogLogicInst);

}

export function useBlogActions() {

    const {subdomain} = useValues(subdomainLogic)
    const blogLogicInst = blogLogic({subdomain})
    return useActions(blogLogicInst);

}