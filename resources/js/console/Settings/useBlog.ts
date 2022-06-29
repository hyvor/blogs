import { useActions, useValues } from "kea";
import blogLogic from "../logic/blogLogic";
import subdomainLogic from "../logic/subdomainLogic";
import getSubdomain from "../logic-helpers/subdomain";

export function useBlogValues() {

    const blogLogicInst = blogLogic({subdomain: getSubdomain()})
    return useValues(blogLogicInst);

}

export function useBlogActions() {

    const blogLogicInst = blogLogic({subdomain: getSubdomain()})
    return useActions(blogLogicInst);

}