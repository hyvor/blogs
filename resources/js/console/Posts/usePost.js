import { useActions, useValues } from "kea";
import postLogic from "../logic/postLogic";

export function usePostValues(id) {
    return useValues(postLogic({id}));
}

export function usePostActions(id) {
    return useActions(postLogic({id}))
}