import { useActions, useValues } from "kea";
import postLogic from "../../logic/postLogic";
import postsLogic from "../../logic/postsLogic";
import getSubdomain from "../../logic-helpers/subdomain";

export function usePostValues(id: number) {
    return useValues(postLogic({ id }));
}

export function usePostActions(id: number) {
    return useActions(postLogic({ id }))
}


export function getCurrentPostId() : null | number {
    
    const postsLogicInst = postsLogic({subdomain: getSubdomain()});

    if (!postsLogicInst.isMounted()) {
        return null;
    }

    return postsLogicInst.values.activePostId;
}

export function getCurrentPostValues() {
    const id = getCurrentPostId();
    if (!id) {
        return null;
    }
    const postLogicInst = postLogic({ id });
    if (!postLogicInst.isMounted()) {
        return null;
    }
    return postLogicInst.values;
}